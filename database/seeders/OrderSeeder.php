<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::approved()->first();
        $product  = Product::active()->where('product_type', 'jar')->first();
        $admin    = User::first();

        if (! $customer || ! $product) {
            $this->command->warn('No approved customer or active jar product found. Skipping order seed.');
            return;
        }

        $unitPrice = (float) $product->getPriceForCustomer($customer->id);
        $qty       = 2;
        $lineTotal = $qty * $unitPrice;

        $order = Order::create([
            'order_no'               => Order::generateOrderNo(),
            'order_type'             => 'customer',
            'customer_id'            => $customer->id,
            'zone_id'                => $customer->zone_id,
            'ordered_by'             => $admin?->id,
            'preferred_delivery_slot'=> 'morning',
            'order_date'             => now()->toDateString(),
            'subtotal'               => $lineTotal,
            'discount'               => 0,
            'delivery_charge'        => 0,
            'total_amount'           => $lineTotal,
            'payment_status'         => 'unpaid',
            'order_status'           => 'pending',
        ]);

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => $qty,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
        ]);

        $this->command->info("Sample order {$order->order_no} created.");
    }
}
