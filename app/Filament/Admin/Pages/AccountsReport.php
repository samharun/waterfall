<?php

namespace App\Filament\Admin\Pages;

use App\Models\AccountCategory;
use App\Models\AccountTransaction;
use App\Models\Investor;
use App\Models\PaymentAccount;
use App\Models\StaffSalaryPayment;
use App\Models\Supplier;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AccountsReport extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'accounts.reports.view';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Reports';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.admin.pages.accounts-report';

    public ?string $start_date = null;
    public ?string $end_date = null;
    public ?int $category_id = null;
    public ?int $payment_account_id = null;
    public ?string $transaction_type = null;
    public ?string $status = 'approved';

    public function mount(): void
    {
        $this->start_date = now()->startOfMonth()->toDateString();
        $this->end_date = now()->toDateString();
    }

    public function transactions(): Collection
    {
        return AccountTransaction::with(['category', 'paymentAccount'])
            ->when($this->start_date, fn ($q) => $q->whereDate('transaction_date', '>=', $this->start_date))
            ->when($this->end_date, fn ($q) => $q->whereDate('transaction_date', '<=', $this->end_date))
            ->when($this->category_id, fn ($q) => $q->where('account_category_id', $this->category_id))
            ->when($this->payment_account_id, fn ($q) => $q->where('payment_account_id', $this->payment_account_id))
            ->when($this->transaction_type, fn ($q) => $q->where('transaction_type', $this->transaction_type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('transaction_date')
            ->get();
    }

    public function categories(): Collection
    {
        return AccountCategory::orderBy('type')->orderBy('name')->get();
    }

    public function paymentAccounts(): Collection
    {
        return PaymentAccount::orderBy('name')->get();
    }

    public function categoryExpense(): Collection
    {
        return $this->transactions()
            ->where('transaction_type', 'expense')
            ->groupBy(fn ($row) => $row->category?->name ?? 'Uncategorized')
            ->map(fn ($rows) => $rows->sum('amount'));
    }

    public function salaryPayments(): Collection
    {
        return StaffSalaryPayment::whereBetween('payment_date', [$this->start_date, $this->end_date])->orderByDesc('payment_date')->get();
    }

    public function investors(): Collection
    {
        return Investor::orderBy('name')->get();
    }

    public function suppliers(): Collection
    {
        return Supplier::orderByDesc('current_due')->get();
    }
}
