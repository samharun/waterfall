<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'products.view';
    protected static string $createPermission = 'products.create';
    protected static string $editPermission   = 'products.update';
    protected static string $deletePermission = 'products.delete';

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Products';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Product Information')
                ->description('Core product details and pricing.')
                ->icon('heroicon-o-cube')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                if (! $record) {
                                    $set('sku', strtoupper(Str::slug($state, '-')));
                                }
                            }),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(100)
                            ->unique(Product::class, 'sku', ignoreRecord: true)
                            ->helperText('Auto-generated or enter manually.'),

                        Forms\Components\Select::make('product_type')
                            ->label('Product Type')
                            ->options(Product::typeLabels())
                            ->default('jar')
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('status')
                            ->options(Product::statusLabels())
                            ->default('active')
                            ->required(),

                        Forms\Components\TextInput::make('default_price')
                            ->label('Default Price')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Deposit Amount')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Applicable for jar and bottle types.'),

                        Forms\Components\TextInput::make('stock_alert_qty')
                            ->label('Stock Alert Qty')
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0),

                        Forms\Components\TextInput::make('current_stock')
                            ->label('Current Stock')
                            ->numeric()
                            ->integer()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Updated automatically via Stock Transactions.'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'info'    => 'jar',
                        'success' => 'bottle',
                        'warning' => 'accessory',
                    ]),

                Tables\Columns\TextColumn::make('default_price')
                    ->label('Price (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Deposit (৳)')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('stock_alert_qty')
                    ->label('Alert Qty')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (Product $record) => $record->isLowStock() ? 'danger' : null)
                    ->description(fn (Product $record) => $record->isLowStock() ? '⚠ Low Stock' : null),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'gray'    => 'inactive',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('Product Type')
                    ->options(Product::typeLabels()),

                Tables\Filters\SelectFilter::make('status')
                    ->options(Product::statusLabels()),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn (Builder $q) => $q->where('status', 'active')
                        ->whereColumn('current_stock', '<=', 'stock_alert_qty')
                    )
                    ->toggle(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                Actions\Action::make('recalculate_stock')
                    ->label('Recalculate Stock')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(function (Product $record) {
                        $record->recalculateCurrentStock();
                        Notification::make()->title('Stock recalculated')->success()->send();
                    }),

                Actions\Action::make('quick_stock_in')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()->integer()->minValue(1)->required(),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Unit Cost (৳)')->numeric()->prefix('৳')->nullable(),
                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Date')->default(now()->toDateString())->required(),
                        Forms\Components\Textarea::make('remarks')->rows(2)->nullable(),
                    ])
                    ->action(function (Product $record, array $data) {
                        StockTransaction::create([
                            'transaction_no'   => StockTransaction::generateTransactionNo(),
                            'product_id'       => $record->id,
                            'transaction_type' => 'stock_in',
                            'quantity'         => $data['quantity'],
                            'unit_cost'        => $data['unit_cost'] ?? null,
                            'transaction_date' => $data['transaction_date'],
                            'remarks'          => $data['remarks'] ?? null,
                            'created_by'       => Auth::id(),
                        ]);
                        Notification::make()->title('Stock added')->success()->send();
                    }),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                    Actions\ForceDeleteAction::make(),
                ])->label('Actions'),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}

