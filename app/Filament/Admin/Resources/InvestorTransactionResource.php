<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvestorTransactionResource\Pages;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Models\PaymentAccount;
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

class InvestorTransactionResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.investors.view';
    protected static string $createPermission = 'accounts.investors.manage';
    protected static string $editPermission = 'accounts.investors.manage';
    protected static string $deletePermission = 'accounts.investors.manage';

    protected static ?string $model = InvestorTransaction::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Investor Funds';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Investor Transaction')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('investor_id')->label('Investor')->options(fn () => Investor::orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                        Forms\Components\DatePicker::make('transaction_date')->default(now()->toDateString())->required(),
                        Forms\Components\Select::make('transaction_type')->options(InvestorTransaction::typeLabels())->default('investment_received')->required(),
                        Forms\Components\TextInput::make('amount')->numeric()->prefix('BDT')->minValue(0.01)->required(),
                        Forms\Components\Select::make('payment_account_id')->label('Payment Account')->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                        Forms\Components\TextInput::make('reference_no')->maxLength(255),
                        Forms\Components\FileUpload::make('attachment')->directory('accounts/investors')->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('note')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('investor.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')->badge()->colors(['success' => 'investment_received', 'danger' => 'return_paid', 'warning' => 'loan_repayment']),
                Tables\Columns\TextColumn::make('amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('paymentAccount.name')->label('Payment Account'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('transaction_type')->options(InvestorTransaction::typeLabels()), Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->after(function (InvestorTransaction $record) {
                    if ($record->accountTransaction) {
                        app(\App\Services\Accounts\AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                    }
                    $record->investor?->recalculateBalance();
                }),
            ])])
            ->defaultSort('transaction_date', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInvestorTransactions::route('/'), 'create' => Pages\CreateInvestorTransaction::route('/create'), 'edit' => Pages\EditInvestorTransaction::route('/{record}/edit')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
