<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\DueCollectionResource;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueCollectionOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_collectors_include_authorized_office_roles_without_delivery_staff(): void
    {
        foreach (['super_admin', 'admin', 'delivery_manager', 'billing_officer'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertArrayHasKey($user->id, DueCollectionResource::collectorOptions());
        }
        foreach (['customer', 'dealer', 'stock_manager'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertArrayNotHasKey($user->id, DueCollectionResource::collectorOptions());
        }
    }

    public function test_active_deliveries_are_available_and_staff_assignment_is_respected(): void
    {
        $customer = Customer::create(['name' => 'Collection customer', 'mobile' => '01712345678']);
        $staff = User::factory()->create(['role' => 'delivery_staff']);
        $otherStaff = User::factory()->create(['role' => 'delivery_staff']);
        $manager = User::factory()->create(['role' => 'delivery_manager']);
        $order = Order::create(['order_type' => 'customer', 'customer_id' => $customer->id, 'order_date' => today(), 'order_status' => 'confirmed']);
        $assigned = Delivery::create(['order_id' => $order->id, 'delivery_staff_id' => $staff->id, 'delivery_status' => 'assigned']);
        $other = Delivery::create(['order_id' => $order->id, 'delivery_staff_id' => $otherStaff->id, 'delivery_status' => 'in_progress']);
        $cancelled = Delivery::create(['order_id' => $order->id, 'delivery_staff_id' => $staff->id, 'delivery_status' => 'cancelled']);
        $this->assertArrayHasKey($assigned->id, DueCollectionResource::deliveryOptions($staff->id));
        $this->assertArrayNotHasKey($other->id, DueCollectionResource::deliveryOptions($staff->id));
        $this->assertArrayNotHasKey($cancelled->id, DueCollectionResource::deliveryOptions(null));
        $this->assertArrayHasKey($assigned->id, DueCollectionResource::deliveryOptions($manager->id));
        $this->assertArrayHasKey($other->id, DueCollectionResource::deliveryOptions($manager->id));
    }
}
