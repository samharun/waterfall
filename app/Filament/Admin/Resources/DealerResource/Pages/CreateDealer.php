<?php

namespace App\Filament\Admin\Resources\DealerResource\Pages;

use App\Filament\Admin\Resources\DealerResource;
use App\Models\Dealer;
use Filament\Resources\Pages\CreateRecord;

class CreateDealer extends CreateRecord
{
    protected static string $resource = DealerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['dealer_code'] = Dealer::generateDealerCode();
        return $data;
    }
}
