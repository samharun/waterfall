<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeliveryStaffSeeder extends Seeder
{
    public function run(): void
    {
        // ── Ensure admin user has correct role ─────────────────────
        User::where('email', 'admin@waterfall.com')
            ->update(['role' => 'admin']);

        // ── Create a delivery staff user ───────────────────────────
        $staff = User::firstOrCreate(
            ['email' => 'staff@waterfall.com'],
            [
                'name'     => 'Delivery Staff 1',
                'password' => Hash::make('staff123'),
                'role'     => 'delivery_staff',
            ]
        );

        // Ensure role is set if user already existed
        if ($staff->role !== 'delivery_staff') {
            $staff->update(['role' => 'delivery_staff']);
        }

        // ── Assign delivery for first confirmed order ──────────────
        $order = Order::where('order_status', 'confirmed')
            ->whereDoesntHave('deliveries', fn ($q) => $q->whereNotIn('delivery_status', ['cancelled']))
            ->first();

        if ($order) {
            Delivery::create([
                'delivery_no'       => Delivery::generateDeliveryNo(),
                'order_id'          => $order->id,
                'zone_id'           => $order->zone_id,
                'delivery_staff_id' => $staff->id,
                'assigned_by'       => User::where('role', 'admin')->first()?->id,
                'assigned_at'       => now(),
                'delivery_status'   => 'assigned',
            ]);

            $order->update(['order_status' => 'assigned']);

            $this->command->info("Delivery {$order->order_no} assigned to {$staff->name}.");
        } else {
            $this->command->warn('No confirmed order found. Skipping delivery assignment.');
        }
    }
}
