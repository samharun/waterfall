<?php
namespace App\Filament\Admin\Resources\BusinessAssetResource\Pages;
use App\Filament\Admin\Resources\BusinessAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListBusinessAssets extends ListRecords { protected static string $resource = BusinessAssetResource::class; protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; } }
