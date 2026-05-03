<?php

namespace App\Filament\Admin\Resources\IncomeResource\Pages;

use App\Filament\Admin\Resources\IncomeResource;
use App\Models\AccountTransaction;
use App\Services\Accounts\AccountTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditIncome extends EditRecord
{
    protected static string $resource = IncomeResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(AccountTransactionService::class)->updateTransaction($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->using(fn (AccountTransaction $record) => app(AccountTransactionService::class)->deleteTransaction($record)),
            Actions\RestoreAction::make(),
        ];
    }
}
