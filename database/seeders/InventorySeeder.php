<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\JarDeposit;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $jar    = Product::where('sku', 'WF-JAR-20L')->first();
        $admin  = User::where('role', 'admin')->first() ?? User::first();

        if (! $jar) {
            $this->command->warn('WF-JAR-20L product not found. Skipping inventory seed.');
            return;
        }

        // ── Initial stock_in ──────────────────────────────────────
        if ($jar->current_stock === 0) {
            StockTransaction::create([
                'transaction_no'   => StockTransaction::generateTransactionNo(),
                'product_id'       => $jar->id,
                'transaction_type' => 'stock_in',
                'quantity'         => 100,
                'unit_cost'        => 30.00,
                'transaction_date' => now()->toDateString(),
                'remarks'          => 'Initial stock',
                'created_by'       => $admin?->id,
            ]);
            // Model boot auto-recalculates current_stock
        }

        $jar->refresh();
        $this->command->info("Product {$jar->sku} current_stock: {$jar->current_stock}");

        // ── Jar deposit for first approved customer ────────────────
        $customer = Customer::approved()->first();

        if ($customer && $customer->jar_deposit_qty === 0) {
            JarDeposit::create([
                'deposit_no'       => JarDeposit::generateDepositNo(),
                'party_type'       => 'customer',
                'customer_id'      => $customer->id,
                'product_id'       => $jar->id,
                'transaction_type' => 'deposit_received',
                'quantity'         => 2,
                'deposit_amount'   => 400.00,
                'transaction_date' => now()->toDateString(),
                'remarks'          => 'Initial jar deposit',
                'created_by'       => $admin?->id,
            ]);
            // Model boot auto-recalculates jar_deposit_qty
        }

        $customer?->refresh();
        if ($customer) {
            $this->command->info("Customer {$customer->customer_id} jar_deposit_qty: {$customer->jar_deposit_qty}");
        } else {
            $this->command->warn('No approved customer found. Skipping jar deposit seed.');
        }
    }
}
