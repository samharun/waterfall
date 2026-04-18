<?php

namespace App\Filament\Admin\Resources\DealerPriceResource\Pages;

use App\Filament\Admin\Resources\DealerPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDealerPrices extends ListRecords
{
    protected static string $resource = DealerPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
