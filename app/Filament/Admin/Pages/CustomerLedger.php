<?php

namespace App\Filament\Admin\Pages;

use App\Models\Customer;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CustomerLedger extends Page implements HasForms
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'reports.customer_ledger.view';
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Customer Ledger';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.admin.pages.customer-ledger';

    public ?int    $customer_id = null;
    public ?string $date_from   = null;
    public ?string $date_until  = null;

    public function getLedgerEntries(): Collection
    {
        if (! $this->customer_id) {
            return collect();
        }

        $entries = collect();

        // Invoice entries (Debit)
        $invoices = \App\Models\Invoice::where('customer_id', $this->customer_id)
            ->whereNotIn('invoice_status', ['draft', 'cancelled'])
            ->when($this->date_from,  fn ($q) => $q->whereDate('invoice_date', '>=', $this->date_from))
            ->when($this->date_until, fn ($q) => $q->whereDate('invoice_date', '<=', $this->date_until))
            ->get();

        foreach ($invoices as $inv) {
            $entries->push([
                'date'        => $inv->invoice_date,
                'reference'   => $inv->invoice_no,
                'description' => 'Invoice',
                'debit'       => (float) $inv->total_amount,
                'credit'      => 0.0,
                'type'        => 'invoice',
            ]);
        }

        // Payment entries (Credit)
        $payments = \App\Models\Payment::where('customer_id', $this->customer_id)
            ->when($this->date_from,  fn ($q) => $q->whereDate('payment_date', '>=', $this->date_from))
            ->when($this->date_until, fn ($q) => $q->whereDate('payment_date', '<=', $this->date_until))
            ->get();

        foreach ($payments as $pay) {
            $entries->push([
                'date'        => $pay->payment_date,
                'reference'   => $pay->payment_no,
                'description' => 'Payment (' . ucfirst($pay->payment_method) . ')',
                'debit'       => 0.0,
                'credit'      => (float) $pay->amount,
                'type'        => 'payment',
            ]);
        }

        // Sort by date ascending
        return $entries->sortBy('date')->values();
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer_id ? Customer::find($this->customer_id) : null;
    }
}
