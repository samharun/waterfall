<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SettingsService;

class PaymentReceiptPrintController extends Controller
{
    public function print(Payment $payment)
    {
        abort_unless(auth()->user()?->can('payments.print'), 403);

        $payment->load(['customer.zone', 'dealer.zone', 'invoice', 'receivedBy']);

        $settings = app(SettingsService::class);

        return view('prints.payment-receipt', [
            'payment'  => $payment,
            'company'  => $settings->company(),
            'branding' => $settings->branding(),
            'billing'  => $settings->billing(),
            'logoUrl'  => $settings->logoUrl(),
            'context'  => 'admin',
        ]);
    }
}
