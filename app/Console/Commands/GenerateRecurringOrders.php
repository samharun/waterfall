<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRecurringOrders extends Command
{
    protected $signature = 'waterfall:generate-recurring-orders
                            {--date= : Target date (Y-m-d). Defaults to today.}
                            {--dry-run : Preview without creating orders.}';

    protected $description = 'Generate pending-review orders from active subscriptions due for delivery.';

    public function handle(): int
    {
        $targetDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : today();

        $dryRun = $this->option('dry-run');

        $this->info("Generating recurring orders for: {$targetDate->toDateString()}" . ($dryRun ? ' [DRY RUN]' : ''));

        $subscriptions = CustomerSubscription::with(['customer', 'product'])
            ->active()
            ->whereNotNull('next_delivery_date')
            ->whereDate('next_delivery_date', '<=', $targetDate)
            ->get();

        $this->info("Found {$subscriptions->count()} subscription(s) due.");

        $created = 0;
        $skipped = 0;

        foreach ($subscriptions as $sub) {
            $customer = $sub->customer;
            $product  = $sub->product;

            if (! $customer || ! $product) {
                $this->warn("  Skipping sub {$sub->subscription_no}: missing customer or product.");
                $skipped++;
                continue;
            }

            if ($customer->approval_status !== 'approved') {
                $this->warn("  Skipping sub {$sub->subscription_no}: customer not approved.");
                $skipped++;
                continue;
            }

            // Prevent duplicate order for same subscription + date
            $exists = Order::where('subscription_id', $sub->id)
                ->whereDate('order_date', $targetDate)
                ->exists();

            if ($exists) {
                $this->line("  Skipping sub {$sub->subscription_no}: order already exists for {$targetDate->toDateString()}.");
                $skipped++;
                continue;
            }

            $unitPrice = (float) $product->getPriceForCustomer($customer->id, $targetDate->toDateString());
            $lineTotal = $sub->quantity * $unitPrice;

            if ($dryRun) {
                $this->line("  [DRY] Would create order for {$customer->customer_id} — {$product->name} x{$sub->quantity} @ ৳{$unitPrice}");
                $created++;
                continue;
            }

            try {
                DB::transaction(function () use ($sub, $customer, $product, $unitPrice, $lineTotal, $targetDate) {
                    $order = Order::create([
                        'order_no'               => Order::generateOrderNo(),
                        'order_type'             => 'customer',
                        'customer_id'            => $customer->id,
                        'subscription_id'        => $sub->id,
                        'zone_id'                => $customer->zone_id,
                        'ordered_by'             => null,
                        'preferred_delivery_slot'=> $sub->preferred_delivery_slot,
                        'preferred_delivery_time'=> $sub->preferred_delivery_time,
                        'order_date'             => $targetDate->toDateString(),
                        'subtotal'               => $lineTotal,
                        'discount'               => 0,
                        'delivery_charge'        => 0,
                        'total_amount'           => $lineTotal,
                        'payment_status'         => 'unpaid',
                        'order_status'           => 'pending',
                        'remarks'                => "Auto-generated from subscription {$sub->subscription_no}",
                    ]);

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $sub->quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    // Advance next delivery date
                    $next = $sub->calculateNextDeliveryDate($targetDate->copy()->addDay());
                    $sub->update(['next_delivery_date' => $next?->toDateString()]);
                });

                $this->info("  ✓ Created order for {$customer->customer_id} — {$product->name}");
                $created++;

            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for sub {$sub->subscription_no}: " . $e->getMessage());
                Log::error("Recurring order generation failed for {$sub->subscription_no}: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info("Done. Created: {$created} | Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
