<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
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

class OrderResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'orders.view';
    protected static string $createPermission = 'orders.create';
    protected static string $editPermission   = 'orders.update';
    protected static string $deletePermission = 'orders.delete';

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Order Information ──────────────────────────────────
            Section::make('Order Information')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('order_no')
                            ->label('Order No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('order_type')
                            ->label('Order Type')
                            ->options(Order::orderTypeLabels())
                            ->default('customer')
                            ->required()
                            ->live(),

                        // Customer select — visible when order_type = customer
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Customer $c) => [
                                    $c->id => "{$c->customer_id} — {$c->name} ({$c->mobile})",
                                ])
                            )
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => $get('order_type') === 'customer')
                            ->required(fn (Get $get) => $get('order_type') === 'customer')
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer?->zone_id) {
                                        $set('zone_id', $customer->zone_id);
                                    }
                                }
                            }),

                        // Dealer select — visible when order_type = dealer
                        Forms\Components\Select::make('dealer_id')
                            ->label('Dealer')
                            ->options(fn () => Dealer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Dealer $d) => [
                                    $d->id => "{$d->dealer_code} — {$d->name} ({$d->mobile})",
                                ])
                            )
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => $get('order_type') === 'dealer')
                            ->required(fn (Get $get) => $get('order_type') === 'dealer')
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $dealer = Dealer::find($state);
                                    if ($dealer?->zone_id) {
                                        $set('zone_id', $dealer->zone_id);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('zone_id')
                            ->label('Zone')
                            ->options(fn () => Zone::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\DatePicker::make('order_date')
                            ->label('Order Date')
                            ->default(now()->toDateString())
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('preferred_delivery_slot')
                            ->label('Delivery Slot')
                            ->options(Order::deliverySlotLabels())
                            ->default('now')
                            ->required()
                            ->live(),

                        Forms\Components\DateTimePicker::make('preferred_delivery_time')
                            ->label('Delivery Time')
                            ->visible(fn (Get $get) => $get('preferred_delivery_slot') === 'custom')
                            ->required(fn (Get $get) => $get('preferred_delivery_slot') === 'custom'),

                        Forms\Components\Select::make('order_status')
                            ->label('Order Status')
                            ->options(fn (?Order $record) => self::manualOrderStatusOptions($record))
                            ->default('pending')
                            ->required()
                            ->helperText(fn (?Order $record) => self::orderStatusHelperText($record)),

                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(Order::paymentStatusLabels())
                            ->default('unpaid')
                            ->required(),
                    ]),
                ]),

            // ── Order Items ────────────────────────────────────────
            Section::make('Order Items')
                ->icon('heroicon-o-list-bullet')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Grid::make(4)->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(fn () => Product::active()->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Product $p) => [
                                            $p->id => "[{$p->sku}] {$p->name}",
                                        ])
                                    )
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $product  = Product::find($state);
                                        $price    = self::resolveProductPrice($product, $get);
                                        $qty      = max(1, (int) $get('quantity'));
                                        $set('unit_price', $price);
                                        $set('line_total', round($qty * $price, 2));
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->numeric()
                                    ->integer()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $qty   = max(1, (int) $state);
                                        $price = (float) $get('unit_price');
                                        $set('line_total', round($qty * $price, 2));
                                    }),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('Unit Price (৳)')
                                    ->numeric()
                                    ->prefix('৳')
                                    ->minValue(0)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $qty   = max(1, (int) $get('quantity'));
                                        $price = (float) $state;
                                        $set('line_total', round($qty * $price, 2));
                                    }),

                                Forms\Components\TextInput::make('line_total')
                                    ->label('Line Total (৳)')
                                    ->numeric()
                                    ->prefix('৳')
                                    ->disabled()
                                    ->dehydrated(true),
                            ]),
                        ])
                        ->columns(1)
                        ->addActionLabel('Add Product')
                        ->minItems(1)
                        ->reorderable(false)
                        ->collapsible(),
                ]),

            // ── Amount Summary ─────────────────────────────────────
            Section::make('Amount Summary')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),

                        Forms\Components\TextInput::make('discount')
                            ->label('Discount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('delivery_charge')
                            ->label('Delivery Charge (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),
                    ]),

                    Forms\Components\Textarea::make('remarks')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * Returns the order status options that are safe to set manually.
     * "assigned" and "delivered" are driven by delivery assignments only.
     */
    protected static function manualOrderStatusOptions(?Order $record): array
    {
        $all = Order::orderStatusLabels();

        // If the order already has an active delivery, allow all statuses (read-only context).
        // Otherwise, strip delivery-driven statuses so they can't be set by hand.
        if ($record && $record->deliveries()->whereNotIn('delivery_status', ['cancelled'])->exists()) {
            return $all;
        }

        return array_diff_key($all, array_flip(['assigned', 'delivered']));
    }

    /**
     * Helper text shown under the order_status field.
     */
    protected static function orderStatusHelperText(?Order $record): ?string
    {
        if ($record && $record->deliveries()->whereNotIn('delivery_status', ['cancelled'])->exists()) {
            return null;
        }

        return '"Assigned" and "Delivered" are set automatically via Delivery Assignments.';
    }

    /**
     * Resolve the correct unit price for a product based on order context.
     * Called from repeater item afterStateUpdated callbacks.
     */
    protected static function resolveProductPrice(?Product $product, Get $get): float
    {
        if (! $product) {
            return 0;
        }

        $orderType  = $get('../../order_type');
        $customerId = $get('../../customer_id');
        $dealerId   = $get('../../dealer_id');
        $orderDate  = $get('../../order_date');

        if ($orderType === 'customer' && $customerId) {
            return (float) $product->getPriceForCustomer((int) $customerId, $orderDate);
        }

        if ($orderType === 'dealer' && $dealerId) {
            return (float) $product->getPriceForDealer((int) $dealerId, $orderDate);
        }

        return (float) $product->default_price;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_no')
                    ->label('Order No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('order_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'info'    => 'customer',
                        'warning' => 'dealer',
                    ]),

                // Combined customer/dealer column
                Tables\Columns\TextColumn::make('party')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (Order $record): string {
                        if ($record->order_type === 'customer' && $record->customer) {
                            return "{$record->customer->customer_id} — {$record->customer->name}";
                        }
                        if ($record->order_type === 'dealer' && $record->dealer) {
                            return "{$record->dealer->dealer_code} — {$record->dealer->name}";
                        }
                        return '—';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('customer', fn (Builder $cq) => $cq
                                ->where('customer_id', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            )->orWhereHas('dealer', fn (Builder $dq) => $dq
                                ->where('dealer_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            );
                        });
                    }),

                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zone')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('order_date')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('preferred_delivery_slot')
                    ->label('Slot')
                    ->badge()
                    ->colors([
                        'success' => 'now',
                        'info'    => 'morning',
                        'warning' => 'afternoon',
                        'gray'    => 'evening',
                        'danger'  => 'custom',
                    ]),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal (৳)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Discount (৳)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('delivery_charge')
                    ->label('Delivery (৳)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->colors([
                        'danger'  => 'unpaid',
                        'warning' => 'partial',
                        'success' => 'paid',
                    ]),

                Tables\Columns\TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'info'    => 'confirmed',
                        'gray'    => 'assigned',
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('delivery.delivery_status')
                    ->label('Delivery')
                    ->badge()
                    ->colors([
                        'gray'    => 'pending',
                        'info'    => 'assigned',
                        'warning' => 'in_progress',
                        'success' => 'delivered',
                        'danger'  => 'failed',
                    ])
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('order_type')
                    ->options(Order::orderTypeLabels()),

                Tables\Filters\SelectFilter::make('zone_id')
                    ->label('Zone')
                    ->options(fn () => Zone::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('preferred_delivery_slot')
                    ->label('Delivery Slot')
                    ->options(Order::deliverySlotLabels()),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options(Order::paymentStatusLabels()),

                Tables\Filters\SelectFilter::make('order_status')
                    ->options(Order::orderStatusLabels())
                    ->default('pending'),

                Tables\Filters\Filter::make('order_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn (Builder $q) => $q->whereDate('order_date', '>=', $data['from']))
                            ->when($data['until'], fn (Builder $q) => $q->whereDate('order_date', '<=', $data['until']));
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),

                Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->order_status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->markConfirmed();
                        Notification::make()->title('Order confirmed')->success()->send();
                    }),

                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record) => in_array($record->order_status, ['pending', 'confirmed']))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->markCancelled();
                        Notification::make()->title('Order cancelled')->warning()->send();
                    }),

                Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->payment_status !== 'paid')
                    ->action(function (Order $record) {
                        $record->update(['payment_status' => 'paid']);
                        Notification::make()->title('Marked as paid')->success()->send();
                    }),

                Actions\Action::make('mark_unpaid')
                    ->label('Mark Unpaid')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (Order $record) => $record->payment_status !== 'unpaid')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['payment_status' => 'unpaid']);
                        Notification::make()->title('Marked as unpaid')->send();
                    }),

                Actions\Action::make('assign_delivery')
                    ->label('Assign Delivery')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->canCreateDelivery())
                    ->form([
                        Forms\Components\Select::make('delivery_staff_id')
                            ->label('Delivery Staff')
                            ->options(fn () => User::deliveryStaff()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                            )
                            ->searchable()
                            ->required(),

                        Forms\Components\Textarea::make('delivery_note')
                            ->label('Delivery Note')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (Order $record, array $data) {
                        Delivery::create([
                            'delivery_no'       => Delivery::generateDeliveryNo(),
                            'order_id'          => $record->id,
                            'zone_id'           => $record->zone_id,
                            'delivery_staff_id' => $data['delivery_staff_id'],
                            'assigned_by'       => Auth::id(),
                            'assigned_at'       => now(),
                            'delivery_status'   => 'assigned',
                            'delivery_note'     => $data['delivery_note'] ?? null,
                        ]);

                        $record->update(['order_status' => 'assigned']);

                        Notification::make()->title('Delivery assigned successfully')->success()->send();
                    }),

                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
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
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
