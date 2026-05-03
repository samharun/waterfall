<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JarPurchaseResource\Pages;
use App\Models\JarPurchase;
use App\Models\PaymentAccount;
use App\Models\Supplier;
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

class JarPurchaseResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.jar_purchases.view';
    protected static string $createPermission = 'accounts.jar_purchases.manage';
    protected static string $editPermission = 'accounts.jar_purchases.manage';
    protected static string $deletePermission = 'accounts.jar_purchases.manage';

    protected static ?string $model = JarPurchase::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Jar Purchases';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        $recalculate = function (Get $get, Set $set): void {
            $total = (int) $get('quantity') * (float) $get('unit_price');
            $paid = (float) $get('paid_amount');
            $set('total_amount', $total);
            $set('due_amount', max($total - $paid, 0));
            $set('payment_status', $paid <= 0 ? 'unpaid' : ($paid >= $total ? 'paid' : 'partial'));
        };

        return $schema->components([
            Section::make('Jar Purchase')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('purchase_date')->default(now()->toDateString())->required(),
                        Forms\Components\Select::make('supplier_id')->label('Supplier')->options(fn () => Supplier::orderBy('name')->pluck('name', 'id'))->searchable(),
                        Forms\Components\TextInput::make('supplier_name')->maxLength(255),
                        Forms\Components\TextInput::make('jar_type')->maxLength(255),
                        Forms\Components\TextInput::make('quantity')->numeric()->integer()->default(0)->minValue(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('unit_price')->numeric()->prefix('BDT')->default(0)->minValue(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('total_amount')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('BDT')->default(0)->minValue(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('due_amount')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\Select::make('payment_status')->options(['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid'])->default('unpaid'),
                        Forms\Components\Select::make('payment_account_id')->label('Payment Account')->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))->searchable()->required(fn (Get $get) => (float) $get('paid_amount') > 0),
                        Forms\Components\FileUpload::make('attachment')->directory('accounts/jar-purchases')->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('note')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->placeholder(fn (JarPurchase $record) => $record->supplier_name ?: '-')->searchable(),
                Tables\Columns\TextColumn::make('jar_type')->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity')->sortable(),
                Tables\Columns\TextColumn::make('total_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('due_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('payment_status')->badge()->colors(['success' => 'paid', 'warning' => 'partial', 'danger' => 'unpaid']),
            ])
            ->filters([Tables\Filters\SelectFilter::make('payment_status')->options(['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid']), Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->after(function (JarPurchase $record) {
                    if ($record->accountTransaction) {
                        app(\App\Services\Accounts\AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                    }
                    $record->supplier?->recalculateDue();
                }),
            ])])
            ->defaultSort('purchase_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJarPurchases::route('/'),
            'create' => Pages\CreateJarPurchase::route('/create'),
            'edit' => Pages\EditJarPurchase::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
