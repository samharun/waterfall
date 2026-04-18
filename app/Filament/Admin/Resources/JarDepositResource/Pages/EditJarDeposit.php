<?php

namespace App\Filament\Admin\Resources\JarDepositResource\Pages;

use App\Filament\Admin\Resources\JarDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJarDeposit extends EditRecord
{
    protected static string $resource = JarDepositResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['party_type'] === 'customer') {
            $data['dealer_id'] = null;
        } else {
            $data['customer_id'] = null;
        }

        if ($data['transaction_type'] !== 'adjustment') {
            $data['adjustment_direction'] = null;
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
