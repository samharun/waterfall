<?php

namespace App\Filament\Admin\Pages;

use App\Models\AccountTransaction;
use App\Models\Investor;
use App\Models\PaymentAccount;
use App\Models\StaffSalaryPayment;
use App\Models\Supplier;
use BackedEnum;
use Filament\Pages\Page;

class AccountsDashboard extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'accounts.dashboard.view';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.admin.pages.accounts-dashboard';

    public function stats(): array
    {
        $today = today();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $approved = AccountTransaction::approved();

        $todayIncome = (clone $approved)->where('transaction_type', 'income')->whereDate('transaction_date', $today)->sum('amount');
        $todayExpense = (clone $approved)->where('transaction_type', 'expense')->whereDate('transaction_date', $today)->sum('amount');
        $monthIncome = (clone $approved)->where('transaction_type', 'income')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');
        $monthExpense = (clone $approved)->where('transaction_type', 'expense')->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount');

        return [
            'Opening Balance' => PaymentAccount::sum('opening_balance'),
            'Today Income' => $todayIncome,
            'Today Expense' => $todayExpense,
            'Current Balance' => PaymentAccount::sum('current_balance'),
            'This Month Income' => $monthIncome,
            'This Month Expense' => $monthExpense,
            'Net Profit/Loss' => (float) $monthIncome - (float) $monthExpense,
            'Total Investor Fund' => Investor::sum('total_invested'),
            'Supplier Due' => Supplier::sum('current_due'),
            'Staff Salary Paid This Month' => StaffSalaryPayment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('paid_amount'),
        ];
    }
}
