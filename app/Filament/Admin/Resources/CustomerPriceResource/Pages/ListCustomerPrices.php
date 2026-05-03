<?php

namespace App\Filament\Admin\Resources\CustomerPriceResource\Pages;

use App\Filament\Admin\Resources\CustomerPriceResource;
use App\Filament\Admin\Resources\CustomerPriceResource\Widgets\CustomerPriceStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerPrices extends ListRecords
{
    protected static string $resource = CustomerPriceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerPriceStatsOverview::class,
        ];
    }
}
