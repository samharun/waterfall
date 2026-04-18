<?php

namespace App\Filament\Admin\Resources\DealerPriceResource\Pages;

use App\Filament\Admin\Resources\DealerPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDealerPrice extends EditRecord
{
    protected static string $resource = DealerPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
