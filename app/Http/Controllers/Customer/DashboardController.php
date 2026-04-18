<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer->load('zone.deliveryManager');

        $pendingOrders = $customer->orders()
            ->whereIn('order_status', ['pending', 'confirmed', 'assigned'])
            ->count();

        $lastOrder = $customer->orders()
            ->with('items.product')
            ->latest('order_date')
            ->first();

        $lastDelivery = $lastOrder?->delivery;

        $lastInvoice = $customer->invoices()
            ->whereNotIn('invoice_status', ['draft', 'cancelled'])
            ->latest('invoice_date')
            ->first();

        $subscription = $customer->subscriptions()
            ->whereIn('status', ['active', 'paused'])
            ->latest()
            ->first();

        return view('customer.dashboard', compact(
            'customer', 'pendingOrders', 'lastOrder', 'lastDelivery', 'lastInvoice', 'subscription'
        ));
    }
}
