<?php

namespace App\Filament\Admin\Resources\PaymentAccountResource\Pages;

use App\Filament\Admin\Resources\PaymentAccountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePaymentAccount extends CreateRecord
{
    protected static string $resource = PaymentAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['current_balance']);
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }
}
