<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear irrelevant FK based on order_type
        if ($data['order_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate item line_totals then order totals
        foreach ($this->record->items as $item) {
            $lineTotal = (float) $item->quantity * (float) $item->unit_price;
            $item->update(['line_total' => $lineTotal]);
        }

        $this->record->recalculateTotals();
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
