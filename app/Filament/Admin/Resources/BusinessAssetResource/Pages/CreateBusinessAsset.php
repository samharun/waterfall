<?php
namespace App\Filament\Admin\Resources\BusinessAssetResource\Pages;
use App\Filament\Admin\Resources\BusinessAssetResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
class CreateBusinessAsset extends CreateRecord { protected static string $resource = BusinessAssetResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $data['created_by'] = Auth::id(); $data['updated_by'] = Auth::id(); return $data; } }
