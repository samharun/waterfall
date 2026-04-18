<?php

namespace App\Filament\Admin\Resources\CustomerSubscriptionResource\Pages;

use App\Filament\Admin\Resources\CustomerSubscriptionResource;
use App\Models\CustomerSubscription;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCustomerSubscription extends CreateRecord
{
    protected static string $resource = CustomerSubscriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['subscription_no'] = CustomerSubscription::generateSubscriptionNo();
        $data['created_by']      = Auth::id();
        $data['updated_by']      = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Auto-calculate next delivery date if not set
        if (! $this->record->next_delivery_date) {
            $next = $this->record->calculateNextDeliveryDate();
            $this->record->update(['next_delivery_date' => $next?->toDateString()]);
        }
    }
}
