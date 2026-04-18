<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockTransactionResource\Pages;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
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

class StockTransactionResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'stock_transactions.view';
    protected static string $createPermission = 'stock_transactions.create';
    protected static string $editPermission   = 'stock_transactions.update';
    protected static string $deletePermission = 'stock_transactions.delete';

    protected static ?string $model = StockTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-up-down';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock Transactions';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Stock Transaction')
                ->icon('heroicon-o-arrows-up-down')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('transaction_no')
                            ->label('Transaction No')
                            ->disabled()->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

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

                        Forms\Components\Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options(StockTransaction::typeLabels())
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('adjustment_direction')
                            ->label('Adjustment Direction')
                            ->options(StockTransaction::directionLabels())
                            ->visible(fn (Get $get) => $get('transaction_type') === 'adjustment')
                            ->required(fn (Get $get) => $get('transaction_type') === 'adjustment'),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()->integer()->minValue(1)->required(),

                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Unit Cost (৳)')
                            ->numeric()->prefix('৳')->minValue(0)->nullable(),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->default(now()->toDateString())->required(),

                        Forms\Components\TextInput::make('reference_type')
                            ->label('Reference Type')
                            ->maxLength(100)->nullable(),

                        Forms\Components\TextInput::make('reference_id')
                            ->label('Reference ID')
                            ->numeric()->nullable(),

                        Forms\Components\Textarea::make('remarks')
                            ->rows(3)->nullable()->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_no')
                    ->label('Txn No')
                    ->searchable()->sortable()->copyable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()->sortable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()->sortable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'stock_in',
                        'danger'  => 'stock_out',
                        'warning' => 'adjustment',
                        'danger'  => 'damaged',
                        'info'    => 'returned',
                    ]),

                Tables\Columns\TextColumn::make('adjustment_direction')
                    ->label('Direction')
                    ->badge()
                    ->colors(['success' => 'increase', 'danger' => 'decrease'])
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('quantity')->sortable(),

                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Unit Cost (৳)')
                    ->numeric(2)->sortable()->placeholder('—'),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')->date()->sortable(),

                Tables\Columns\TextColumn::make('reference_type')
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('reference_id')
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options(StockTransaction::typeLabels()),

                Tables\Filters\SelectFilter::make('adjustment_direction')
                    ->options(StockTransaction::directionLabels()),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('transaction_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('transaction_date', '<=', $data['until']))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
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
            'index'  => Pages\ListStockTransactions::route('/'),
            'create' => Pages\CreateStockTransaction::route('/create'),
            'edit'   => Pages\EditStockTransaction::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
