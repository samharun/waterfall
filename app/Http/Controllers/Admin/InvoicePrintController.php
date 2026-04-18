<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\SettingsService;

class InvoicePrintController extends Controller
{
    public function print(Invoice $invoice)
    {
        abort_unless(auth()->user()?->can('invoices.print'), 403);

        $invoice->load(['customer.zone', 'dealer.zone', 'payments']);

        $settings = app(SettingsService::class);

        return view('prints.invoice', [
            'invoice'  => $invoice,
            'company'  => $settings->company(),
            'branding' => $settings->branding(),
            'billing'  => $settings->billing(),
            'logoUrl'  => $settings->logoUrl(),
            'context'  => 'admin',
        ]);
    }
}
