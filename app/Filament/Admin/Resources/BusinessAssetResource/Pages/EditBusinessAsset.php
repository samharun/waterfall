<?php
namespace App\Filament\Admin\Resources\BusinessAssetResource\Pages;
use App\Filament\Admin\Resources\BusinessAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
class EditBusinessAsset extends EditRecord { protected static string $resource = BusinessAssetResource::class; protected function mutateFormDataBeforeSave(array $data): array { $data['updated_by'] = Auth::id(); return $data; } protected function getHeaderActions(): array { return [Actions\DeleteAction::make(), Actions\RestoreAction::make()]; } }
