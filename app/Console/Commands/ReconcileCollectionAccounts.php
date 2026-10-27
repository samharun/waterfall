<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class ReconcileCollectionAccounts extends Command
{
    protected $signature = 'accounts:reconcile-collections {--payment-id=* : Reviewed payment IDs} {--apply : Enable and post the explicitly selected payments}';

    protected $description = 'Preview legacy collections missing Accounts postings; apply only explicitly reviewed payment IDs';

    public function handle(): int
    {
        $ids = $this->option('payment-id');
        if ($this->option('apply') && ! $ids) {
            $this->error('Specify reviewed --payment-id values. Check for existing manual income before posting.');
            return self::FAILURE;
        }
        $query = Payment::query()->where('accounting_enabled', false)
            ->when($ids, fn ($q) => $q->whereIn('id', $ids));
        $this->warn('Legacy rows may already be represented by manual income or opening balances. Do not post them twice.');
        $count = 0;
        foreach ($query->orderBy('id')->lazyById(100) as $payment) {
            $this->line("{$payment->id} | {$payment->payment_no} | {$payment->collection_status} | {$payment->payment_method} | {$payment->amount}");
            if ($this->option('apply')) {
                $payment->forceFill(['accounting_enabled' => true])->save();
            }
            $count++;
        }
        $this->info($count.' collection(s) '.($this->option('apply') ? 'enabled and reconciled.' : 'previewed. No balances changed.'));
        return self::SUCCESS;
    }
}
