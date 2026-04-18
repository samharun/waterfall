<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $zone = Zone::first();

        // Create a user with role = customer
        $user = User::firstOrCreate(
            ['email' => 'customer@waterfall.com'],
            [
                'name'     => 'Test Customer',
                'password' => Hash::make('password'),
                'role'     => 'customer',
            ]
        );

        $user->update(['role' => 'customer']);

        // Create or update linked customer record
        $customer = Customer::firstOrCreate(
            ['mobile' => '01712345678'],
            [
                'user_id'         => $user->id,
                'customer_id'     => Customer::generateCustomerId(),
                'name'            => 'Test Customer',
                'email'           => 'customer@waterfall.com',
                'address'         => 'Mohammadpur, Dhaka',
                'zone_id'         => $zone?->id,
                'customer_type'   => 'residential',
                'approval_status' => 'approved',
                'approved_at'     => now(),
            ]
        );

        // Ensure user_id is linked
        if (! $customer->user_id) {
            $customer->update(['user_id' => $user->id, 'approval_status' => 'approved']);
        }

        $this->command->info("Customer login: mobile=01712345678 / password=password");
        $this->command->info("Customer ID: {$customer->customer_id}");
    }
}
