<?php

namespace App\Filament\Admin\Resources\DueCollectionResource\Pages;

use App\Filament\Admin\Resources\DueCollectionResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDueCollection extends CreateRecord
{
    protected static string $resource = DueCollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Always stamp these on creation
        $data['collection_source'] = 'delivery_staff';
        $data['received_by']       = Auth::id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
