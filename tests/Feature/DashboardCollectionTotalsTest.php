<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\AccountsDashboard;
use App\Filament\Admin\Widgets\TodayCollectionSummaryWidget;
use App\Models\Customer;
use App\Models\Payment;
use App\Services\Accounts\CollectionSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardCollectionTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_counts_accepted_collections_from_all_sources_and_preserves_method_labels(): void
    {
        $this->travelTo(Carbon::parse('2026-10-28 12:00:00'));
        $customer = Customer::create(['name' => 'Collection totals', 'mobile' => '01712345678']);
        $make = function (array $data) use ($customer): Payment {
            $payment = new Payment($data + [
                'payment_type' => 'customer', 'customer_id' => $customer->id,
                'payment_date' => today(), 'amount' => 100,
            ]);
            // Include historical receipts even if they have not been posted to Accounts.
            $payment->forceFill(['accounting_enabled' => false])->save();
            return $payment;
        };
        $make(['amount' => 100, 'collection_source' => 'admin']);
        $make(['amount' => 250, 'collection_source' => 'delivery_staff', 'payment_method' => 'bkash']);
        $make(['amount' => 50, 'payment_date' => '2026-10-01']);
        $make(['amount' => 75, 'payment_date' => '2026-09-30']);
        $make(['amount' => 80, 'collection_status' => 'pending_review']);
        $make(['amount' => 90, 'collection_status' => 'rejected']);
        $make(['amount' => 500])->delete();

        $this->assertSame(['today' => 350.0, 'month' => 400.0, 'all_time' => 475.0], app(CollectionSummaryService::class)->totals());
        $summary = (new TodayCollectionSummaryWidget)->getSummary();
        $this->assertSame(350.0, $summary['total_collected']);
        $this->assertSame(2, $summary['total_count']);
        $this->assertSame(80.0, $summary['pending_review']);
        $this->assertSame(250.0, $summary['by_method']->keyBy('payment_method')['bkash']['amount']);
        $stats = (new AccountsDashboard)->stats();
        $this->assertSame(350.0, $stats['Collected Today']);
        $this->assertSame(400.0, $stats['Collected This Month']);
        $this->assertSame(475.0, $stats['Total Collected']);
    }
}
