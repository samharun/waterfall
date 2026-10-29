<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Models\{Delivery, Order, Product, User, Zone};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_backlog_includes_previous_days_and_excludes_completed_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $old = Order::create(['order_date' => today()->subDays(2), 'order_status' => 'confirmed']);
        Delivery::create(['order_id' => $old->id, 'delivery_status' => 'pending']);
        $done = Order::create(['order_date' => today()->subDays(3), 'order_status' => 'delivered']);
        Delivery::create(['order_id' => $done->id, 'delivery_status' => 'delivered', 'delivered_at' => now()]);
        Product::create(['name' => 'Jar', 'sku' => 'J1', 'product_type' => 'jar', 'current_stock' => 4, 'stock_alert_qty' => 5, 'status' => 'active']);
        $page = new Dashboard;
        $page->mount();
        $page->queueFilter = 'overdue';
        $data = $page->getDashboardData();
        $this->assertSame(1, $data['overdue']);
        $this->assertSame(1, $data['unassigned']);
        $this->assertSame([$old->id], $data['queue']->modelKeys());
        $this->assertSame(1, $data['lowStock']);
    }

    public function test_confirmation_and_assignment_are_scoped_and_do_not_duplicate_deliveries(): void
    {
        $manager = User::factory()->create(['role' => 'delivery_manager']);
        $zone = Zone::create(['name' => 'Managed', 'code' => 'M', 'delivery_manager_id' => $manager->id]);
        $staff = User::factory()->create(['role' => 'delivery_staff']);
        $staff->zones()->attach($zone);
        $order = Order::create(['zone_id' => $zone->id, 'order_date' => today()]);
        $this->actingAs($manager);
        $page = new Dashboard;
        $page->mount();
        $page->confirmOrder($order->id);
        $this->assertSame('confirmed', $order->fresh()->order_status);
        $this->assertSame(1, $order->deliveries()->count());
        $page->openAssignment($order->id);
        $page->assignmentStaffId = $staff->id;
        $page->assignStaff();
        $this->assertSame('assigned', $order->fresh()->order_status);
        $this->assertSame($staff->id, $order->deliveries()->first()->delivery_staff_id);
        try {
            $page->confirmOrder($order->id);
            $this->fail('Repeated confirmation accepted');
        } catch (\Illuminate\Validation\ValidationException) {
            $this->assertSame(1, $order->deliveries()->count());
        }
    }

    public function test_manager_cannot_confirm_another_zone_or_choose_it_as_filter(): void
    {
        $manager = User::factory()->create(['role' => 'delivery_manager']);
        $zone = Zone::create(['name' => 'Other', 'code' => 'O']);
        $order = Order::create(['zone_id' => $zone->id, 'order_date' => today()]);
        $this->actingAs($manager);
        $page = new Dashboard;
        $page->mount();
        try {
            $page->confirmOrder($order->id);
            $this->fail('Out-of-zone action accepted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertSame('pending', $order->fresh()->order_status);
        }
        $page->zoneFilter = (string) $zone->id;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $page->getDashboardData();
    }

    public function test_read_only_role_cannot_confirm_orders(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'billing_officer']));
        $page = new Dashboard;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $page->confirmOrder(1);
    }

    public function test_creation_channel_is_recorded_for_new_orders(): void
    {
        \Illuminate\Support\Facades\Route::post('/api/dashboard-source-test', fn () => Order::create(['order_date' => today()])->source_channel);
        $this->post('/api/dashboard-source-test')->assertOk()->assertSee('app_api');
        \Illuminate\Support\Facades\Route::post('/dashboard-web-source-test', fn () => Order::create(['order_date' => today()])->source_channel);
        $this->post('/dashboard-web-source-test')->assertOk()->assertSee('web');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/dashboard-web-source-test')->assertOk()->assertSee('admin');
    }

    public function test_dashboard_renders_with_empty_data_and_updates_filters(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        filament()->setCurrentPanel(filament()->getPanel('admin'));
        Livewire::test(Dashboard::class)
            ->assertSee('Operations Dashboard')
            ->assertSee('No orders yet.')
            ->set('selectedDate', '2026-10-20')
            ->set('chartDays', 14)
            ->assertSee('Deliveries over the last 14 days')
            ->call('refreshDashboard')->assertHasNoErrors();
    }

    public function test_manager_only_sees_own_zone_orders_and_search_respects_date(): void
    {
        $manager = User::factory()->create(['role' => 'delivery_manager']);
        $this->actingAs($manager);
        $own = Zone::create(['name' => 'Own', 'code' => 'OWN', 'delivery_manager_id' => $manager->id]);
        $other = Zone::create(['name' => 'Other', 'code' => 'OTHER']);
        foreach ([['OWN-1', $own->id, '2026-10-20'], ['HIDDEN-1', $other->id, '2026-10-20'], ['FUTURE-1', $own->id, '2026-10-21']] as [$no, $zone, $date]) {
            Order::create(['order_no' => $no, 'zone_id' => $zone, 'order_date' => $date]);
        }
        $page = new Dashboard;
        $page->selectedDate = '2026-10-20';
        $data = $page->getDashboardData();
        $this->assertSame(1, $data['orders']);
        $this->assertSame(['OWN-1'], $data['recent']->pluck('order_no')->all());
        $page->search = 'HIDDEN';
        $this->assertCount(0, $page->getDashboardData()['recent']);
        $this->actingAs(User::factory()->create(['role' => 'stock_manager']));
        $page->search = '';
        $this->assertCount(0, $page->getDashboardData()['recent']);
    }
}
