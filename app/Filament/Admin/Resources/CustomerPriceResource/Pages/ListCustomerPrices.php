<?php

namespace App\Filament\Admin\Resources\CustomerPriceResource\Pages;

use App\Filament\Admin\Resources\CustomerPriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPrices extends ListRecords
{
    protected static string $resource = CustomerPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
