<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class OrderRelationshipsTest extends TestCase
{
    public function test_order_has_many_payments(): void
    {
        $relationship = (new Order)->payments();

        $this->assertInstanceOf(HasMany::class, $relationship);
        $this->assertInstanceOf(Payment::class, $relationship->getRelated());
        $this->assertSame('order_id', $relationship->getForeignKeyName());
    }
}
