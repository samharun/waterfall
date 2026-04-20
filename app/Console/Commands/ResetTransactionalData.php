<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetTransactionalData extends Command
{
    protected $signature = 'app:reset-transactional-data
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Truncate all transactional data (orders, deliveries, payments, invoices, stock transactions, jar deposits) while keeping customers, users, products, zones, and settings. NON-PRODUCTION ONLY.';

    // Tables to wipe, in dependency order (children first)
    private const TABLES = [
        'payments',
        'invoices',
        'deliveries',
        'order_items',
        'orders',
        'stock_transactions',
        'jar_deposits',
        'customer_subscriptions',
    ];

    // Columns to reset on other tables after wipe
    private const CUSTOMER_RESET = [
        'current_due'     => 0,
        'jar_deposit_qty' => 0,
    ];

    private const DEALER_RESET = [
        'current_due' => 0,
    ];

    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('This command is disabled in production.');
            return self::FAILURE;
        }

        $this->warn('⚠️  This will permanently delete all transactional data:');
        $this->line('   ' . implode(', ', self::TABLES));
        $this->line('   Customer/dealer due balances and jar deposits will be reset to 0.');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Are you sure you want to continue?')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach (self::TABLES as $table) {
            DB::table($table)->truncate();
            $this->line("  ✓ Truncated <comment>{$table}</comment>");
        }

        // Reset derived columns on customers and dealers
        DB::table('customers')->update(self::CUSTOMER_RESET);
        $this->line('  ✓ Reset <comment>customers</comment> due & jar deposit to 0');

        DB::table('dealers')->update(self::DEALER_RESET);
        $this->line('  ✓ Reset <comment>dealers</comment> due to 0');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('✅  Transactional data cleared. Customers, users, products, zones, and settings are untouched.');

        return self::SUCCESS;
    }
}
