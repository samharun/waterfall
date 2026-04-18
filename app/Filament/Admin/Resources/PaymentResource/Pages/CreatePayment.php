<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['payment_no'] = Payment::generatePaymentNo();
        $data['received_by'] = $data['received_by'] ?? Auth::id();

        // Clear irrelevant FK
        if ($data['payment_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        // Validate: prevent overpayment against invoice
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);
            if ($invoice && (float) $data['amount'] > (float) $invoice->due_amount) {
                Notification::make()
                    ->title("Amount exceeds invoice due (৳{$invoice->due_amount}). Capped.")
                    ->warning()
                    ->send();
                $data['amount'] = $invoice->due_amount;
            }
        }

        return $data;
    }
}
