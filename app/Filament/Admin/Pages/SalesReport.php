<?php

namespace App\Filament\Admin\Pages;

use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Payment;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SalesReport extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'reports.sales.view';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Sales Report';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.admin.pages.sales-report';

    // Filter state
    public ?string $date_from    = null;
    public ?string $date_until   = null;
    public ?string $payment_type = null;
    public ?string $payment_method = null;
    public ?int    $customer_id  = null;
    public ?int    $dealer_id    = null;
    public ?int    $received_by  = null;

    public function mount(): void
    {
        $this->date_from  = Carbon::now()->startOfMonth()->toDateString();
        $this->date_until = Carbon::now()->toDateString();
    }

    public function getPayments(): Collection
    {
        return Payment::with(['customer', 'dealer', 'invoice', 'receivedBy'])
            ->when($this->date_from,      fn ($q) => $q->whereDate('payment_date', '>=', $this->date_from))
            ->when($this->date_until,     fn ($q) => $q->whereDate('payment_date', '<=', $this->date_until))
            ->when($this->payment_type,   fn ($q) => $q->where('payment_type', $this->payment_type))
            ->when($this->payment_method, fn ($q) => $q->where('payment_method', $this->payment_method))
            ->when($this->customer_id,    fn ($q) => $q->where('customer_id', $this->customer_id))
            ->when($this->dealer_id,      fn ($q) => $q->where('dealer_id', $this->dealer_id))
            ->when($this->received_by,    fn ($q) => $q->where('received_by', $this->received_by))
            ->orderByDesc('payment_date')
            ->get();
    }

    public function getTotalAmount(): float
    {
        return (float) $this->getPayments()->sum('amount');
    }

    public function getPaymentCount(): int
    {
        return $this->getPayments()->count();
    }

    public function filter(): void
    {
        // Livewire re-renders automatically on property change
    }
}
