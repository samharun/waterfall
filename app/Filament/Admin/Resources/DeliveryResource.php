<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DeliveryResource\Pages;
use App\Models\Delivery;
use App\Models\Order;
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

class DeliveryResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'deliveries.view';
    protected static string $createPermission = 'deliveries.create';
    protected static string $editPermission   = 'deliveries.update';
    protected static string $deletePermission = 'deliveries.delete';

    protected static ?string $model = Delivery::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Order & Delivery';

    protected static ?string $navigationLabel = 'Delivery Assignments';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Delivery Information')
                ->icon('heroicon-o-truck')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('delivery_no')
                            ->label('Delivery No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('order_id')
                            ->label('Order')
                            ->options(function () {
                                return Order::whereIn('order_status', ['confirmed', 'assigned'])
                                    ->whereDoesntHave('deliveries', fn (Builder $q) => $q->active())
                                    ->orWhereHas('deliveries') // allow editing existing
                                    ->with(['customer', 'dealer'])
                                    ->get()
                                    ->mapWithKeys(function (Order $o) {
                                        $party = $o->customer
                                            ? "{$o->customer->customer_id} {$o->customer->name}"
                                            : ($o->dealer ? "{$o->dealer->dealer_code} {$o->dealer->name}" : '—');
                                        return [$o->id => "{$o->order_no} — {$party} (৳{$o->total_amount})"];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $order = Order::find($state);
                                    if ($order?->zone_id) {
                                        $set('zone_id', $order->zone_id);
                                    }
                                }
                            })
                            ->visibleOn('create'),

                        // On edit show order_no as readonly text
                        Forms\Components\TextInput::make('order.order_no')
                            ->label('Order')
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('zone_id')
                            ->label('Zone')
                            ->options(fn () => Zone::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),

                        Forms\Components\Select::make('delivery_staff_id')
                            ->label('Delivery Staff')
                            ->options(fn () => User::deliveryStaff()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                            )
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state) {
                                    // Auto-promote status to assigned
                                    if ($get('delivery_status') === 'pending') {
                                        $set('delivery_status', 'assigned');
                                    }
                                    if (! $get('assigned_at')) {
                                        $set('assigned_at', now()->format('Y-m-d H:i:s'));
                                    }
                                    $set('assigned_by', Auth::id());
                                }
                            }),

                        Forms\Components\Select::make('delivery_status')
                            ->label('Delivery Status')
                            ->options(Delivery::statusLabels())
                            ->default('pending')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('assigned_by')
                            ->label('Assigned By')
                            ->options(fn () => User::backOffice()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->default(fn () => Auth::id()),

                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->label('Assigned At')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Delivered At')
                            ->nullable(),
                    ]),
                ]),

            Section::make('Notes')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->schema([
                    Forms\Components\Textarea::make('delivery_note')
                        ->label('Delivery Note')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('failure_reason')
                        ->label('Failure Reason')
                        ->rows(3)
                        ->nullable()
                        ->visible(fn (Get $get) => $get('delivery_status') === 'failed')
                        ->required(fn (Get $get) => $get('delivery_status') === 'failed')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delivery_no')
                    ->label('Delivery No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('order.order_no')
                    ->label('Order No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                // Combined customer/dealer from order
                Tables\Columns\TextColumn::make('party')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (Delivery $record): string {
                        $order = $record->order;
                        if (! $order) {
                            return '—';
                        }
                        if ($order->order_type === 'customer' && $order->customer) {
                            return "{$order->customer->customer_id} — {$order->customer->name}";
                        }
                        if ($order->order_type === 'dealer' && $order->dealer) {
                            return "{$order->dealer->dealer_code} — {$order->dealer->name}";
                        }
                        return '—';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('order', function (Builder $q) use ($search) {
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

                Tables\Columns\TextColumn::make('deliveryStaff.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unassigned'),

                Tables\Columns\TextColumn::make('delivery_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray'    => 'pending',
                        'info'    => 'assigned',
                        'warning' => 'in_progress',
                        'success' => 'delivered',
                        'danger'  => 'failed',
                        'gray'    => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('assigned_at')
                    ->label('Assigned At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Delivered At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),

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

                Tables\Filters\SelectFilter::make('delivery_staff_id')
                    ->label('Delivery Staff')
                    ->options(fn () => User::deliveryStaff()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('delivery_status')
                    ->label('Status')
                    ->options(Delivery::statusLabels()),

                Tables\Filters\Filter::make('assigned_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Assigned From'),
                        Forms\Components\DatePicker::make('until')->label('Assigned Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('assigned_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('assigned_at', '<=', $data['until']))
                    ),

                Tables\Filters\Filter::make('delivered_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Delivered From'),
                        Forms\Components\DatePicker::make('until')->label('Delivered Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('delivered_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('delivered_at', '<=', $data['until']))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),

                // Mark Assigned
                Actions\Action::make('mark_assigned')
                    ->label('Assign Staff')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (Delivery $r) => $r->delivery_status === 'pending')
                    ->form([
                        Forms\Components\Select::make('delivery_staff_id')
                            ->label('Delivery Staff')
                            ->options(fn () => User::deliveryStaff()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"])
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Delivery $record, array $data) {
                        $record->markAssigned($data['delivery_staff_id'], Auth::id());
                        Notification::make()->title('Delivery assigned')->success()->send();
                    }),

                // Mark In Progress
                Actions\Action::make('mark_in_progress')
                    ->label('In Progress')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (Delivery $r) => $r->delivery_status === 'assigned')
                    ->requiresConfirmation()
                    ->action(function (Delivery $record) {
                        $record->markInProgress();
                        Notification::make()->title('Delivery in progress')->send();
                    }),

                // Mark Delivered
                Actions\Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Delivery $r) => in_array($r->delivery_status, ['assigned', 'in_progress']))
                    ->requiresConfirmation()
                    ->action(function (Delivery $record) {
                        $record->markDelivered();
                        Notification::make()->title('Delivery completed')->success()->send();
                    }),

                // Mark Failed
                Actions\Action::make('mark_failed')
                    ->label('Mark Failed')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (Delivery $r) => in_array($r->delivery_status, ['assigned', 'in_progress']))
                    ->form([
                        Forms\Components\Textarea::make('failure_reason')
                            ->label('Failure Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Delivery $record, array $data) {
                        $record->markFailed($data['failure_reason']);
                        Notification::make()->title('Delivery marked as failed')->warning()->send();
                    }),

                // Cancel Delivery
                Actions\Action::make('cancel_delivery')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->visible(fn (Delivery $r) => in_array($r->delivery_status, ['pending', 'assigned', 'in_progress']))
                    ->requiresConfirmation()
                    ->action(function (Delivery $record) {
                        $record->markCancelled();
                        Notification::make()->title('Delivery cancelled')->send();
                    }),

                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('bulk_delivered')
                        ->label('Mark Delivered')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records
                                ->filter(fn (Delivery $r) => in_array($r->delivery_status, ['assigned', 'in_progress']))
                                ->each(fn (Delivery $r) => $r->markDelivered());
                            Notification::make()->title('Selected deliveries marked delivered')->success()->send();
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
            'index'  => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit'   => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
