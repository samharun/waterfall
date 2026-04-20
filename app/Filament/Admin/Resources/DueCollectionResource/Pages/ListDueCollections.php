<?php

namespace App\Filament\Admin\Resources\DueCollectionResource\Pages;

use App\Filament\Admin\Resources\DueCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDueCollections extends ListRecords
{
    protected static string $resource = DueCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Record Collection'),
        ];
    }
}
