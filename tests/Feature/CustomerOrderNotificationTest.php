<?php

namespace Tests\Feature;

use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusPushNotification;
use App\Models\Customer;
use App\Models\CustomerDeviceToken;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerOrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_remove_device_token(): void
    {
        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Push Test Customer',
            'mobile' => '01700000011',
            'email' => 'push-test@example.com',
            'address' => 'Dhaka',
            'zone_id' => null,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        Sanctum::actingAs($customer);

        $storeResponse = $this->postJson('/api/customer/device-token', [
            'device_token' => 'token-abc-123',
            'platform' => 'android',
            'device_name' => 'Pixel 8',
            'app_version' => '1.0.0+1',
        ]);

        $storeResponse
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('customer_device_tokens', [
            'customer_id' => $customer->id,
            'device_token' => 'token-abc-123',
            'platform' => 'android',
            'is_active' => true,
        ]);

        $deleteResponse = $this->deleteJson('/api/customer/device-token', [
            'device_token' => 'token-abc-123',
        ]);

        $deleteResponse
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('customer_device_tokens', [
            'customer_id' => $customer->id,
            'device_token' => 'token-abc-123',
            'is_active' => false,
        ]);
    }

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

    public function test_order_model_dispatches_status_changed_event(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Queued Push Customer',
            'mobile' => '01700000021',
            'email' => 'queued-push@example.com',
            'address' => 'Dhaka',
            'zone_id' => null,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        $order = Order::create([
            'order_type' => 'customer',
            'customer_id' => $customer->id,
            'zone_id' => null,
            'preferred_delivery_slot' => 'now',
            'preferred_delivery_time' => null,
            'order_date' => now()->toDateString(),
            'subtotal' => 120,
            'discount' => 0,
            'delivery_charge' => 0,
            'total_amount' => 120,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'remarks' => null,
        ]);

        $order->update([
            'order_status' => 'confirmed',
        ]);

        Event::assertDispatched(OrderStatusChanged::class, function (OrderStatusChanged $event) use ($order): bool {
            return $event->order->is($order)
                && $event->oldStatus === 'pending'
                && $event->newStatus === 'confirmed';
        });
    }

    public function test_order_status_changed_listener_is_queueable(): void
    {
        $listener = app(SendOrderStatusPushNotification::class);

        $this->assertContains(ShouldQueue::class, class_implements($listener));
        $this->assertSame('notifications', $listener->queue);
        $this->assertTrue($listener->afterCommit);
    }

    public function test_test_push_command_supports_dry_run(): void
    {
        $customer = Customer::create([
            'user_id' => null,
            'name' => 'Console Push Customer',
            'mobile' => '01700000031',
            'email' => 'console-push@example.com',
            'address' => 'Dhaka',
            'zone_id' => null,
            'customer_type' => 'residential',
            'approval_status' => 'approved',
        ]);

        CustomerDeviceToken::create([
            'customer_id' => $customer->id,
            'device_token' => 'console-device-token',
            'platform' => 'android',
            'last_seen_at' => now(),
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_type' => 'customer',
            'customer_id' => $customer->id,
            'zone_id' => null,
            'preferred_delivery_slot' => 'now',
            'preferred_delivery_time' => null,
            'order_date' => now()->toDateString(),
            'subtotal' => 120,
            'discount' => 0,
            'delivery_charge' => 0,
            'total_amount' => 120,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'remarks' => null,
        ]);

        $this->artisan('waterfall:test-order-status-push', [
            '--order-id' => $order->id,
            '--dry-run' => true,
        ])
            ->expectsOutputToContain('Dry run only. Nothing was sent.')
            ->assertSuccessful();
    }
}
