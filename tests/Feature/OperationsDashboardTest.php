<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\Dashboard;
use App\Models\{Order, User, Zone};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

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
