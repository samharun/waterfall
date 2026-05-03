<?php

namespace App\Filament\Admin\Resources\StaffSalaryPaymentResource\Pages;

use App\Filament\Admin\Resources\StaffSalaryPaymentResource;
use App\Models\StaffSalaryPayment;
use App\Services\Accounts\AccountTransactionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateStaffSalaryPayment extends CreateRecord
{
    protected static string $resource = StaffSalaryPaymentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data = self::calculate($data);
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $record = StaffSalaryPayment::create($data);
            $this->syncTransaction($record);

            return $record->refresh();
        });
    }

    public static function calculate(array $data): array
    {
        $net = (float) $data['basic_salary'] + (float) $data['bonus'] - (float) $data['deduction'] - (float) $data['advance_deduction'];
        $paid = (float) $data['paid_amount'];
        $data['net_payable'] = max($net, 0);
        $data['due_amount'] = max($data['net_payable'] - $paid, 0);
        $data['status'] = $paid <= 0 ? 'unpaid' : ($paid >= $data['net_payable'] ? 'paid' : 'partial');

        return $data;
    }

    protected function syncTransaction(StaffSalaryPayment $record): void
    {
        if ((float) $record->paid_amount <= 0) {
            return;
        }

        $service = app(AccountTransactionService::class);
        $transaction = $service->createExpense([
            'transaction_date' => $record->payment_date,
            'account_category_id' => $service->categoryId('Staff Salary', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->paid_amount,
            'paid_to' => $record->staff?->name ?? $record->staff_name,
            'description' => 'Salary payment for ' . $record->salary_month,
            'status' => 'approved',
            'created_by' => $record->created_by,
        ]);

        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
}
