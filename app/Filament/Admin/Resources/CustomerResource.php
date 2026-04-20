<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\CustomerApprovedNotification;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'customers.view';
    protected static string $createPermission = 'customers.create';
    protected static string $editPermission   = 'customers.update';
    protected static string $deletePermission = 'customers.delete';

    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Customers';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = Customer::where('approval_status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer Basic Information')
                ->icon('heroicon-o-identification')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('customer_id')
                            ->label('Customer ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated (WF-CUS-XXXXXX)')
                            ->visibleOn('edit'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('name_bn')
                            ->label('Name (Bangla)')
                            ->maxLength(255)
                            ->nullable(),

                        Forms\Components\TextInput::make('mobile')
                            ->required()
                            ->maxLength(11)
                            ->tel()
                            ->regex('/^01[3-9][0-9]{8}$/')
                            ->helperText('Bangladesh mobile: 01XXXXXXXXX (11 digits)')
                            ->unique(Customer::class, 'mobile', ignoreRecord: true),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->nullable()
                            ->unique(Customer::class, 'email', ignoreRecord: true),

                        Forms\Components\Select::make('customer_type')
                            ->options(Customer::typeLabels())
                            ->default('residential')
                            ->required(),

                        Forms\Components\Select::make('zone_id')
                            ->label('Zone / Line')
                            ->options(fn () => Zone::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->helperText('Assign a zone before approving the customer.'),
                    ]),

                    Forms\Components\Textarea::make('address')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('address_bn')
                        ->label('Address (Bangla)')
                        ->rows(3)
                        ->maxLength(500)
                        ->nullable()
                        ->columnSpanFull(),
                ]),

            Section::make('Account & Approval')
                ->icon('heroicon-o-shield-check')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('approval_status')
                            ->options(Customer::approvalStatusLabels())
                            ->default('pending')
                            ->required(),

                        Forms\Components\Select::make('default_delivery_slot')
                            ->options(Customer::deliverySlotLabels())
                            ->nullable()
                            ->placeholder('None'),

                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->prefix('BDT')
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('current_due')
                            ->numeric()
                            ->prefix('BDT')
                            ->default(0),

                        Forms\Components\TextInput::make('jar_deposit_qty')
                            ->label('Jar Deposit Qty')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\Select::make('approved_by')
                            ->label('Approved By')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('None'),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->nullable(),
                    ]),
                ]),

            Section::make('System Information')
                ->icon('heroicon-o-cog-6-tooth')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Linked User Account')
                            ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('None'),

                        Forms\Components\TextInput::make('qr_code')
                            ->label('QR Code')
                            ->maxLength(255)
                            ->nullable(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_id')
                    ->label('Customer ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->name_bn),

                Tables\Columns\TextColumn::make('mobile')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zone')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('customer_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'info' => 'residential',
                        'warning' => 'corporate',
                    ]),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('current_due')
                    ->label('Due (BDT)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('jar_deposit_qty')
                    ->label('Jar Dep.')
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

                Tables\Filters\SelectFilter::make('customer_type')
                    ->options(Customer::typeLabels()),

                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Approval Status')
                    ->options(Customer::approvalStatusLabels()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                    Actions\Action::make('change_password')
                        ->label('Change Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->modalHeading(fn (Customer $record) => "Change Password: {$record->name}")
                        ->modalDescription(fn (Customer $record) => $record->user_id
                            ? 'Set a new password for this customer app login.'
                            : 'This customer does not have a linked login yet. Saving here will create one and set the password.')
                        ->fillForm(fn (Customer $record) => [
                            'email' => $record->user?->email ?? static::defaultCustomerLoginEmail($record),
                        ])
                        ->form([
                            Forms\Components\TextInput::make('email')
                                ->label('Login Email')
                                ->email()
                                ->disabled()
                                ->dehydrated(false)
                                ->helperText('This is the customer login account that will be updated or created.'),

                            Forms\Components\TextInput::make('password')
                                ->label('New Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(6)
                                ->maxLength(255),

                            Forms\Components\TextInput::make('password_confirmation')
                                ->label('Confirm Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->same('password'),
                        ])
                        ->action(function (Customer $record, array $data) {
                            DB::transaction(function () use ($record, $data) {
                                $user = $record->user;

                                if (! $user) {
                                    $user = User::create([
                                        'name' => $record->name,
                                        'email' => static::uniqueCustomerLoginEmail($record),
                                        'password' => Hash::make($data['password']),
                                        'role' => 'customer',
                                    ]);

                                    $record->update(['user_id' => $user->id]);

                                    return;
                                }

                                $user->update([
                                    'name' => $record->name,
                                    'password' => Hash::make($data['password']),
                                ]);
                            });

                            Notification::make()
                                ->title('Customer password updated')
                                ->success()
                                ->body('The customer can now sign in with the new password.')
                                ->send();
                        }),

                Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Customer $record) => $record->approval_status !== 'approved' && auth()->user()?->can('customers.approve'))
                    ->fillForm(fn (Customer $record) => [
                        'zone_id' => $record->zone_id,
                        'address' => $record->address,
                    ])
                    ->form([
                        Forms\Components\Select::make('zone_id')
                            ->label('Zone / Line')
                            ->options(fn () => Zone::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Required before approval.'),

                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->maxLength(500)
                            ->required()
                            ->helperText('Customer must have a delivery address before approval.'),
                    ])
                    ->action(function (Customer $record, array $data) {
                        $record->update([
                            'zone_id'         => $data['zone_id'],
                            'address'         => $data['address'],
                            'approval_status' => 'approved',
                            'approved_by'     => Auth::id(),
                            'approved_at'     => now(),
                        ]);

                        if ($record->email && $record->user) {
                            $record->user->notify(new CustomerApprovedNotification($record));
                        }

                        Notification::make()
                            ->title('Customer approved')
                            ->success()
                            ->send();
                    }),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Customer $record) => $record->approval_status === 'pending' && auth()->user()?->can('customers.reject'))
                    ->requiresConfirmation()
                    ->action(function (Customer $record) {
                        $record->update(['approval_status' => 'rejected']);
                        Notification::make()
                            ->title('Customer rejected')
                            ->warning()
                            ->send();
                    }),

                Actions\Action::make('mark_inactive')
                    ->label('Deactivate')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->visible(fn (Customer $record) => $record->approval_status === 'approved')
                    ->requiresConfirmation()
                    ->action(function (Customer $record) {
                        $record->update(['approval_status' => 'inactive']);
                        Notification::make()
                            ->title('Customer marked inactive')
                            ->send();
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
                            $approvedCount = 0;
                            $skippedCount = 0;

                            $records->each(function (Customer $record) use (&$approvedCount, &$skippedCount) {
                                if (! $record->zone_id || ! $record->address) {
                                    $skippedCount++;
                                    return;
                                }

                                $record->update([
                                    'approval_status' => 'approved',
                                    'approved_by' => Auth::id(),
                                    'approved_at' => now(),
                                ]);
                                $approvedCount++;
                            });

                            if ($approvedCount > 0) {
                                Notification::make()
                                    ->title("{$approvedCount} customer(s) approved")
                                    ->success()
                                    ->send();
                            }

                            if ($skippedCount > 0) {
                                Notification::make()
                                    ->title("{$skippedCount} customer(s) skipped")
                                    ->body('Zone and address are required before approving.')
                                    ->warning()
                                    ->send();
                            }
                        }),

                    Actions\BulkAction::make('bulk_inactive')
                        ->label('Mark Inactive')
                        ->icon('heroicon-o-pause-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(fn (Customer $record) => $record->update(['approval_status' => 'inactive']));
                            Notification::make()->title('Selected customers marked inactive')->send();
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    protected static function defaultCustomerLoginEmail(Customer $record): string
    {
        return $record->email ?: sprintf('%s-customer@waterfall.local', $record->mobile);
    }

    protected static function uniqueCustomerLoginEmail(Customer $record): string
    {
        $baseEmail = static::defaultCustomerLoginEmail($record);

        if (! User::where('email', $baseEmail)->exists()) {
            return $baseEmail;
        }

        [$localPart, $domain] = str_contains($baseEmail, '@')
            ? explode('@', $baseEmail, 2)
            : [$baseEmail, 'waterfall.local'];

        return sprintf('%s+customer%s@%s', $localPart, $record->id, $domain);
    }
}
