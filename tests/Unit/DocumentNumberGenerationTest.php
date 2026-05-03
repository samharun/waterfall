<?php

namespace Tests\Unit;

use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNumberGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_number_uses_daily_sequence_format(): void
    {
        $this->assertSame('WF-PAY-260503-0001', Payment::generatePaymentNo('2026-05-03'));
        $this->assertSame('WF-PAY-260503-0002', Payment::generatePaymentNo('2026-05-03'));
        $this->assertSame('WF-PAY-260504-0001', Payment::generatePaymentNo('2026-05-04'));
    }

    public function test_invoice_number_uses_daily_sequence_format(): void
    {
        $this->assertSame('WF-INV-260503-0001', Invoice::generateInvoiceNo('2026-05-03'));
        $this->assertSame('WF-INV-260503-0002', Invoice::generateInvoiceNo('2026-05-03'));
        $this->assertSame('WF-INV-260504-0001', Invoice::generateInvoiceNo('2026-05-04'));
    }

    public function test_delivery_number_uses_daily_sequence_format(): void
    {
        $this->assertSame('WF-DEL-260503-0001', Delivery::generateDeliveryNo('2026-05-03'));
        $this->assertSame('WF-DEL-260503-0002', Delivery::generateDeliveryNo('2026-05-03'));
        $this->assertSame('WF-DEL-260504-0001', Delivery::generateDeliveryNo('2026-05-04'));
    }
}
