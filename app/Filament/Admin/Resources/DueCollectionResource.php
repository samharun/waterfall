<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DueCollectionResource\Pages;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DueCollectionResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'due_collections.manage';
    protected static string $createPermission = 'due_collections.manage';
    protected static string $editPermission   = 'due_collections.manage';
    protected static string $deletePermission = 'due_collections.manage';

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Due Collections';

    protected static ?string $slug = 'due-collections';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = Payment::where('collection_source', 'delivery_staff')
            ->where('collection_status', 'pending_review')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Collection Details')
                ->icon('heroicon-o-arrow-down-tray')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('payment_no')
                            ->label('Collection No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        // Which delivery staff handed the cash
                        Forms\Components\Select::make('collected_from_staff_id')
                            ->label('Collected From (Staff)')
                            ->options(fn () => User::deliveryStaff()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->id => $u->name])
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                // Auto-fill delivery select when staff changes
                                $set('delivery_id', null);
                            }),

                        // Optionally link to a specific delivery
                        Forms\Components\Select::make('delivery_id')
                            ->label('Related Delivery (optional)')
                            ->options(function (Get $get) {
                                $staffId = $get('collected_from_staff_id');
                                $query = Delivery::with(['order.customer', 'order.dealer'])
                                    ->where('delivery_status', 'delivered');

                                if ($staffId) {
                                    $query->where('delivery_staff_id', $staffId);
                                }

                                return $query->orderByDesc('delivered_at')
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(function (Delivery $d) {
                                        $party = $d->order?->customer
                                            ? $d->order->customer->name
                                            : ($d->order?->dealer?->name ?? '—');
                                        return [$d->id => "{$d->delivery_no} — {$party}"];
                                    });
                            })
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (! $state) {
                                    return;
                                }
                                $delivery = Delivery::with(['order.customer', 'order.dealer'])->find($state);
                                if (! $delivery) {
                                    return;
                                }
                                // Auto-fill order and party
                                $set('order_id', $delivery->order_id);
                                if ($delivery->order?->order_type === 'customer') {
                                    $set('payment_type', 'customer');
                                    $set('customer_id', $delivery->order->customer_id);
                                    $set('dealer_id', null);
                                } else {
                                    $set('payment_type', 'dealer');
                                    $set('dealer_id', $delivery->order->dealer_id ?? null);
                                    $set('customer_id', null);
                                }
                                // Pre-fill staff if not already set
                                if ($delivery->delivery_staff_id) {
                                    $set('collected_from_staff_id', $delivery->delivery_staff_id);
                                }
                            }),

                        Forms\Components\Select::make('payment_type')
                            ->label('Party Type')
                            ->options(Payment::typeLabels())
                            ->default('customer')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Customer $c) => [
                                    $c->id => "{$c->customer_id} — {$c->name} ({$c->mobile})",
                                ])
                            )
                            ->searchable()
                            ->nullable()
                            ->visible(fn (Get $get) => $get('payment_type') === 'customer')
                            ->required(fn (Get $get) => $get('payment_type') === 'customer'),

                        Forms\Components\Select::make('dealer_id')
                            ->label('Dealer')
                            ->options(fn () => Dealer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Dealer $d) => [
                                    $d->id => "{$d->dealer_code} — {$d->name} ({$d->mobile})",
                                ])
                            )
                            ->searchable()
                            ->nullable()
                            ->visible(fn (Get $get) => $get('payment_type') === 'dealer')
                            ->required(fn (Get $get) => $get('payment_type') === 'dealer'),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Collection Date')
                            ->default(now()->toDateString())
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount Collected (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required()
                            ->minValue(0.01),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(Payment::methodLabels())
                            ->default('cash')
                            ->required(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference No')
                            ->nullable(),

                        Forms\Components\Select::make('collection_status')
                            ->label('Status')
                            ->options(Payment::collectionStatusLabels())
                            ->default('pending_review')
                            ->required()
                            ->helperText('Set to "Accepted" once cash is verified and handed to billing.'),
                    ]),

                    Forms\Components\Textarea::make('remarks')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_no')
                    ->label('Collection No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('collectedFromStaff.name')
                    ->label('Collected From')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('party')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (Payment $record): string {
                        if ($record->payment_type === 'customer' && $record->customer) {
                            return "{$record->customer->customer_id} — {$record->customer->name}";
                        }
                        if ($record->payment_type === 'dealer' && $record->dealer) {
                            return "{$record->dealer->dealer_code} — {$record->dealer->name}";
                        }
                        return '—';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('customer', fn ($cq) => $cq
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('customer_id', 'like', "%{$search}%")
                            )->orWhereHas('dealer', fn ($dq) => $dq
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('dealer_code', 'like', "%{$search}%")
                            );
                        });
                    }),

                Tables\Columns\TextColumn::make('delivery.delivery_no')
                    ->label('Delivery No')
                    ->fontFamily('mono')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'info'    => 'bkash',
                        'warning' => 'nagad',
                        'gray'    => 'bank',
                    ]),

                Tables\Columns\TextColumn::make('collection_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending_review',
                        'success' => 'accepted',
                        'danger'  => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => Payment::collectionStatusLabels()[$state] ?? $state),

                Tables\Columns\TextColumn::make('receivedBy.name')
                    ->label('Recorded By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('collected_from_staff_id')
                    ->label('Staff')
                    ->options(fn () => User::deliveryStaff()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('collection_status')
                    ->label('Status')
                    ->options(Payment::collectionStatusLabels()),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Method')
                    ->options(Payment::methodLabels()),

                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('payment_date', '<=', $data['until']))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $r) => $r->collection_status === 'pending_review' && auth()->user()?->can('collections.reconcile'))
                    ->requiresConfirmation()
                    ->action(function (Payment $record) {
                        $record->update([
                            'collection_status' => 'accepted',
                            'collected_at'      => now(),
                            'received_by'       => Auth::id(),
                        ]);
                        Notification::make()->title('Collection accepted')->success()->send();
                    }),

                Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $r) => $r->collection_status === 'pending_review' && auth()->user()?->can('collections.reconcile'))
                    ->form([
                        Forms\Components\Textarea::make('remarks')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $record->update([
                            'collection_status' => 'rejected',
                            'remarks'           => $data['remarks'],
                        ]);
                        Notification::make()->title('Collection rejected')->warning()->send();
                    }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_accept')
                        ->label('Accept Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('collections.reconcile'))
                        ->action(function ($records) {
                            $records
                                ->filter(fn (Payment $r) => $r->collection_status === 'pending_review')
                                ->each(fn (Payment $r) => $r->update([
                                    'collection_status' => 'accepted',
                                    'collected_at'      => now(),
                                    'received_by'       => Auth::id(),
                                ]));
                            Notification::make()->title('Collections accepted')->success()->send();
                        }),

                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Scope this resource to only show delivery_staff collections.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('collection_source', 'delivery_staff')
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDueCollections::route('/'),
            'create' => Pages\CreateDueCollection::route('/create'),
            'edit'   => Pages\EditDueCollection::route('/{record}/edit'),
        ];
    }
}

