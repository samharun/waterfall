<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DealerUserSeeder extends Seeder
{
    public function run(): void
    {
        $zone = Zone::first();

        $user = User::firstOrCreate(
            ['email' => 'dealer@waterfall.com'],
            [
                'name'     => 'Test Dealer',
                'password' => Hash::make('password'),
                'role'     => 'dealer',
            ]
        );

        $user->update(['role' => 'dealer']);

        $dealer = Dealer::firstOrCreate(
            ['mobile' => '01810000001'],
            [
                'user_id'         => $user->id,
                'dealer_code'     => Dealer::generateDealerCode(),
                'name'            => 'Test Dealer',
                'email'           => 'dealer@waterfall.com',
                'address'         => 'Dhanmondi, Dhaka',
                'zone_id'         => $zone?->id,
                'approval_status' => 'approved',
                'approved_at'     => now(),
            ]
        );

        if (! $dealer->user_id) {
            $dealer->update(['user_id' => $user->id, 'approval_status' => 'approved']);
        }

        $this->command->info("Dealer login: mobile=01810000001 / password=password");
        $this->command->info("Dealer Code: {$dealer->dealer_code}");
    }
}
