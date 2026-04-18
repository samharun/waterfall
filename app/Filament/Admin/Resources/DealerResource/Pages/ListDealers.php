<?php

namespace App\Filament\Admin\Resources\DealerResource\Pages;

use App\Filament\Admin\Resources\DealerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDealers extends ListRecords
{
    protected static string $resource = DealerResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
