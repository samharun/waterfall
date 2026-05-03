<?php
namespace App\Filament\Admin\Resources\MaintenanceCostResource\Pages;
use App\Filament\Admin\Resources\MaintenanceCostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListMaintenanceCosts extends ListRecords { protected static string $resource = MaintenanceCostResource::class; protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; } }
