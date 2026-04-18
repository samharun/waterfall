<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    use ApiResponse;

    // ── GET /api/customer/bills ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user();

        $bills = Invoice::forCustomer($customer->id)
            ->whereNotIn('invoice_status', ['draft'])
            ->orderByDesc('invoice_date')
            ->paginate(15);

        $items = $bills->getCollection()->map(fn (Invoice $inv) => [
            'id'             => $inv->id,
            'invoice_no'     => $inv->invoice_no,
            'billing_month'  => $inv->billing_month,
            'billing_year'   => $inv->billing_year,
            'month_label'    => $this->monthLabel($inv->billing_month, $inv->billing_year, $locale),
            'invoice_date'   => $inv->invoice_date?->toDateString(),
            'due_date'       => $inv->due_date?->toDateString(),
            'total_amount'   => (float) $inv->total_amount,
            'paid_amount'    => (float) $inv->paid_amount,
            'due_amount'     => (float) $inv->due_amount,
            'status'         => $inv->invoice_status,
            'status_label'   => $this->translateStatus($inv->invoice_status, $locale),
        ]);

        return $this->success('Bills retrieved.', [
            'bills'      => $items,
            'pagination' => [
                'current_page' => $bills->currentPage(),
                'last_page'    => $bills->lastPage(),
                'per_page'     => $bills->perPage(),
                'total'        => $bills->total(),
            ],
        ]);
    }

    // ── GET /api/customer/due-balance ──────────────────────────────

    public function dueBalance(Request $request): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user();

        $invoices = Invoice::forCustomer($customer->id)
            ->whereNotIn('invoice_status', ['cancelled', 'draft'])
            ->get();

        $totalBill = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalDue  = $invoices->sum('due_amount');

        $overdue = $invoices
            ->filter(fn (Invoice $inv) => $inv->due_date && $inv->due_date->isPast() && $inv->due_amount > 0)
            ->sum('due_amount');

        $latestInvoice = $invoices->sortByDesc('invoice_date')->first();

        return $this->success('Due balance retrieved.', [
            'total_bill_amount' => (float) $totalBill,
            'total_paid_amount' => (float) $totalPaid,
            'total_due'         => (float) $totalDue,
            'overdue_amount'    => (float) $overdue,
            'latest_bill'       => $latestInvoice ? [
                'invoice_no'    => $latestInvoice->invoice_no,
                'billing_month' => $latestInvoice->billing_month,
                'billing_year'  => $latestInvoice->billing_year,
                'month_label'   => $this->monthLabel($latestInvoice->billing_month, $latestInvoice->billing_year, $locale),
                'due_amount'    => (float) $latestInvoice->due_amount,
                'status'        => $latestInvoice->invoice_status,
                'status_label'  => $this->translateStatus($latestInvoice->invoice_status, $locale),
            ] : null,
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function monthLabel(?int $month, ?int $year, string $locale): ?string
    {
        if (! $month || ! $year) {
            return null;
        }

        if ($locale === 'bn') {
            $bnMonths = [
                1 => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ',
                4 => 'এপ্রিল',   5 => 'মে',           6 => 'জুন',
                7 => 'জুলাই',    8 => 'আগস্ট',        9 => 'সেপ্টেম্বর',
                10 => 'অক্টোবর', 11 => 'নভেম্বর',    12 => 'ডিসেম্বর',
            ];
            $bnDigits = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
            $bnYear   = strtr((string) $year, $bnDigits);
            return ($bnMonths[$month] ?? '') . ' ' . $bnYear;
        }

        return \Carbon\Carbon::create($year, $month)->format('F Y');
    }
}
