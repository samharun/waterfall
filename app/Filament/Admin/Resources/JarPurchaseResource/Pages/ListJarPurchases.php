<?php

namespace App\Filament\Admin\Resources\JarPurchaseResource\Pages;

use App\Filament\Admin\Resources\JarPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJarPurchases extends ListRecords
{
    protected static string $resource = JarPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
