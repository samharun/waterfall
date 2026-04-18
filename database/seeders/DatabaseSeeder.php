<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Production-safe seeders only.
     * Demo/test data must be run manually via WaterfallDemoSeeder.
     */
    public function run(): void
    {
        // ── Always safe for production ─────────────────────────────
        $this->call([
            RolePermissionSeeder::class,  // Ensures correct role assignments
            AppSettingsSeeder::class,     // Default SMS/OTP/company/branding settings
        ]);

        $this->command->info('');
        $this->command->info('✅ Production seeders complete.');
        $this->command->info('');
        $this->command->info('To seed demo/test data, run:');
        $this->command->info('  php artisan db:seed --class=WaterfallDemoSeeder');
    }
}
