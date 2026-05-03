<?php

namespace App\Filament\Admin\Resources\ZoneResource\Pages;

use App\Filament\Admin\Resources\ZoneResource;
use App\Filament\Admin\Resources\ZoneResource\Widgets\ZoneStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZones extends ListRecords
{
    protected static string $resource = ZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ZoneStatsOverview::class,
        ];
    }
}
