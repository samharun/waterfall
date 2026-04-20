<?php

namespace App\Filament\Admin\Resources\DeliveryResource\Pages;

use App\Filament\Admin\Resources\DeliveryResource;
use App\Models\Delivery;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditDelivery extends EditRecord
{
    protected static string $resource = DeliveryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // If staff assigned and assigned_at not set, fill it
        if (! empty($data['delivery_staff_id']) && empty($data['assigned_at'])) {
            $data['assigned_at'] = now();
            $data['assigned_by'] = $data['assigned_by'] ?? Auth::id();
        }

        // If delivered and delivered_at not set, fill it
        if (($data['delivery_status'] ?? '') === 'delivered' && empty($data['delivered_at'])) {
            $data['delivered_at'] = now();
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Order status sync and due recalculation are handled
        // centrally by Delivery::booted() updated hook.
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
