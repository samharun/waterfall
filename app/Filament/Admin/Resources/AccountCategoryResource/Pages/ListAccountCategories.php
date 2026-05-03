<?php

namespace App\Filament\Admin\Resources\AccountCategoryResource\Pages;

use App\Filament\Admin\Resources\AccountCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountCategories extends ListRecords
{
    protected static string $resource = AccountCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
