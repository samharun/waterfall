<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $customer = auth()->user()->customer;

        $invoices = $customer->invoices()
            ->whereNotIn('invoice_status', ['draft'])
            ->latest('invoice_date')
            ->paginate(15);

        return view('customer.invoices.index', compact('customer', 'invoices'));
    }

    public function show(Invoice $invoice)
    {
        $customer = auth()->user()->customer;

        abort_if($invoice->customer_id !== $customer->id, 403);

        $invoice->load('payments');

        return view('customer.invoices.show', compact('invoice'));
    }
}
