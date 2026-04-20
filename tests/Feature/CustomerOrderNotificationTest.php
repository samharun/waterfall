<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_order_creates_admin_notifications(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $deliveryManager = User::factory()->create([
            'role' => 'delivery_manager',
        ]);

        $zone = Zone::create([
            'name' => 'Dhaka North',
            'code' => 'DN-01',
            'delivery_manager_id' => $deliveryManager->id,
            'status' => 'active',
        ]);

        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Test Customer',
            'mobile' => '01700000001',
            'email' => 'customer@example.com',
            'address' => 'Mirpur, Dhaka',
            'zone_id' => $zone->id,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        $product = Product::create([
            'name' => '19L Jar Water',
            'sku' => 'JAR-19L',
            'product_type' => 'jar',
            'default_price' => 120,
            'deposit_amount' => 0,
            'stock_alert_qty' => 0,
            'current_stock' => 100,
            'status' => 'active',
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/customer/orders', [
            'product_id' => $product->id,
            'quantity' => 2,
            'delivery_slot' => 'now',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(1, $deliveryManager->notifications()->count());

        $adminNotification = $admin->notifications()->first();

        $this->assertSame('filament', $adminNotification->data['format']);
        $this->assertStringContainsString('New order WF-ORD-', $adminNotification->data['title']);
        $this->assertStringContainsString('Test Customer placed a customer order', $adminNotification->data['body']);
    }
}
