<?php

namespace Database\Seeders;

use App\Models\AccountCategory;
use Illuminate\Database\Seeder;

class AccountCategorySeeder extends Seeder
{
    public function run(): void
    {
        $expenseCategories = [
            'Staff Salary', 'Jar Purchase', 'Machinery Purchase', 'Machinery Maintenance',
            'Maintenance Equipment', 'Vehicle Cost', 'Office Rent', 'Utility Bill',
            'Marketing', 'Software / IT Cost', 'Staff Advance', 'Vendor Payment', 'Other Expense',
        ];

        $incomeCategories = [
            'Customer Payment', 'Dealer Payment', 'Corporate Payment', 'Investor Fund',
            'Bank Deposit', 'Other Income',
        ];

        foreach ($expenseCategories as $name) {
            AccountCategory::updateOrCreate(
                ['name' => $name, 'type' => 'expense'],
                ['status' => true],
            );
        }

        foreach ($incomeCategories as $name) {
            AccountCategory::updateOrCreate(
                ['name' => $name, 'type' => 'income'],
                ['status' => true],
            );
        }
    }
}
