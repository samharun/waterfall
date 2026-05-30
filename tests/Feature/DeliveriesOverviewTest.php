<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\DeliveriesOverview;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeliveriesOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_order_delivery_is_visible_before_staff_assignment(): void
    {
        Carbon::setTestNow('2026-05-30 10:00:00');

        $order = Order::create([
            'order_date' => today(),
            'order_status' => 'pending',
        ]);

        $order->markConfirmed();

        $delivery = $order->deliveries()->firstOrFail();

        $this->assertNull($delivery->assigned_at);

        $page = new DeliveriesOverview();
        $page->date_from = '2026-05-01';
        $page->date_until = '2026-05-30';

        $this->assertTrue($page->getDeliveries()->contains($delivery));
    }
}
