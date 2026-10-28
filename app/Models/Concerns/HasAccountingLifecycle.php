<?php

namespace App\Models\Concerns;

use App\Services\Accounts\AccountTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait HasAccountingLifecycle
{
    public function delete()
    {
        return DB::transaction(function () {
            if (! $this->exists) {
                return null;
            }
            $fresh = static::withTrashed()->lockForUpdate()->findOrFail($this->id);
            if ($fresh->trashed()) {
                return true;
            }
            $this->setRawAttributes($fresh->getAttributes(), true);
            $transaction = $this->accountTransaction()->first();
            if ($transaction) {
                app(AccountTransactionService::class)->deleteTransaction($transaction);
            }
            $result = parent::delete();
            $this->refreshAccountingParty();

            return $result;
        });
    }

    protected function restoreWithAccounting()
    {
        return DB::transaction(function () {
            $fresh = static::withTrashed()->lockForUpdate()->findOrFail($this->id);
            $this->setRawAttributes($fresh->getAttributes(), true);
            if (! $this->trashed()) {
                return true;
            }
            $transaction = $this->accountTransaction()->withTrashed()->first();
            if ($transaction) {
                app(AccountTransactionService::class)->restoreTransaction($transaction);
            }
            $result = $this->restoreWithoutAccounting();
            $this->refreshAccountingParty();

            return $result;
        });
    }

    public static function bootHasAccountingLifecycle(): void
    {
        static::forceDeleting(function () {
            throw ValidationException::withMessages(['record' => 'Financial records must be retained for audit. Use Delete instead.']);
        });
    }

    private function refreshAccountingParty(): void
    {
        if ($this instanceof \App\Models\JarPurchase) {
            $this->supplier()->first()?->recalculateDue();
        } elseif ($this instanceof \App\Models\InvestorTransaction) {
            $this->investor()->first()?->recalculateBalance();
        }
    }
}
