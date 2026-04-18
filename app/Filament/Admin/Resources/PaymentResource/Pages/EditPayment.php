<?php

namespace App\Filament\Admin\Resources\PaymentResource\Pages;

use App\Filament\Admin\Resources\PaymentResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear irrelevant FK
        if ($data['payment_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        // Validate: prevent overpayment (account for this payment's own amount)
        if (! empty($data['invoice_id'])) {
            $invoice    = Invoice::find($data['invoice_id']);
            $currentAmt = (float) $this->record->amount;
            $maxAllowed = (float) ($invoice?->due_amount ?? 0) + $currentAmt;

            if ($invoice && (float) $data['amount'] > $maxAllowed) {
                Notification::make()
                    ->title("Amount exceeds invoice due. Capped at ৳{$maxAllowed}.")
                    ->warning()
                    ->send();
                $data['amount'] = $maxAllowed;
            }
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
