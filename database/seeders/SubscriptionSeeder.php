<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::approved()->first();
        $product  = Product::active()->where('product_type', 'jar')->first();
        $admin    = User::where('role', 'admin')->first() ?? User::first();

        if (! $customer || ! $product) {
            $this->command->warn('No approved customer or jar product found. Skipping subscription seed.');
            return;
        }

        // Skip if already has active subscription
        if ($customer->subscriptions()->whereIn('status', ['active', 'paused'])->exists()) {
            $this->command->info('Customer already has a subscription. Skipping.');
            return;
        }

        $sub = CustomerSubscription::create([
            'subscription_no'         => CustomerSubscription::generateSubscriptionNo(),
            'customer_id'             => $customer->id,
            'product_id'              => $product->id,
            'quantity'                => 1,
            'frequency'               => 'daily',
            'preferred_delivery_slot' => 'morning',
            'start_date'              => today()->toDateString(),
            'status'                  => 'active',
            'created_by'              => $admin?->id,
            'updated_by'              => $admin?->id,
        ]);

        $next = $sub->calculateNextDeliveryDate();
        $sub->update(['next_delivery_date' => $next?->toDateString()]);

        $this->command->info("Subscription {$sub->subscription_no} created for {$customer->customer_id}");
    }
}
