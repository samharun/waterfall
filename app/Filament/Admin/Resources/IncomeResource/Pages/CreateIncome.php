<?php

namespace App\Filament\Admin\Resources\IncomeResource\Pages;

use App\Filament\Admin\Resources\IncomeResource;
use App\Services\Accounts\AccountTransactionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateIncome extends CreateRecord
{
    protected static string $resource = IncomeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AccountTransactionService::class)->createIncome($data);
    }
}
