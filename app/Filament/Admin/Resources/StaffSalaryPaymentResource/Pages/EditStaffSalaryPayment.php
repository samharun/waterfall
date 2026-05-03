<?php

namespace App\Filament\Admin\Resources\StaffSalaryPaymentResource\Pages;

use App\Filament\Admin\Resources\StaffSalaryPaymentResource;
use App\Models\StaffSalaryPayment;
use App\Services\Accounts\AccountTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditStaffSalaryPayment extends EditRecord
{
    protected static string $resource = StaffSalaryPaymentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $data = CreateStaffSalaryPayment::calculate($data);
            $data['updated_by'] = Auth::id();
            $record->update($data);
            $this->syncTransaction($record->refresh());

            return $record->refresh();
        });
    }

    protected function syncTransaction(StaffSalaryPayment $record): void
    {
        $service = app(AccountTransactionService::class);

        if ((float) $record->paid_amount <= 0) {
            if ($record->accountTransaction) {
                $service->deleteTransaction($record->accountTransaction);
                $record->forceFill(['account_transaction_id' => null])->saveQuietly();
            }
            return;
        }

        $payload = [
            'transaction_date' => $record->payment_date,
            'account_category_id' => $service->categoryId('Staff Salary', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->paid_amount,
            'paid_to' => $record->staff?->name ?? $record->staff_name,
            'description' => 'Salary payment for ' . $record->salary_month,
            'status' => 'approved',
        ];

        $transaction = $record->accountTransaction
            ? $service->updateTransaction($record->accountTransaction, $payload)
            : $service->createExpense($payload + ['created_by' => $record->created_by]);

        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->after(function (StaffSalaryPayment $record) {
                if ($record->accountTransaction) {
                    app(AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                }
            }),
            Actions\RestoreAction::make(),
        ];
    }
}
