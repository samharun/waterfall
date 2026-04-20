<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DealerResource\Pages;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\DealerApprovedNotification;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DealerResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'dealers.view';
    protected static string $createPermission = 'dealers.create';
    protected static string $editPermission   = 'dealers.update';
    protected static string $deletePermission = 'dealers.delete';

    protected static ?string $model = Dealer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Dealer / Distributor';

    protected static ?string $navigationLabel = 'Dealers';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = \App\Models\Dealer::where('approval_status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Dealer Basic Information')
                ->icon('heroicon-o-building-storefront')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('dealer_code')
                            ->label('Dealer Code')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated (WF-DLR-XXXXXX)')
                            ->visibleOn('edit'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('mobile')
                            ->required()
                            ->maxLength(11)
                            ->tel()
                            ->regex('/^01[3-9][0-9]{8}$/')
                            ->helperText('Bangladesh mobile: 01XXXXXXXXX (11 digits)')
                            ->unique(Dealer::class, 'mobile', ignoreRecord: true),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\Select::make('zone_id')
                            ->label('Zone / Line')
                            ->options(fn () => Zone::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('— None —'),
                    ]),

                    Forms\Components\Textarea::make('address')
                        ->required()
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ]),

            Section::make('Account & Approval')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('approval_status')
                            ->options(Dealer::approvalStatusLabels())
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('current_due')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0),

                        Forms\Components\Select::make('approved_by')
                            ->label('Approved By')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('— None —'),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->nullable(),
                    ]),
                ]),

            Section::make('System Information')
                ->icon('heroicon-o-cog-6-tooth')
                ->collapsed()
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Linked User Account')
                        ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('— None —'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dealer_code')
                    ->label('Dealer Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zone')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger'  => 'rejected',
                        'gray'    => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('current_due')
                    ->label('Due (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('zone_id')
                    ->label('Zone')
                    ->options(fn () => Zone::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Approval Status')
                    ->options(Dealer::approvalStatusLabels()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Dealer $record) => $record->approval_status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Dealer $record) {
                        $record->update([
                            'approval_status' => 'approved',
                            'approved_by'     => Auth::id(),
                            'approved_at'     => now(),
                        ]);
                        if ($record->email && $record->user) {
                            $record->user->notify(new DealerApprovedNotification($record));
                        }
                        Notification::make()->title('Dealer approved')->success()->send();
                    }),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Dealer $record) => $record->approval_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Dealer $record) {
                        $record->update(['approval_status' => 'rejected']);
                        Notification::make()->title('Dealer rejected')->warning()->send();
                    }),

                Actions\Action::make('mark_inactive')
                    ->label('Deactivate')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->visible(fn (Dealer $record) => $record->approval_status === 'approved')
                    ->requiresConfirmation()
                    ->action(function (Dealer $record) {
                        $record->update(['approval_status' => 'inactive']);
                        Notification::make()->title('Dealer marked inactive')->send();
                    }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Dealer $r) => $r->update([
                                'approval_status' => 'approved',
                                'approved_by'     => Auth::id(),
                                'approved_at'     => now(),
                            ]));
                            Notification::make()->title('Selected dealers approved')->success()->send();
                        }),

                    Actions\BulkAction::make('bulk_inactive')
                        ->label('Mark Inactive')
                        ->icon('heroicon-o-pause-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Dealer $r) => $r->update(['approval_status' => 'inactive']));
                            Notification::make()->title('Selected dealers marked inactive')->send();
                        }),

                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDealers::route('/'),
            'create' => Pages\CreateDealer::route('/create'),
            'edit'   => Pages\EditDealer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
