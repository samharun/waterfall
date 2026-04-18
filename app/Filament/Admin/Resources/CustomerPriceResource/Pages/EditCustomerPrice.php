<?php

namespace App\Filament\Admin\Resources\CustomerPriceResource\Pages;

use App\Filament\Admin\Resources\CustomerPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerPrice extends EditRecord
{
    protected static string $resource = CustomerPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
