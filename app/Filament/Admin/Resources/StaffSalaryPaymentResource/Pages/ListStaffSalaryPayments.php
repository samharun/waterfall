<?php

namespace App\Filament\Admin\Resources\StaffSalaryPaymentResource\Pages;

use App\Filament\Admin\Resources\StaffSalaryPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffSalaryPayments extends ListRecords
{
    protected static string $resource = StaffSalaryPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
