<?php

namespace App\Filament\Admin\Resources\JarDepositResource\Pages;

use App\Filament\Admin\Resources\JarDepositResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJarDeposits extends ListRecords
{
    protected static string $resource = JarDepositResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
