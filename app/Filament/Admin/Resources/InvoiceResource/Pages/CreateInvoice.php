<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['invoice_no'] = Invoice::generateInvoiceNo();
        $data['created_by'] = Auth::id();

        // Clear irrelevant FK
        if ($data['invoice_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        // Calculate totals
        $subtotal    = (float) ($data['subtotal'] ?? 0);
        $previousDue = (float) ($data['previous_due'] ?? 0);
        $total       = $subtotal + $previousDue;
        $paid        = (float) ($data['paid_amount'] ?? 0);

        $data['total_amount'] = $total;
        $data['due_amount']   = max(0, $total - $paid);

        return $data;
    }

    protected function afterCreate(): void
    {
        // If issued on create, update party due
        if (! in_array($this->record->invoice_status, ['draft', 'cancelled'])) {
            $this->record->updatePartyDue();
        }
    }
}
