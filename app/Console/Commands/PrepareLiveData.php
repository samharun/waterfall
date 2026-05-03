<?php

namespace App\Console\Commands;

use Database\Seeders\AccountCategorySeeder;
use Database\Seeders\AppSettingsSeeder;
use Database\Seeders\PaymentAccountSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrepareLiveData extends Command
{
    protected $signature = 'app:prepare-live-data
                            {--force : Skip the interactive prompt}
                            {--confirm= : Required value: PREPARE-LIVE-DATA}
                            {--keep-catalog : Keep zones, products, customer prices, and dealer prices}';

    protected $description = 'Clean demo/transactional data before live entry while preserving back-office users, settings, and default account setup.';

    private const CONFIRMATION = 'PREPARE-LIVE-DATA';

    private const BASE_TABLES = [
        'investor_transactions',
        'maintenance_costs',
        'staff_salary_payments',
        'jar_purchases',
        'business_assets',
        'account_transactions',
        'investors',
        'suppliers',
        'payment_accounts',
        'account_categories',
        'delivery_staff_locations',
        'user_fcm_tokens',
        'customer_notifications',
        'customer_device_tokens',
        'customer_otps',
        'notifications',
        'personal_access_tokens',
        'payments',
        'invoices',
        'deliveries',
        'order_items',
        'orders',
        'stock_transactions',
        'jar_deposits',
        'customer_subscriptions',
        'customer_prices',
        'dealer_prices',
        'dealers',
        'customers',
    ];

    private const CATALOG_TABLES = [
        'user_zone',
        'products',
        'zones',
    ];

    public function handle(): int
    {
        if ($this->option('confirm') !== self::CONFIRMATION) {
            $this->error('Refusing to run without --confirm=' . self::CONFIRMATION);
            return self::FAILURE;
        }

        $tables = $this->tablesToClean();

        $this->warn('This will permanently clean live-entry data from this database.');
        $this->line('Tables to truncate: ' . implode(', ', $tables));
        $this->line('Users with customer, dealer, or delivery_staff roles will be deleted.');
        $this->line('Back-office users, settings, and default account setup will be preserved/reseeded.');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Type yes to continue')) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                DB::table($table)->truncate();
                $this->line("  Truncated {$table}");
            }

            DB::table('users')
                ->whereIn('role', ['customer', 'dealer', 'delivery_staff'])
                ->delete();
            $this->line('  Deleted customer, dealer, and delivery staff users');
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        Artisan::call('db:seed', ['--class' => AppSettingsSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => AccountCategorySeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => PaymentAccountSeeder::class, '--force' => true]);

        $this->newLine();
        $this->info('Live-entry cleanup complete.');
        $this->line('Default account categories, payment accounts, and app settings are ready.');

        return self::SUCCESS;
    }

    private function tablesToClean(): array
    {
        if ($this->option('keep-catalog')) {
            return self::BASE_TABLES;
        }

        return array_merge(self::BASE_TABLES, self::CATALOG_TABLES);
    }
}
