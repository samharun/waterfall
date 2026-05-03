<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ExpenseResource\Pages;
use App\Models\AccountCategory;
use App\Models\AccountTransaction;
use App\Models\PaymentAccount;
use App\Services\Accounts\AccountTransactionService;
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

class ExpenseResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.expense.view';
    protected static string $createPermission = 'accounts.expense.create';
    protected static string $editPermission = 'accounts.expense.edit';
    protected static string $deletePermission = 'accounts.expense.delete';

    protected static ?string $model = AccountTransaction::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Expenses';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Expense')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('transaction_no')->disabled()->dehydrated(false)->visibleOn('edit'),
                        Forms\Components\DatePicker::make('transaction_date')->default(now()->toDateString())->required(),
                        Forms\Components\Select::make('account_category_id')
                            ->label('Category')
                            ->options(fn () => AccountCategory::active()->where('type', 'expense')->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(),
                        Forms\Components\Select::make('payment_account_id')
                            ->label('Payment Account')
                            ->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(),
                        Forms\Components\TextInput::make('amount')->numeric()->prefix('BDT')->minValue(0.01)->required(),
                        Forms\Components\TextInput::make('paid_to')->maxLength(255),
                        Forms\Components\TextInput::make('reference_no')->maxLength(255),
                        Forms\Components\Select::make('status')->options(AccountTransaction::statusLabels())->default('approved')->required(),
                        Forms\Components\FileUpload::make('attachment')->directory('accounts/vouchers')->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(IncomeResource::transactionColumns())
            ->filters(IncomeResource::transactionFilters('expense'))
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make()->using(fn (AccountTransaction $record) => app(AccountTransactionService::class)->deleteTransaction($record)),
                    Actions\RestoreAction::make(),
                ]),
            ])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('transaction_type', 'expense');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
