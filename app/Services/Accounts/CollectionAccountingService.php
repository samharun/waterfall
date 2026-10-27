<?php

namespace App\Services\Accounts;

use App\Models\AccountCategory;
use App\Models\AccountTransaction;
use App\Models\Payment;
use App\Models\PaymentAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CollectionAccountingService
{
    public function sync(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment = Payment::withTrashed()->lockForUpdate()->findOrFail($payment->id);
            if (! $payment->accounting_enabled) {
                return; // Legacy collections require an explicit reconciliation.
            }
            $transaction = AccountTransaction::withTrashed()->where('payment_id', $payment->id)->lockForUpdate()->first();
            $accepted = ! $payment->trashed() && $payment->collection_status === 'accepted';
            if (! $accepted && ! $transaction) {
                return;
            }
            $service = app(AccountTransactionService::class);
            if ($transaction && ! $transaction->trashed() && $transaction->status === 'approved') {
                $service->reverseTransaction($transaction);
            }
            if (! $accepted) {
                $transaction->forceFill(['status' => 'rejected', 'approved_by' => null, 'approved_at' => null])->save();
                return;
            }

            $account = $this->destination($payment);
            $category = AccountCategory::firstOrCreate(
                ['name' => $payment->payment_type === 'dealer' ? 'Dealer Payment' : 'Customer Payment', 'type' => 'income'],
                ['status' => true],
            );
            $data = [
                'payment_id' => $payment->id,
                'transaction_no' => 'COL-'.$payment->id,
                'transaction_type' => 'income',
                'transaction_date' => $payment->payment_date->toDateString(),
                'payment_account_id' => $account->id,
                'account_category_id' => $category->id,
                'amount' => $payment->amount,
                'received_from' => $payment->customer?->name ?? $payment->dealer?->name,
                'reference_no' => $payment->payment_no,
                'description' => 'Customer/dealer collection. Manage this entry from Payments or Due Collections.',
                'status' => 'approved',
                'approved_by' => auth()->id() ?? $payment->received_by,
                'approved_at' => now(),
                'updated_by' => auth()->id(),
                'deleted_at' => null,
            ];
            if ($transaction) {
                $transaction->forceFill($data)->save();
                $service->updatePaymentAccountBalance($transaction);
            } else {
                $service->createIncome($data + ['created_by' => $payment->received_by]);
            }
        });
    }

    private function destination(Payment $payment): PaymentAccount
    {
        if ($payment->payment_account_id) {
            $account = PaymentAccount::find($payment->payment_account_id);
        } else {
            // A holding account acknowledges receipt without claiming an office
            // cash handover or deposit into a particular bank account.
            $name = 'Collections - '.Payment::methodLabels()[$payment->payment_method];
            if ($payment->payment_method === 'cash' && $payment->collection_source === 'delivery_staff') {
                $staffId = $payment->collected_from_staff_id ?? $payment->received_by;
                $name .= ' - Staff '.($staffId ?? 'unassigned');
            }
            $type = match ($payment->payment_method) {
                'cash' => 'cash',
                'bank' => 'bank',
                'bkash', 'nagad' => 'mobile_banking',
                'card' => 'card',
                default => 'other',
            };
            $account = PaymentAccount::withTrashed()->firstOrCreate(['name' => $name], ['type' => $type, 'status' => true]);
        }
        if (! $account || $account->trashed() || ! $account->status) {
            throw ValidationException::withMessages(['payment_account_id' => 'Select an active payment account.']);
        }

        return $account;
    }
}
