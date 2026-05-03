<?php
namespace App\Filament\Admin\Resources\InvestorResource\Pages;
use App\Filament\Admin\Resources\InvestorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
class CreateInvestor extends CreateRecord { protected static string $resource = InvestorResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $data['created_by'] = Auth::id(); $data['updated_by'] = Auth::id(); return $data; } }
