<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryMobileOperationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_staff_can_search_customers_and_record_collections(): void
    {
        $staff = User::factory()->create(['role' => 'delivery_staff']);
        [$customer] = $this->createCustomerAndProduct();

        Sanctum::actingAs($staff);

        $this->getJson('/api/delivery/search-customers?query='.urlencode($customer->mobile))
            ->assertOk()
            ->assertJsonPath('data.0.customer_id', $customer->customer_id)
            ->assertJsonPath('data.0.customer_name', $customer->name);

        $this->postJson('/api/delivery/collections', [
            'customer_id' => $customer->customer_id,
            'amount' => 125.50,
            'remarks' => 'Mobile collection',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('payments', [
            'customer_id' => $customer->id,
            'received_by' => $staff->id,
            'collection_source' => 'delivery_staff',
            'collection_status' => 'accepted',
        ]);

        $this->postJson('/api/delivery/jar-collections', [
            'customer_id' => $customer->customer_id,
            'quantity' => 2,
            'remarks' => 'Empty jars returned',
        ])->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('jar_deposits', [
            'customer_id' => $customer->id,
            'transaction_type' => 'jar_returned',
            'quantity' => 2,
            'created_by' => $staff->id,
        ]);
    }

    public function test_manager_can_search_and_create_a_pending_customer_order_in_managed_zone(): void
    {
        $manager = User::factory()->create(['role' => 'delivery_manager']);
        [$customer, $product] = $this->createCustomerAndProduct($manager);

        Sanctum::actingAs($manager);

        $this->getJson('/api/delivery-manager/search-customers?query='.urlencode($customer->name))
            ->assertOk()
            ->assertJsonPath('data.0.customer_id', $customer->customer_id);

        $response = $this->postJson('/api/delivery-manager/create-customer-order', [
            'customer_id' => $customer->customer_id,
            'customer_name' => 'Untrusted client value',
            'mobile' => '00000000000',
            'zone_name' => 'Untrusted zone',
            'jar_quantity' => 3,
            'delivery_slot' => 'morning',
            'remarks' => 'Phone order',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order.customer_id', $customer->customer_id)
            ->assertJsonPath('data.order.customer_name', $customer->name)
            ->assertJsonPath('data.order.jar_quantity', 3)
            ->assertJsonPath('data.order.total_amount', 360);

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame($manager->id, $order->ordered_by);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('pending', $order->order_status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    /**
     * @return array{Customer, Product}
     */
    private function createCustomerAndProduct(?User $manager = null): array
    {
        $manager ??= User::factory()->create(['role' => 'delivery_manager']);
        $zone = Zone::create([
            'name' => 'Mobile Operations Zone',
            'code' => 'MO-01',
            'delivery_manager_id' => $manager->id,
            'status' => 'active',
        ]);
        $customer = Customer::create([
            'name' => 'Mobile Test Customer',
            'mobile' => '01714116624',
            'address' => 'Dhaka',
            'zone_id' => $zone->id,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);
        $product = Product::create([
            'name' => '20L Water Jar',
            'sku' => 'MOBILE-JAR-20L',
            'product_type' => 'jar',
            'default_price' => 120,
            'deposit_amount' => 0,
            'stock_alert_qty' => 0,
            'current_stock' => 100,
            'status' => 'active',
        ]);

        return [$customer, $product];
    }
}
