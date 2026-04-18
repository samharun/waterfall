<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Demo/test data seeder.
 * DO NOT run in production.
 * Run manually: php artisan db:seed --class=WaterfallDemoSeeder
 */
class WaterfallDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn('⚠  Running demo seeder — for local/dev only!');

        $this->call([
            WaterfallSeeder::class,       // Zones + Products
            CustomerUserSeeder::class,    // Test customer (01712345678 / password)
            DealerUserSeeder::class,      // Test dealer (01810000001 / password)
            DeliveryStaffSeeder::class,   // Test delivery staff (staff@waterfall.com / staff123)
            InventorySeeder::class,       // Initial stock + jar deposit
            DealerSeeder::class,          // Sample dealer pricing
            BillingSeeder::class,         // Sample invoice + payment
            SubscriptionSeeder::class,    // Sample subscription
        ]);

        $this->command->info('');
        $this->command->info('✅ Demo data seeded.');
        $this->command->info('');
        $this->command->info('Test credentials:');
        $this->command->info('  Admin:          admin@waterfall.com / admin123');
        $this->command->info('  Customer:       mobile 01712345678 / password');
        $this->command->info('  Dealer:         mobile 01810000001 / password');
        $this->command->info('  Delivery Staff: staff@waterfall.com / staff123');
    }
}
