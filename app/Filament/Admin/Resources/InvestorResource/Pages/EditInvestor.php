<?php
namespace App\Filament\Admin\Resources\InvestorResource\Pages;
use App\Filament\Admin\Resources\InvestorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
class EditInvestor extends EditRecord { protected static string $resource = InvestorResource::class; protected function afterSave(): void { $this->record->recalculateBalance(); } protected function mutateFormDataBeforeSave(array $data): array { $data['updated_by'] = Auth::id(); return $data; } protected function getHeaderActions(): array { return [Actions\DeleteAction::make(), Actions\RestoreAction::make()]; } }
