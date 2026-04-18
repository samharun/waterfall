<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $dealer = auth()->user()->dealer;

        $invoices = $dealer->invoices()
            ->whereNotIn('invoice_status', ['draft'])
            ->latest('invoice_date')
            ->paginate(15);

        return view('dealer.invoices.index', compact('dealer', 'invoices'));
    }

    public function show(Invoice $invoice)
    {
        $dealer = auth()->user()->dealer;

        abort_if($invoice->dealer_id !== $dealer->id, 403);

        $invoice->load('payments');

        return view('dealer.invoices.show', compact('invoice'));
    }
}
