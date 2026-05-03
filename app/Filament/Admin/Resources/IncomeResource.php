<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IncomeResource\Pages;
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

class IncomeResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.income.view';
    protected static string $createPermission = 'accounts.income.create';
    protected static string $editPermission = 'accounts.income.edit';
    protected static string $deletePermission = 'accounts.income.delete';

    protected static ?string $model = AccountTransaction::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Income';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Income')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('transaction_no')->disabled()->dehydrated(false)->visibleOn('edit'),
                        Forms\Components\DatePicker::make('transaction_date')->default(now()->toDateString())->required(),
                        Forms\Components\Select::make('account_category_id')
                            ->label('Category')
                            ->options(fn () => AccountCategory::active()->where('type', 'income')->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(),
                        Forms\Components\Select::make('payment_account_id')
                            ->label('Payment Account')
                            ->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(),
                        Forms\Components\TextInput::make('amount')->numeric()->prefix('BDT')->minValue(0.01)->required(),
                        Forms\Components\TextInput::make('received_from')->maxLength(255),
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
            ->columns(static::transactionColumns())
            ->filters(static::transactionFilters('income'))
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
        return parent::getEloquentQuery()->where('transaction_type', 'income');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }

    public static function transactionColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('transaction_date')->label('Date')->date()->sortable(),
            Tables\Columns\TextColumn::make('transaction_no')->label('Transaction No')->searchable()->copyable()->fontFamily('mono'),
            Tables\Columns\TextColumn::make('category.name')->label('Category')->searchable(),
            Tables\Columns\TextColumn::make('amount')->money('BDT')->sortable(),
            Tables\Columns\TextColumn::make('paymentAccount.name')->label('Payment Account')->sortable(),
            Tables\Columns\TextColumn::make('status')->badge()->colors(['warning' => 'pending', 'success' => 'approved', 'danger' => 'rejected']),
            Tables\Columns\TextColumn::make('createdBy.name')->label('Created By')->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function transactionFilters(string $type): array
    {
        return [
            Tables\Filters\SelectFilter::make('account_category_id')->label('Category')->options(fn () => AccountCategory::where('type', $type)->orderBy('name')->pluck('name', 'id'))->searchable(),
            Tables\Filters\SelectFilter::make('payment_account_id')->label('Payment Account')->options(fn () => PaymentAccount::orderBy('name')->pluck('name', 'id'))->searchable(),
            Tables\Filters\SelectFilter::make('status')->options(AccountTransaction::statusLabels()),
            Tables\Filters\Filter::make('quick_date')
                ->form([Forms\Components\Select::make('range')->options(['today' => 'Today', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'])])
                ->query(fn (Builder $q, array $data) => match ($data['range'] ?? null) {
                    'today' => $q->whereDate('transaction_date', today()),
                    'this_month' => $q->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()]),
                    'last_month' => $q->whereBetween('transaction_date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()]),
                    'this_year' => $q->whereYear('transaction_date', now()->year),
                    default => $q,
                }),
            Tables\Filters\Filter::make('transaction_date')
                ->form([Forms\Components\DatePicker::make('from'), Forms\Components\DatePicker::make('until')])
                ->query(fn (Builder $q, array $data) => $q
                    ->when($data['from'] ?? null, fn ($q) => $q->whereDate('transaction_date', '>=', $data['from']))
                    ->when($data['until'] ?? null, fn ($q) => $q->whereDate('transaction_date', '<=', $data['until']))),
            Tables\Filters\TrashedFilter::make(),
        ];
    }
}
