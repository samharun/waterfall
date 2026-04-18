<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $dealer = auth()->user()->dealer->load('zone');

        $pendingOrders = $dealer->orders()
            ->whereIn('order_status', ['pending', 'confirmed', 'assigned'])
            ->count();

        $lastOrder = $dealer->orders()
            ->with('items.product')
            ->latest('order_date')
            ->first();

        $lastDelivery = $lastOrder?->delivery;

        $lastInvoice = $dealer->invoices()
            ->whereNotIn('invoice_status', ['draft', 'cancelled'])
            ->latest('invoice_date')
            ->first();

        $monthPayments = $dealer->payments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Jar deposit qty — calculate from records if column missing
        $jarDepositQty = $this->getJarDepositQty($dealer);

        return view('dealer.dashboard', compact(
            'dealer', 'pendingOrders', 'lastOrder', 'lastDelivery',
            'lastInvoice', 'monthPayments', 'jarDepositQty'
        ));
    }

    private function getJarDepositQty($dealer): int
    {
        // Try column first, fall back to calculation
        if (isset($dealer->jar_deposit_qty)) {
            return (int) $dealer->jar_deposit_qty;
        }

        return (int) $dealer->jarDeposits()->get()->sum(
            fn ($jd) => $jd->signedQuantity()
        );
    }
}
