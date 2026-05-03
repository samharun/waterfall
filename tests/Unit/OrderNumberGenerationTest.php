<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_number_uses_daily_sequence_format(): void
    {
        $this->assertSame('WF-ORD-260503-0001', Order::generateOrderNo('2026-05-03'));
        $this->assertSame('WF-ORD-260503-0002', Order::generateOrderNo('2026-05-03'));
    }

    public function test_order_number_sequence_resets_for_new_date(): void
    {
        $this->assertSame('WF-ORD-260503-0001', Order::generateOrderNo('2026-05-03'));
        $this->assertSame('WF-ORD-260504-0001', Order::generateOrderNo('2026-05-04'));
    }
}
