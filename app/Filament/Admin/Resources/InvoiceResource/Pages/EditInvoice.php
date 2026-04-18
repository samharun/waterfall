<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear irrelevant FK
        if ($data['invoice_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        // Recalculate totals (paid_amount comes from payments, keep existing)
        $subtotal    = (float) ($data['subtotal'] ?? 0);
        $previousDue = (float) ($data['previous_due'] ?? 0);
        $total       = $subtotal + $previousDue;
        $paid        = (float) $this->record->paid_amount; // authoritative from payments

        $data['total_amount'] = $total;
        $data['paid_amount']  = $paid;
        $data['due_amount']   = max(0, $total - $paid);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncStatus();
        $this->record->save();
        $this->record->updatePartyDue();
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
