<?php

namespace Tests\Feature;

use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Services\Accounts\AccountTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CollectionAccountingTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        return Customer::create(['name' => 'Accounts test', 'mobile' => '017'.random_int(10000000, 99999999), 'approval_status' => 'approved']);
    }

    private function invoice(Customer $customer): Invoice
    {
        return Invoice::create(['invoice_type' => 'customer', 'customer_id' => $customer->id, 'invoice_date' => today(), 'billing_month' => 10, 'billing_year' => 2026, 'subtotal' => 1000, 'total_amount' => 1000, 'due_amount' => 1000, 'invoice_status' => 'issued']);
    }

    private function payment(Customer $customer, array $data = []): Payment
    {
        return Payment::create($data + ['payment_type' => 'customer', 'customer_id' => $customer->id, 'payment_date' => today(), 'amount' => 600, 'payment_method' => 'cash']);
    }

    public function test_collection_lifecycle_updates_one_posting_and_invoice(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $payment = $this->payment($customer, ['invoice_id' => $invoice->id]);
        $account = $payment->accountTransaction->paymentAccount;
        $this->assertSame('600.00', $account->refresh()->current_balance);
        $this->assertSame('400.00', $invoice->refresh()->due_amount);
        $payment->update(['amount' => 400]);
        $payment->save();
        $this->assertSame('400.00', $account->refresh()->current_balance);
        $this->assertSame(1, AccountTransaction::where('payment_id', $payment->id)->count());
        $payment->update(['collection_status' => 'rejected']);
        $this->assertSame('0.00', $account->refresh()->current_balance);
        $this->assertSame('0.00', $invoice->refresh()->paid_amount);
        $payment->update(['collection_status' => 'accepted']);
        $payment->delete();
        $this->assertSame('0.00', $account->refresh()->current_balance);
        $payment->restore();
        $this->assertSame('400.00', $account->refresh()->current_balance);
        $this->assertSame('400.00', $invoice->refresh()->paid_amount);
        $this->assertSame(1, AccountTransaction::where('payment_id', $payment->id)->count());
    }

    public function test_pending_payment_is_not_income_or_invoice_payment_until_accepted(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $payment = $this->payment($customer, ['invoice_id' => $invoice->id, 'collection_status' => 'pending_review']);
        $this->assertSame(0, AccountTransaction::count());
        $this->assertSame('0.00', $invoice->refresh()->paid_amount);
        $payment->update(['collection_status' => 'accepted']);
        $this->assertSame('600.00', $invoice->refresh()->paid_amount);
        $this->assertSame(1, AccountTransaction::count());
    }

    public function test_moving_payment_resynchronizes_old_and_new_customer_and_invoice(): void
    {
        $first = $this->customer();
        $second = $this->customer();
        foreach ([$first, $second] as $customer) {
            Order::create(['order_type' => 'customer', 'customer_id' => $customer->id, 'order_date' => today(), 'subtotal' => 1000, 'total_amount' => 1000, 'order_status' => 'delivered']);
        }
        $oldInvoice = $this->invoice($first);
        $newInvoice = $this->invoice($second);
        $payment = $this->payment($first, ['invoice_id' => $oldInvoice->id]);
        $payment->update(['customer_id' => $second->id, 'invoice_id' => $newInvoice->id]);
        $this->assertSame('1000.00', $first->refresh()->current_due);
        $this->assertSame('400.00', $second->refresh()->current_due);
        $this->assertSame('0.00', $oldInvoice->refresh()->paid_amount);
        $this->assertSame('600.00', $newInvoice->refresh()->paid_amount);
    }

    public function test_reassigning_destination_moves_balance_without_duplicate_income(): void
    {
        $payment = $this->payment($this->customer());
        $holding = $payment->accountTransaction->paymentAccount;
        $bank = PaymentAccount::create(['name' => 'Actual bank', 'type' => 'bank', 'status' => true]);
        $payment->update(['payment_account_id' => $bank->id]);
        $this->assertSame('0.00', $holding->refresh()->current_balance);
        $this->assertSame('600.00', $bank->refresh()->current_balance);
        $this->assertSame(1, AccountTransaction::approved()->count());
    }

    public function test_failure_rolls_back_payment_invoice_and_account(): void
    {
        $customer = $this->customer();
        $invoice = $this->invoice($customer);
        $account = PaymentAccount::create(['name' => 'Disabled', 'type' => 'cash', 'status' => false]);
        try {
            $this->payment($customer, ['invoice_id' => $invoice->id, 'payment_account_id' => $account->id]);
            $this->fail('An inactive account must fail.');
        } catch (ValidationException) {
            $this->assertSame(0, Payment::count());
            $this->assertSame(0, AccountTransaction::count());
            $this->assertSame('0.00', $invoice->refresh()->paid_amount);
            $this->assertSame('0.00', $account->refresh()->current_balance);
        }
    }

    public function test_invoice_ownership_and_overpayment_are_validated_for_all_callers(): void
    {
        $customer = $this->customer();
        $other = $this->customer();
        $invoice = $this->invoice($customer);
        foreach ([['customer' => $other, 'amount' => 100], ['customer' => $customer, 'amount' => 1001]] as $case) {
            try {
                $this->payment($case['customer'], ['invoice_id' => $invoice->id, 'amount' => $case['amount']]);
                $this->fail('Invalid invoice payment was accepted.');
            } catch (ValidationException) {
                $this->assertSame(0, Payment::count());
            }
        }
    }

    public function test_legacy_collections_require_explicit_idempotent_reconciliation(): void
    {
        $customer = $this->customer();
        $payment = new Payment(['payment_type' => 'customer', 'customer_id' => $customer->id, 'payment_date' => today(), 'amount' => 600]);
        $payment->forceFill(['accounting_enabled' => false])->save();
        $payment->update(['remarks' => 'Legacy receipt']);
        $this->assertSame(0, AccountTransaction::count());
        $this->artisan('accounts:reconcile-collections')->assertSuccessful();
        $this->assertSame(0, AccountTransaction::count());
        $this->artisan('accounts:reconcile-collections', ['--apply' => true])->assertFailed();
        $this->artisan('accounts:reconcile-collections', ['--payment-id' => [$payment->id], '--apply' => true])->assertSuccessful();
        $this->artisan('accounts:reconcile-collections', ['--payment-id' => [$payment->id], '--apply' => true])->assertSuccessful();
        $this->assertSame(1, AccountTransaction::count());
        $this->assertSame('600.00', $payment->accountTransaction->paymentAccount->current_balance);
    }

    public function test_collection_income_cannot_be_edited_independently(): void
    {
        $payment = $this->payment($this->customer());
        $this->expectException(ValidationException::class);
        app(AccountTransactionService::class)->deleteTransaction($payment->accountTransaction);
    }

    public function test_stale_updates_preserve_latest_amount_and_posting(): void
    {
        $payment = $this->payment($this->customer());
        $stale = Payment::findOrFail($payment->id);
        $payment->update(['amount' => 300]);
        $stale->update(['remarks' => 'Receipt checked']);
        $this->assertSame('300.00', $stale->refresh()->amount);
        $this->assertSame('300.00', $stale->accountTransaction->paymentAccount->current_balance);
    }

    public function test_staff_cash_and_mobile_money_use_distinct_holding_accounts(): void
    {
        $customer = $this->customer();
        $staff = \App\Models\User::factory()->create(['role' => 'delivery_staff']);
        $cash = $this->payment($customer, ['received_by' => $staff->id, 'collection_source' => 'delivery_staff']);
        $mobile = $this->payment($customer, ['payment_method' => 'bkash']);
        $this->assertSame('Collections - Cash - Staff '.$staff->id, $cash->accountTransaction->paymentAccount->name);
        $this->assertSame('Collections - bKash', $mobile->accountTransaction->paymentAccount->name);
        $this->assertNotSame($cash->accountTransaction->payment_account_id, $mobile->accountTransaction->payment_account_id);
    }

    public function test_dealer_collection_posts_to_dealer_income(): void
    {
        $zone = \App\Models\Zone::create(['name' => 'Dealer zone', 'code' => 'DLR-01', 'status' => 'active']);
        $dealer = \App\Models\Dealer::create(['name' => 'Dealer', 'mobile' => '01812345678', 'address' => 'Dhaka', 'zone_id' => $zone->id, 'approval_status' => 'approved']);
        $payment = Payment::create(['payment_type' => 'dealer', 'dealer_id' => $dealer->id, 'payment_date' => today(), 'amount' => 50]);
        $this->assertSame('Dealer Payment', $payment->accountTransaction->category->name);
        $this->assertSame('50.00', $payment->accountTransaction->paymentAccount->current_balance);
    }

    public function test_rejected_payment_is_excluded_from_delivery_totals(): void
    {
        $customer = $this->customer();
        $order = Order::create(['order_type' => 'customer', 'customer_id' => $customer->id, 'order_date' => today(), 'total_amount' => 1000]);
        $delivery = \App\Models\Delivery::create(['order_id' => $order->id, 'delivery_status' => 'pending']);
        $payment = $this->payment($customer, ['order_id' => $order->id, 'delivery_id' => $delivery->id, 'collection_status' => 'rejected']);
        $this->assertSame(0.0, $delivery->totalCollectedAmount());
        $this->assertFalse($delivery->hasPaymentCollection());
        $payment->update(['collection_status' => 'accepted']);
        $this->assertSame(600.0, $delivery->totalCollectedAmount());
    }

    public function test_sales_report_and_ledger_exclude_unaccepted_collections(): void
    {
        $customer = $this->customer();
        $this->payment($customer);
        $this->payment($customer, ['collection_status' => 'pending_review']);
        $this->payment($customer, ['collection_status' => 'rejected']);
        $report = new \App\Filament\Admin\Pages\SalesReport;
        $report->customer_id = $customer->id;
        $this->assertSame(600.0, $report->getTotalAmount());
        $ledger = new \App\Filament\Admin\Pages\CustomerLedger;
        $ledger->customer_id = $customer->id;
        $this->assertSame(600.0, $ledger->getLedgerEntries()->sum('credit'));
    }
}
