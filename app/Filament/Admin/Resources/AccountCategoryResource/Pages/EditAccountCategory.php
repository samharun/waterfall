<?php

namespace App\Filament\Admin\Resources\AccountCategoryResource\Pages;

use App\Filament\Admin\Resources\AccountCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAccountCategory extends EditRecord
{
    protected static string $resource = AccountCategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make(), Actions\RestoreAction::make()];
    }
}
