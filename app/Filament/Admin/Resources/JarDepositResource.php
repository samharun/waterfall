<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JarDepositResource\Pages;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\JarDeposit;
use App\Models\Product;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
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

class JarDepositResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'jar_deposits.view';
    protected static string $createPermission = 'jar_deposits.create';
    protected static string $editPermission   = 'jar_deposits.update';
    protected static string $deletePermission = 'jar_deposits.delete';

    protected static ?string $model = JarDeposit::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Jar Deposit Tracking';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Party Information')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('deposit_no')
                            ->label('Deposit No')
                            ->disabled()->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('party_type')
                            ->label('Party Type')
                            ->options(JarDeposit::partyTypeLabels())
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
                            ->visible(fn (Get $get) => $get('party_type') === 'customer')
                            ->required(fn (Get $get) => $get('party_type') === 'customer'),

                        Forms\Components\Select::make('dealer_id')
                            ->label('Dealer')
                            ->options(fn () => Dealer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Dealer $d) => [
                                    $d->id => "{$d->dealer_code} — {$d->name} ({$d->mobile})",
                                ])
                            )
                            ->searchable()
                            ->visible(fn (Get $get) => $get('party_type') === 'dealer')
                            ->required(fn (Get $get) => $get('party_type') === 'dealer'),
                    ]),
                ]),

            Section::make('Deposit Transaction')
                ->icon('heroicon-o-archive-box')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\Select::make('product_id')
                            ->label('Product')
                            ->options(fn () => Product::active()
                                ->where('product_type', 'jar')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Product $p) => [
                                    $p->id => "[{$p->sku}] {$p->name}",
                                ])
                            )
                            ->searchable()
                            ->required()
                            ->helperText('Showing jar-type products.'),

                        Forms\Components\Select::make('transaction_type')
                            ->label('Transaction Type')
                            ->options(JarDeposit::typeLabels())
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('adjustment_direction')
                            ->label('Adjustment Direction')
                            ->options(JarDeposit::directionLabels())
                            ->visible(fn (Get $get) => $get('transaction_type') === 'adjustment')
                            ->required(fn (Get $get) => $get('transaction_type') === 'adjustment'),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()->integer()->minValue(1)->required(),

                        Forms\Components\TextInput::make('deposit_amount')
                            ->label('Deposit Amount (৳)')
                            ->numeric()->prefix('৳')->minValue(0)->default(0),

                        Forms\Components\DatePicker::make('transaction_date')
                            ->label('Transaction Date')
                            ->default(now()->toDateString())->required(),

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
                Tables\Columns\TextColumn::make('deposit_no')
                    ->label('Deposit No')
                    ->searchable()->sortable()->copyable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('party_type')
                    ->label('Party')
                    ->badge()
                    ->colors(['info' => 'customer', 'warning' => 'dealer']),

                Tables\Columns\TextColumn::make('party_name')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (JarDeposit $record): string {
                        if ($record->party_type === 'customer' && $record->customer) {
                            return "{$record->customer->customer_id} — {$record->customer->name}";
                        }
                        if ($record->party_type === 'dealer' && $record->dealer) {
                            return "{$record->dealer->dealer_code} — {$record->dealer->name}";
                        }
                        return '—';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('customer', fn ($cq) => $cq
                                ->where('customer_id', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            )->orWhereHas('dealer', fn ($dq) => $dq
                                ->where('dealer_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            );
                        });
                    }),

                Tables\Columns\TextColumn::make('product.sku')
                    ->label('SKU')->searchable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')->searchable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'deposit_received',
                        'info'    => 'jar_issued',
                        'warning' => 'jar_returned',
                        'gray'    => 'adjustment',
                    ]),

                Tables\Columns\TextColumn::make('adjustment_direction')
                    ->label('Direction')
                    ->badge()
                    ->colors(['success' => 'increase', 'danger' => 'decrease'])
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('quantity')->sortable(),

                Tables\Columns\TextColumn::make('deposit_amount')
                    ->label('Deposit (৳)')->numeric(2)->sortable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')->date()->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true)->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('party_type')
                    ->options(JarDeposit::partyTypeLabels()),

                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options(JarDeposit::typeLabels()),

                Tables\Filters\SelectFilter::make('adjustment_direction')
                    ->options(JarDeposit::directionLabels()),

                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

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
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
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
            'index'  => Pages\ListJarDeposits::route('/'),
            'create' => Pages\CreateJarDeposit::route('/create'),
            'edit'   => Pages\EditJarDeposit::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}

