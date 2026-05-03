<?php

namespace App\Services\Accounts;

use App\Models\AccountCategory;
use App\Models\AccountTransaction;
use App\Models\PaymentAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountTransactionService
{
    public function createIncome(array $data): AccountTransaction
    {
        $data['transaction_type'] = 'income';

        return $this->createTransaction($data);
    }

    public function createExpense(array $data, bool $allowOverdraft = false): AccountTransaction
    {
        $data['transaction_type'] = 'expense';

        return $this->createTransaction($data, $allowOverdraft);
    }

    public function createTransaction(array $data, bool $allowOverdraft = false): AccountTransaction
    {
        return DB::transaction(function () use ($data, $allowOverdraft) {
            $data = $this->prepareTransactionData($data);
            $this->guardExpenseBalance($data, null, $allowOverdraft);

            $transaction = AccountTransaction::create($data);

            if ($transaction->status === 'approved') {
                $this->updatePaymentAccountBalance($transaction);
            }

            return $transaction->refresh();
        });
    }

    public function updateTransaction(AccountTransaction $transaction, array $data, bool $allowOverdraft = false): AccountTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $allowOverdraft) {
            $transaction = AccountTransaction::query()->lockForUpdate()->findOrFail($transaction->id);
            $oldApproved = $transaction->status === 'approved';
            $oldData = $transaction->replicate();

            if ($oldApproved) {
                $this->reverseTransaction($transaction);
            }

            $data = $this->prepareTransactionData($data, $transaction);
            $this->guardExpenseBalance($data, $oldApproved ? $oldData : null, $allowOverdraft);

            $transaction->fill($data)->save();

            if ($transaction->status === 'approved') {
                $this->updatePaymentAccountBalance($transaction);
            }

            return $transaction->refresh();
        });
    }

    public function approveTransaction(AccountTransaction $transaction): AccountTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction = AccountTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status !== 'approved') {
                $transaction->forceFill([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ])->save();

                $this->updatePaymentAccountBalance($transaction);
            }

            return $transaction->refresh();
        });
    }

    public function rejectTransaction(AccountTransaction $transaction): AccountTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction = AccountTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'approved') {
                $this->reverseTransaction($transaction);
            }

            $transaction->forceFill([
                'status' => 'rejected',
                'approved_by' => null,
                'approved_at' => null,
            ])->save();

            return $transaction->refresh();
        });
    }

    public function deleteTransaction(AccountTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $transaction = AccountTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if ($transaction->status === 'approved') {
                $this->reverseTransaction($transaction);
            }

            $transaction->delete();
        });
    }

    public function reverseTransaction(AccountTransaction $transaction): void
    {
        $direction = $transaction->transaction_type === 'income' ? -1 : 1;
        $this->updateAccount((int) $transaction->payment_account_id, $direction * (float) $transaction->amount);
    }

    public function updatePaymentAccountBalance(AccountTransaction $transaction): void
    {
        if ($transaction->transaction_type === 'transfer') {
            return;
        }

        $direction = $transaction->transaction_type === 'income' ? 1 : -1;
        $this->updateAccount((int) $transaction->payment_account_id, $direction * (float) $transaction->amount);
    }

    public function generateTransactionNo(null|string|Carbon $date = null): string
    {
        $month = Carbon::parse($date ?? today())->format('Ym');
        $prefix = 'ACC-' . $month . '-';

        $lastNo = AccountTransaction::withTrashed()
            ->where('transaction_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('transaction_no');

        $next = $lastNo ? ((int) substr($lastNo, -6)) + 1 : 1;

        return $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function categoryId(string $name, string $type): ?int
    {
        return AccountCategory::query()
            ->where('name', $name)
            ->where('type', $type)
            ->value('id');
    }

    protected function prepareTransactionData(array $data, ?AccountTransaction $transaction = null): array
    {
        $date = $data['transaction_date'] ?? $transaction?->transaction_date ?? today();
        $status = $data['status'] ?? $transaction?->status ?? 'approved';

        $data['transaction_no'] = $data['transaction_no'] ?? $transaction?->transaction_no ?? $this->generateTransactionNo($date);
        $data['transaction_date'] = Carbon::parse($date)->toDateString();
        $data['transaction_type'] = $data['transaction_type'] ?? $transaction?->transaction_type;
        $data['status'] = $status;
        $data['amount'] = round((float) ($data['amount'] ?? 0), 2);
        $data['created_by'] = $data['created_by'] ?? $transaction?->created_by ?? Auth::id();
        $data['updated_by'] = Auth::id();

        if ($status === 'approved') {
            $data['approved_by'] = $data['approved_by'] ?? $transaction?->approved_by ?? Auth::id();
            $data['approved_at'] = $data['approved_at'] ?? $transaction?->approved_at ?? now();
        } else {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        return $data;
    }

    protected function guardExpenseBalance(array $data, ?AccountTransaction $alreadyReversed = null, bool $allowOverdraft = false): void
    {
        if ($allowOverdraft || ($data['status'] ?? null) !== 'approved' || ($data['transaction_type'] ?? null) !== 'expense') {
            return;
        }

        $account = PaymentAccount::query()->lockForUpdate()->findOrFail($data['payment_account_id']);

        if ((float) $data['amount'] > (float) $account->current_balance) {
            throw ValidationException::withMessages([
                'amount' => 'Expense amount cannot be greater than the selected payment account balance.',
            ]);
        }
    }

    protected function updateAccount(int $paymentAccountId, float $amount): void
    {
        $account = PaymentAccount::query()->lockForUpdate()->findOrFail($paymentAccountId);
        $account->current_balance = round((float) $account->current_balance + $amount, 2);
        $account->save();
    }
}
