<?php

namespace Tests\Feature;

use App\Models\{AccountTransaction, Customer, Investor, InvestorTransaction, JarPurchase, MaintenanceCost, Order, Payment, PaymentAccount, StaffSalaryPayment, Supplier, User};
use App\Services\Accounts\AccountTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountsConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_balance_edit_preserves_postings_and_latest_balance(): void
    {
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 100]);
        $this->assertSame('100.00', $account->fresh()->current_balance);
        $stale = PaymentAccount::find($account->id);
        app(AccountTransactionService::class)->createIncome(['payment_account_id' => $account->id, 'amount' => 50]);
        $stale->update(['opening_balance' => 200]);
        $this->assertSame('250.00', $account->fresh()->current_balance);
    }

    public function test_used_account_cannot_be_deleted(): void
    {
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash']);
        app(AccountTransactionService::class)->createIncome(['payment_account_id' => $account->id, 'amount' => 50]);
        $this->expectException(ValidationException::class);
        $account->refresh()->delete();
    }

    public function test_invalid_transaction_amounts_and_inactive_accounts_roll_back(): void
    {
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 100]);
        $service = app(AccountTransactionService::class);
        foreach ([0, -50] as $amount) {
            try {
                $service->createExpense(['payment_account_id' => $account->id, 'amount' => $amount]);
                $this->fail('Invalid amount accepted');
            } catch (ValidationException) {
                $this->assertSame('100.00', $account->fresh()->current_balance);
                $this->assertSame(0, AccountTransaction::count());
            }
        }
        $pending = $service->createIncome(['payment_account_id' => $account->id, 'amount' => 10, 'status' => 'pending']);
        $account->update(['status' => false]);
        try {
            $service->approveTransaction($pending);
            $this->fail('Inactive account accepted');
        } catch (ValidationException) {
            $this->assertSame('pending', $pending->fresh()->status);
            $this->assertSame('100.00', $account->fresh()->current_balance);
        }
    }

    public function test_linked_source_delete_restore_is_atomic_and_idempotent(): void
    {
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 1000]);
        $supplier = Supplier::create(['name' => 'Supplier']);
        $investor = Investor::create(['name' => 'Investor']);
        $records = [
            StaffSalaryPayment::create(['salary_month' => '2026-10', 'payment_date' => today(), 'paid_amount' => 100, 'payment_account_id' => $account->id]),
            JarPurchase::create(['purchase_date' => today(), 'supplier_id' => $supplier->id, 'total_amount' => 150, 'paid_amount' => 100, 'due_amount' => 50, 'payment_account_id' => $account->id]),
            MaintenanceCost::create(['maintenance_date' => today(), 'cost' => 100, 'payment_account_id' => $account->id]),
            InvestorTransaction::create(['investor_id' => $investor->id, 'transaction_date' => today(), 'transaction_type' => 'return_paid', 'amount' => 100, 'payment_account_id' => $account->id]),
        ];
        $service = app(AccountTransactionService::class);
        foreach ($records as $record) {
            $entry = $service->createExpense(['payment_account_id' => $account->id, 'amount' => 100]);
            $record->update(['account_transaction_id' => $entry->id]);
            $this->assertTrue($entry->hasSourceRecord());
            $this->assertSame('900.00', $account->fresh()->current_balance);
            $record->delete();
            $this->assertSame('1000.00', $account->fresh()->current_balance);
            $this->assertTrue($entry->fresh()->trashed());
            $record->restore();
            $record->restore();
            $this->assertSame('900.00', $account->fresh()->current_balance);
            $record->delete();
            $record->delete();
            $this->assertSame('1000.00', $account->fresh()->current_balance);
        }
        $this->assertSame('0.00', $supplier->fresh()->current_due);
        $records[1]->restore();
        $this->assertSame('50.00', $supplier->fresh()->current_due);
        $records[3]->restore();
        $this->assertSame('100.00', $investor->fresh()->total_returned);
    }

    public function test_failed_restore_keeps_both_source_and_posting_deleted(): void
    {
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 100]);
        $service = app(AccountTransactionService::class);
        $entry = $service->createExpense(['payment_account_id' => $account->id, 'amount' => 100]);
        $salary = StaffSalaryPayment::create(['salary_month' => '2026-10', 'payment_date' => today(), 'paid_amount' => 100, 'payment_account_id' => $account->id, 'account_transaction_id' => $entry->id]);
        $salary->delete();
        $service->createExpense(['payment_account_id' => $account->id, 'amount' => 90]);
        try {
            $salary->restore();
            $this->fail('Overdrawn restore accepted');
        } catch (ValidationException) {
            $this->assertTrue($salary->fresh()->trashed());
            $this->assertTrue($entry->fresh()->trashed());
            $this->assertSame('10.00', $account->fresh()->current_balance);
        }
    }

    public function test_payments_sync_old_and_new_order_status_through_lifecycle(): void
    {
        $customer = Customer::create(['name' => 'Customer', 'mobile' => '01700000123', 'approval_status' => 'approved']);
        $data = ['customer_id' => $customer->id, 'order_type' => 'customer', 'order_date' => today(), 'total_amount' => 100];
        $first = Order::create($data);
        $second = Order::create($data);
        $payment = Payment::create(['payment_type' => 'customer', 'customer_id' => $customer->id, 'order_id' => $first->id, 'payment_date' => today(), 'amount' => 40]);
        $this->assertSame('partial', $first->fresh()->payment_status);
        $payment->update(['amount' => 100]);
        $this->assertSame('paid', $first->fresh()->payment_status);
        $payment->update(['order_id' => $second->id]);
        $this->assertSame('unpaid', $first->fresh()->payment_status);
        $this->assertSame('paid', $second->fresh()->payment_status);
        $payment->delete();
        $this->assertSame('unpaid', $second->fresh()->payment_status);
        $payment->restore();
        $this->assertSame('paid', $second->fresh()->payment_status);
        $second->refresh()->update(['total_amount' => 200]);
        $this->assertSame('partial', $second->fresh()->payment_status);
    }

    public function test_paid_purchase_requires_destination_account(): void
    {
        $this->expectException(ValidationException::class);
        JarPurchase::create(['purchase_date' => today(), 'paid_amount' => 50]);
    }

    public function test_ledger_uses_delivered_orders_and_carries_opening_balance(): void
    {
        $customer = Customer::create(['name' => 'Ledger customer', 'mobile' => '01700000456', 'approval_status' => 'approved']);
        $base = ['customer_id' => $customer->id, 'order_type' => 'customer', 'order_status' => 'delivered'];
        Order::create($base + ['order_date' => '2026-09-20', 'total_amount' => 100]);
        Order::create($base + ['order_date' => '2026-10-10', 'total_amount' => 200]);
        Payment::create(['customer_id' => $customer->id, 'payment_type' => 'customer', 'payment_date' => '2026-09-21', 'amount' => 30]);
        Payment::create(['customer_id' => $customer->id, 'payment_type' => 'customer', 'payment_date' => '2026-10-11', 'amount' => 50]);
        \App\Models\Invoice::create(['customer_id' => $customer->id, 'invoice_type' => 'customer', 'invoice_date' => '2026-10-15', 'billing_month' => 10, 'billing_year' => 2026, 'subtotal' => 200, 'previous_due' => 70, 'total_amount' => 270, 'invoice_status' => 'issued']);
        $page = new \App\Filament\Admin\Pages\CustomerLedger();
        $page->customer_id = $customer->id;
        $page->date_from = '2026-10-01';
        $page->date_until = '2026-10-31';
        $this->assertSame(70.0, $page->getOpeningBalance());
        $this->assertSame(200.0, $page->getLedgerEntries()->sum('debit'));
        $this->assertSame(50.0, $page->getLedgerEntries()->sum('credit'));
        $this->assertSame((float) $customer->fresh()->current_due, $page->getOpeningBalance() + $page->getLedgerEntries()->sum('debit') - $page->getLedgerEntries()->sum('credit'));
    }

    public function test_linked_entries_are_not_editable_in_income_or_expense_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $account = PaymentAccount::create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 100]);
        $entry = app(AccountTransactionService::class)->createExpense(['payment_account_id' => $account->id, 'amount' => 50]);
        StaffSalaryPayment::create(['salary_month' => '2026-10', 'payment_date' => today(), 'paid_amount' => 50, 'payment_account_id' => $account->id, 'account_transaction_id' => $entry->id]);
        $this->assertFalse(\App\Filament\Admin\Resources\ExpenseResource::canEdit($entry));
        $this->assertFalse(\App\Filament\Admin\Resources\ExpenseResource::canDelete($entry));
        $this->assertFalse(\App\Filament\Admin\Resources\IncomeResource::canEdit($entry));
    }
}
