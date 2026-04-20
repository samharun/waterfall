<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerSubscriptionResource\Pages;
use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Product;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CustomerSubscriptionResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'subscriptions.view';
    protected static string $createPermission = 'subscriptions.manage';
    protected static string $editPermission   = 'subscriptions.manage';
    protected static string $deletePermission = 'subscriptions.manage';

    protected static ?string $model = CustomerSubscription::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Subscriptions';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Subscription Information')
                ->icon('heroicon-o-arrow-path')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('subscription_no')
                            ->label('Subscription No')
                            ->disabled()->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Customer $c) => [
                                    $c->id => "{$c->customer_id} — {$c->name} ({$c->mobile})",
                                ])
                            )
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn () => Product::active()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Product $p) => [
                                    $p->id => "[{$p->sku}] {$p->name}",
                                ])
                            )
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()->integer()->minValue(1)->default(1)->required(),

                        Forms\Components\Select::make('frequency')
                            ->options(CustomerSubscription::frequencyLabels())
                            ->default('daily')
                            ->required()
                            ->live(),

                        Forms\Components\CheckboxList::make('delivery_days')
                            ->options(CustomerSubscription::deliveryDayOptions())
                            ->columns(3)
                            ->visible(fn (Get $get) => in_array($get('frequency'), ['weekly', 'custom_days']))
                            ->required(fn (Get $get) => in_array($get('frequency'), ['weekly', 'custom_days'])),

                        Forms\Components\Select::make('preferred_delivery_slot')
                            ->label('Delivery Slot')
                            ->options(CustomerSubscription::slotLabels())
                            ->default('morning')
                            ->required()
                            ->live(),

                        Forms\Components\TimePicker::make('preferred_delivery_time')
                            ->label('Delivery Time')
                            ->visible(fn (Get $get) => $get('preferred_delivery_slot') === 'custom')
                            ->required(fn (Get $get) => $get('preferred_delivery_slot') === 'custom'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->default(today()->toDateString())
                            ->required(),

                        Forms\Components\DatePicker::make('next_delivery_date')
                            ->label('Next Delivery Date')
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->options(CustomerSubscription::statusLabels())
                            ->default('active')
                            ->required()
                            ->live(),
                    ]),
                ]),

            Section::make('Pause Information')
                ->icon('heroicon-o-pause-circle')
                ->visible(fn (Get $get) => $get('status') === 'paused')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('paused_from')->nullable(),
                        Forms\Components\DatePicker::make('paused_to')->nullable()->afterOrEqual('paused_from'),
                        Forms\Components\Textarea::make('pause_reason')->rows(2)->nullable()->columnSpanFull(),
                    ]),
                ]),

            Section::make('Notes')
                ->schema([
                    Forms\Components\Textarea::make('remarks')->rows(3)->nullable()->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subscription_no')
                    ->label('Sub No')->searchable()->sortable()->copyable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('customer.customer_id')
                    ->label('Customer ID')->searchable()->sortable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('customer.mobile')
                    ->label('Mobile')->searchable(),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('quantity')->sortable(),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge()
                    ->colors([
                        'info'    => 'daily',
                        'success' => 'weekly',
                        'warning' => 'custom_days',
                        'gray'    => 'monthly',
                    ]),

                Tables\Columns\TextColumn::make('preferred_delivery_slot')
                    ->label('Slot')->badge()
                    ->colors(['info' => 'morning', 'warning' => 'afternoon', 'gray' => 'evening', 'danger' => 'custom']),

                Tables\Columns\TextColumn::make('next_delivery_date')
                    ->label('Next Delivery')->date()->sortable()->placeholder('—'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'paused',
                        'danger'  => 'cancelled',
                        'gray'    => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('paused_from')->date()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('paused_to')->date()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(CustomerSubscription::statusLabels()),
                Tables\Filters\SelectFilter::make('frequency')->options(CustomerSubscription::frequencyLabels()),
                Tables\Filters\SelectFilter::make('product_id')->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))->searchable(),
                Tables\Filters\SelectFilter::make('customer_id')->label('Customer')
                    ->options(fn () => Customer::orderBy('name')->pluck('name', 'id'))->searchable(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                Actions\Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->visible(fn (CustomerSubscription $r) => $r->isActive())
                    ->form([
                        Forms\Components\DatePicker::make('paused_from')->default(today()->toDateString())->required(),
                        Forms\Components\DatePicker::make('paused_to')->nullable()->label('Resume On (optional)'),
                        Forms\Components\Textarea::make('pause_reason')->rows(2)->nullable(),
                    ])
                    ->action(function (CustomerSubscription $record, array $data) {
                        $record->pause($data['pause_reason'] ?? null, $data['paused_from'], $data['paused_to'] ?? null);
                        Notification::make()->title('Subscription paused')->warning()->send();
                    }),

                Actions\Action::make('resume')
                    ->label('Resume')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn (CustomerSubscription $r) => $r->isPaused())
                    ->requiresConfirmation()
                    ->action(function (CustomerSubscription $record) {
                        $record->resume();
                        Notification::make()->title('Subscription resumed')->success()->send();
                    }),

                Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CustomerSubscription $r) => in_array($r->status, ['active', 'paused']))
                    ->requiresConfirmation()
                    ->action(function (CustomerSubscription $record) {
                        $record->cancel();
                        Notification::make()->title('Subscription cancelled')->send();
                    }),

                Actions\Action::make('recalculate')
                    ->label('Recalculate Next Date')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn (CustomerSubscription $r) => $r->isActive())
                    ->action(function (CustomerSubscription $record) {
                        $next = $record->calculateNextDeliveryDate();
                        $record->update(['next_delivery_date' => $next?->toDateString()]);
                        Notification::make()->title('Next delivery date updated')->success()->send();
                    }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                ])->label('Actions'),
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
            'index'  => Pages\ListCustomerSubscriptions::route('/'),
            'create' => Pages\CreateCustomerSubscription::route('/create'),
            'edit'   => Pages\EditCustomerSubscription::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
