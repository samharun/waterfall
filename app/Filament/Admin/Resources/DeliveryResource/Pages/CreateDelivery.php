<?php

namespace App\Filament\Admin\Resources\DeliveryResource\Pages;

use App\Filament\Admin\Resources\DeliveryResource;
use App\Models\Delivery;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDelivery extends CreateRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['delivery_no'] = Delivery::generateDeliveryNo();

        // If staff assigned, ensure assigned_by and assigned_at are set
        if (! empty($data['delivery_staff_id'])) {
            $data['assigned_by'] = $data['assigned_by'] ?? Auth::id();
            $data['assigned_at'] = $data['assigned_at'] ?? now();
            if (($data['delivery_status'] ?? 'pending') === 'pending') {
                $data['delivery_status'] = 'assigned';
            }
        }

        // If delivered, set delivered_at
        if (($data['delivery_status'] ?? '') === 'delivered' && empty($data['delivered_at'])) {
            $data['delivered_at'] = now();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Order status sync and due recalculation handled centrally
        // by Delivery::booted() created hook.
    }
}
