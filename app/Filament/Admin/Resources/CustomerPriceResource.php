<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerPriceResource\Pages;
use App\Models\Customer;
use App\Models\CustomerPrice;
use App\Models\Product;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerPriceResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'customer_prices.view';
    protected static string $createPermission = 'customer_prices.manage';
    protected static string $editPermission   = 'customer_prices.manage';
    protected static string $deletePermission = 'customer_prices.manage';

    protected static ?string $model = CustomerPrice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Customer Pricing';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer Price Information')
                ->icon('heroicon-o-tag')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::orderBy('name')
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

                        Forms\Components\TextInput::make('custom_price')
                            ->label('Custom Price (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->minValue(0)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options(CustomerPrice::statusLabels())
                            ->default('active')
                            ->required(),

                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->nullable(),

                        Forms\Components\DatePicker::make('effective_to')
                            ->label('Effective To')
                            ->nullable()
                            ->afterOrEqual('effective_from'),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.customer_id')
                    ->label('Customer ID')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.mobile')
                    ->label('Mobile')
                    ->searchable(),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('custom_price')
                    ->label('Price (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('effective_from')
                    ->label('From')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('effective_to')
                    ->label('To')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),

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
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->options(fn () => Customer::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(CustomerPrice::statusLabels()),

                Tables\Filters\Filter::make('currently_effective')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query) => $query->currentlyEffective()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomerPrices::route('/'),
            'create' => Pages\CreateCustomerPrice::route('/create'),
            'edit'   => Pages\EditCustomerPrice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
