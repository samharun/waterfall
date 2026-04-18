<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DealerPriceResource\Pages;
use App\Models\Dealer;
use App\Models\DealerPrice;
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

class DealerPriceResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'dealer_prices.view';
    protected static string $createPermission = 'dealer_prices.manage';
    protected static string $editPermission   = 'dealer_prices.manage';
    protected static string $deletePermission = 'dealer_prices.manage';

    protected static ?string $model = DealerPrice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-bangladeshi';

    protected static string|\UnitEnum|null $navigationGroup = 'Dealer / Distributor';

    protected static ?string $navigationLabel = 'Dealer Pricing';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Dealer Price Information')
                ->icon('heroicon-o-currency-bangladeshi')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('dealer_id')
                            ->label('Dealer')
                            ->options(fn () => Dealer::orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Dealer $d) => [
                                    $d->id => "{$d->dealer_code} — {$d->name} ({$d->mobile})",
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
                            ->options(DealerPrice::statusLabels())
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
                Tables\Columns\TextColumn::make('dealer.dealer_code')
                    ->label('Dealer Code')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('dealer.name')
                    ->label('Dealer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dealer.mobile')
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
                Tables\Filters\SelectFilter::make('dealer_id')
                    ->label('Dealer')
                    ->options(fn () => Dealer::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(DealerPrice::statusLabels()),

                Tables\Filters\Filter::make('currently_effective')
                    ->label('Currently Effective')
                    ->query(fn (Builder $query) => $query->currentlyEffective()),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
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
            'index'  => Pages\ListDealerPrices::route('/'),
            'create' => Pages\CreateDealerPrice::route('/create'),
            'edit'   => Pages\EditDealerPrice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
