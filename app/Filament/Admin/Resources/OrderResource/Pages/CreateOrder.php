<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate order number
        $data['order_no'] = Order::generateOrderNo();

        // Set ordered_by to current user
        $data['ordered_by'] = $data['ordered_by'] ?? Auth::id();

        // Clear irrelevant FK based on order_type
        if ($data['order_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate totals after items are saved via Repeater
        $this->record->recalculateTotals();
    }
}
