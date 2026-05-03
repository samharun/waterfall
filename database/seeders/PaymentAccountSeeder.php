<?php

namespace Database\Seeders;

use App\Models\PaymentAccount;
use Illuminate\Database\Seeder;

class PaymentAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Cash', 'type' => 'cash'],
            ['name' => 'Bank', 'type' => 'bank'],
            ['name' => 'bKash', 'type' => 'mobile_banking'],
            ['name' => 'Nagad', 'type' => 'mobile_banking'],
            ['name' => 'Rocket', 'type' => 'mobile_banking'],
        ];

        foreach ($accounts as $account) {
            PaymentAccount::firstOrCreate(
                ['name' => $account['name']],
                $account + ['opening_balance' => 0, 'current_balance' => 0, 'status' => true],
            );
        }
    }
}
