<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class WaterfallSeeder extends Seeder
{
    public function run(): void
    {
        // ── Zones ──────────────────────────────────────────────────
        $zones = [
            ['name' => 'Mohammadpur Line-1', 'code' => 'MDP-L1', 'status' => 'active'],
            ['name' => 'Dhanmondi Line-1',   'code' => 'DHN-L1', 'status' => 'active'],
            ['name' => 'Mirpur Line-1',       'code' => 'MIR-L1', 'status' => 'active'],
        ];

        foreach ($zones as $zone) {
            Zone::firstOrCreate(['code' => $zone['code']], $zone);
        }

        // ── Products ───────────────────────────────────────────────
        $products = [
            [
                'name'            => '20 Liter Water Jar',
                'sku'             => 'WF-JAR-20L',
                'product_type'    => 'jar',
                'default_price'   => 40.00,
                'deposit_amount'  => 200.00,
                'stock_alert_qty' => 20,
                'status'          => 'active',
            ],
            [
                'name'            => 'Small Water Bottle',
                'sku'             => 'WF-BOT-SM',
                'product_type'    => 'bottle',
                'default_price'   => 15.00,
                'deposit_amount'  => 0.00,
                'stock_alert_qty' => 100,
                'status'          => 'active',
            ],
            [
                'name'            => 'Jar Cap',
                'sku'             => 'WF-CAP',
                'product_type'    => 'accessory',
                'default_price'   => 5.00,
                'deposit_amount'  => 0.00,
                'stock_alert_qty' => 200,
                'status'          => 'active',
            ],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }
    }
}
