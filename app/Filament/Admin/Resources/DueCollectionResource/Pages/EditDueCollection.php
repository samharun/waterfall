<?php

namespace App\Filament\Admin\Resources\DueCollectionResource\Pages;

use App\Filament\Admin\Resources\DueCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDueCollection extends EditRecord
{
    protected static string $resource = DueCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
