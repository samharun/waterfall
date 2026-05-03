<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StaffSalaryPaymentResource\Pages;
use App\Models\PaymentAccount;
use App\Models\StaffSalaryPayment;
use App\Models\User;
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

class StaffSalaryPaymentResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.staff_salary.view';
    protected static string $createPermission = 'accounts.staff_salary.manage';
    protected static string $editPermission = 'accounts.staff_salary.manage';
    protected static string $deletePermission = 'accounts.staff_salary.manage';

    protected static ?string $model = StaffSalaryPayment::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Staff Salary';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        $recalculate = function (Get $get, Set $set): void {
            $net = (float) $get('basic_salary') + (float) $get('bonus') - (float) $get('deduction') - (float) $get('advance_deduction');
            $paid = (float) $get('paid_amount');
            $set('net_payable', max($net, 0));
            $set('due_amount', max($net - $paid, 0));
            $set('status', $paid <= 0 ? 'unpaid' : ($paid >= $net ? 'paid' : 'partial'));
        };

        return $schema->components([
            Section::make('Salary Payment')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\Select::make('staff_id')->label('Staff')->options(fn () => User::backOffice()->orderBy('name')->pluck('name', 'id'))->searchable(),
                        Forms\Components\TextInput::make('staff_name')->maxLength(255),
                        Forms\Components\TextInput::make('salary_month')->placeholder('2026-04')->required()->maxLength(7),
                        Forms\Components\DatePicker::make('payment_date')->default(now()->toDateString())->required(),
                        Forms\Components\TextInput::make('basic_salary')->numeric()->prefix('BDT')->default(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('bonus')->numeric()->prefix('BDT')->default(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('deduction')->numeric()->prefix('BDT')->default(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('advance_deduction')->numeric()->prefix('BDT')->default(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('net_payable')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('paid_amount')->numeric()->prefix('BDT')->default(0)->minValue(0)->live(onBlur: true)->afterStateUpdated($recalculate),
                        Forms\Components\TextInput::make('due_amount')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\Select::make('status')->options(StaffSalaryPayment::statusLabels())->default('unpaid')->required(),
                        Forms\Components\Select::make('payment_account_id')->label('Payment Account')->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                    ]),
                    Forms\Components\Textarea::make('note')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('salary_month')->sortable(),
                Tables\Columns\TextColumn::make('staff.name')->label('Staff')->placeholder(fn (StaffSalaryPayment $record) => $record->staff_name ?: '-')->searchable(),
                Tables\Columns\TextColumn::make('net_payable')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('due_amount')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->colors(['success' => 'paid', 'warning' => 'partial', 'danger' => 'unpaid']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(StaffSalaryPayment::statusLabels()),
                Tables\Filters\Filter::make('payment_date')->form([Forms\Components\DatePicker::make('from'), Forms\Components\DatePicker::make('until')])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                        ->when($data['until'] ?? null, fn ($q) => $q->whereDate('payment_date', '<=', $data['until']))),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([Actions\ActionGroup::make([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->after(function (StaffSalaryPayment $record) {
                    if ($record->accountTransaction) {
                        app(\App\Services\Accounts\AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                    }
                }),
            ])])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStaffSalaryPayments::route('/'),
            'create' => Pages\CreateStaffSalaryPayment::route('/create'),
            'edit' => Pages\EditStaffSalaryPayment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
