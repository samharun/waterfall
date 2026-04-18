<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerPrice;
use App\Models\Dealer;
use App\Models\DealerPrice;
use App\Models\Product;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        $zone = Zone::where('code', 'MDP-L1')->first();
        $jar  = Product::where('sku', 'WF-JAR-20L')->first();

        // ── Sample Dealer ──────────────────────────────────────────
        $dealer = Dealer::firstOrCreate(
            ['mobile' => '01710000001'],
            [
                'dealer_code'     => Dealer::generateDealerCode(),
                'name'            => 'Mohammadpur Dealer',
                'email'           => null,
                'address'         => 'Mohammadpur, Dhaka',
                'zone_id'         => $zone?->id,
                'approval_status' => 'approved',
                'approved_at'     => now(),
            ]
        );

        // ── Dealer Pricing ─────────────────────────────────────────
        if ($jar && $dealer) {
            DealerPrice::firstOrCreate(
                ['dealer_id' => $dealer->id, 'product_id' => $jar->id, 'effective_from' => null],
                [
                    'custom_price' => 32.00,
                    'status'       => 'active',
                ]
            );
        }

        // ── Customer Pricing ───────────────────────────────────────
        $customer = Customer::first();

        if ($jar && $customer) {
            CustomerPrice::firstOrCreate(
                ['customer_id' => $customer->id, 'product_id' => $jar->id, 'effective_from' => null],
                [
                    'custom_price' => 35.00,
                    'status'       => 'active',
                ]
            );
        }
    }
}
