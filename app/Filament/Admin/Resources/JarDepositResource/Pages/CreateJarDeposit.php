<?php

namespace App\Filament\Admin\Resources\JarDepositResource\Pages;

use App\Filament\Admin\Resources\JarDepositResource;
use App\Models\JarDeposit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateJarDeposit extends CreateRecord
{
    protected static string $resource = JarDepositResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['deposit_no'] = JarDeposit::generateDepositNo();
        $data['created_by'] = Auth::id();

        // Clear irrelevant FK
        if ($data['party_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        // Clear adjustment_direction for non-adjustment types
        if ($data['transaction_type'] !== 'adjustment') {
            $data['adjustment_direction'] = null;
        }

        return $data;
    }
}
