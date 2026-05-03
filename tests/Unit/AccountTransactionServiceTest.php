<?php

namespace Tests\Unit;

use App\Models\AccountCategory;
use App\Models\PaymentAccount;
use App\Services\Accounts\AccountTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AccountTransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_income_and_expense_update_payment_account_balance(): void
    {
        $account = PaymentAccount::create([
            'name' => 'Cash',
            'type' => 'cash',
            'opening_balance' => 100,
            'current_balance' => 100,
        ]);

        $incomeCategory = AccountCategory::create(['name' => 'Customer Payment', 'type' => 'income']);
        $expenseCategory = AccountCategory::create(['name' => 'Jar Purchase', 'type' => 'expense']);
        $service = app(AccountTransactionService::class);

        $income = $service->createIncome([
            'transaction_date' => '2026-05-03',
            'account_category_id' => $incomeCategory->id,
            'payment_account_id' => $account->id,
            'amount' => 50,
            'status' => 'approved',
        ]);

        $this->assertSame('150.00', $account->refresh()->current_balance);

        $expense = $service->createExpense([
            'transaction_date' => '2026-05-03',
            'account_category_id' => $expenseCategory->id,
            'payment_account_id' => $account->id,
            'amount' => 30,
            'status' => 'approved',
        ]);

        $this->assertSame('120.00', $account->refresh()->current_balance);

        $service->updateTransaction($expense, [
            'transaction_date' => '2026-05-03',
            'transaction_type' => 'expense',
            'account_category_id' => $expenseCategory->id,
            'payment_account_id' => $account->id,
            'amount' => 40,
            'status' => 'approved',
        ]);

        $this->assertSame('110.00', $account->refresh()->current_balance);

        $service->deleteTransaction($income);

        $this->assertSame('60.00', $account->refresh()->current_balance);
    }

    public function test_pending_transaction_does_not_affect_balance_until_approved(): void
    {
        $account = PaymentAccount::create(['name' => 'Bank', 'type' => 'bank', 'current_balance' => 100]);
        $category = AccountCategory::create(['name' => 'Other Income', 'type' => 'income']);
        $service = app(AccountTransactionService::class);

        $transaction = $service->createIncome([
            'transaction_date' => '2026-05-03',
            'account_category_id' => $category->id,
            'payment_account_id' => $account->id,
            'amount' => 25,
            'status' => 'pending',
        ]);

        $this->assertSame('100.00', $account->refresh()->current_balance);

        $service->approveTransaction($transaction);

        $this->assertSame('125.00', $account->refresh()->current_balance);
    }

    public function test_expense_cannot_exceed_available_balance(): void
    {
        $account = PaymentAccount::create(['name' => 'bKash', 'type' => 'mobile_banking', 'current_balance' => 10]);
        $category = AccountCategory::create(['name' => 'Other Expense', 'type' => 'expense']);

        $this->expectException(ValidationException::class);

        app(AccountTransactionService::class)->createExpense([
            'transaction_date' => '2026-05-03',
            'account_category_id' => $category->id,
            'payment_account_id' => $account->id,
            'amount' => 11,
            'status' => 'approved',
        ]);
    }

    public function test_updating_expense_still_checks_available_balance(): void
    {
        $account = PaymentAccount::create(['name' => 'Nagad', 'type' => 'mobile_banking', 'current_balance' => 100]);
        $category = AccountCategory::create(['name' => 'Utility Bill', 'type' => 'expense']);
        $service = app(AccountTransactionService::class);

        $expense = $service->createExpense([
            'transaction_date' => '2026-05-03',
            'account_category_id' => $category->id,
            'payment_account_id' => $account->id,
            'amount' => 40,
            'status' => 'approved',
        ]);

        $this->expectException(ValidationException::class);

        $service->updateTransaction($expense, [
            'transaction_date' => '2026-05-03',
            'account_category_id' => $category->id,
            'payment_account_id' => $account->id,
            'amount' => 101,
            'status' => 'approved',
        ]);
    }
}
