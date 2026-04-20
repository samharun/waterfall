<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Dealer;
use Illuminate\Console\Command;

class RecalculateAllDues extends Command
{
    protected $signature = 'app:recalculate-dues';

    protected $description = 'Recalculate current_due for all customers and dealers from delivered orders and accepted payments.';

    public function handle(): int
    {
        $customers = Customer::withTrashed(false)->get();
        $bar = $this->output->createProgressBar($customers->count());

        $this->info('Recalculating customer dues...');
        $bar->start();

        foreach ($customers as $customer) {
            $customer->recalculateCurrentDue();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $dealers = Dealer::withTrashed(false)->get();
        $bar2 = $this->output->createProgressBar($dealers->count());

        $this->info('Recalculating dealer dues...');
        $bar2->start();

        foreach ($dealers as $dealer) {
            $dealer->recalculateCurrentDue();
            $bar2->advance();
        }

        $bar2->finish();
        $this->newLine();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
