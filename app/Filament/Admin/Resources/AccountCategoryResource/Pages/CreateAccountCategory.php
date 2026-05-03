<?php

namespace App\Filament\Admin\Resources\AccountCategoryResource\Pages;

use App\Filament\Admin\Resources\AccountCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateAccountCategory extends CreateRecord
{
    protected static string $resource = AccountCategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        return $data;
    }
}
