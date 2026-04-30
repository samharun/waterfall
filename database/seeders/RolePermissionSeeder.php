<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * RolePermissionSeeder
 *
 * This project uses a simple role string column on users (no Spatie).
 * Permissions are defined in App\Support\RolePermissions.
 * This seeder ensures all existing users have correct roles assigned.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure the default back-office admin exists after a fresh local reset.
        User::updateOrCreate(
            ['email' => 'admin@waterfall.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ],
        );

        // Ensure delivery staff has correct role
        User::where('email', 'staff@waterfall.com')
            ->update(['role' => 'delivery_staff']);

        // Ensure customer user has correct role
        User::where('email', 'customer@waterfall.com')
            ->update(['role' => 'customer']);

        // Ensure dealer user has correct role
        User::where('email', 'dealer@waterfall.com')
            ->update(['role' => 'dealer']);

        $this->command->info('Role assignments verified.');
        $this->command->info('');
        $this->command->info('Permission matrix is defined in App\\Support\\RolePermissions.');
        $this->command->info('');
        $this->command->info('Role summary:');
        $this->command->info('  super_admin    → all permissions');
        $this->command->info('  admin          → all operational + settings');
        $this->command->info('  delivery_manager → delivery + orders + customers view');
        $this->command->info('  billing_officer  → invoices + payments + reports');
        $this->command->info('  stock_manager    → products + stock + jar deposits');
        $this->command->info('  customer         → /customer panel only');
        $this->command->info('  dealer           → /dealer panel only');
        $this->command->info('  delivery_staff   → /delivery panel only');
    }
}
