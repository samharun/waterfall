<x-filament-panels::page>
    <form wire:submit.prevent="$refresh" class="mb-6 grid gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900 md:grid-cols-3">
        <input type="date" wire:model="start_date" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950" />
        <input type="date" wire:model="end_date" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950" />
        <select wire:model="transaction_type" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">All Types</option><option value="income">Income</option><option value="expense">Expense</option>
        </select>
        <select wire:model="category_id" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">All Categories</option>
                @foreach ($this->categories() as $category)<option value="{{ $category->id }}">{{ $category->name }} ({{ $category->type }})</option>@endforeach
        </select>
        <select wire:model="payment_account_id" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">All Accounts</option>
                @foreach ($this->paymentAccounts() as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach
        </select>
        <select wire:model="status" class="rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950">
                <option value="">All Status</option><option value="approved">Approved</option><option value="pending">Pending</option><option value="rejected">Rejected</option>
        </select>
    </form>

    @php($transactions = $this->transactions())
    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-sm text-gray-500">Income</div><div class="text-xl font-semibold">BDT {{ number_format((float) $transactions->where('transaction_type', 'income')->sum('amount'), 2) }}</div></div>
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-sm text-gray-500">Expense</div><div class="text-xl font-semibold">BDT {{ number_format((float) $transactions->where('transaction_type', 'expense')->sum('amount'), 2) }}</div></div>
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-sm text-gray-500">Staff Salary</div><div class="text-xl font-semibold">BDT {{ number_format((float) $this->salaryPayments()->sum('paid_amount'), 2) }}</div></div>
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900"><div class="text-sm text-gray-500">Supplier Due</div><div class="text-xl font-semibold">BDT {{ number_format((float) $this->suppliers()->sum('current_due'), 2) }}</div></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 font-semibold">Daily / Monthly Cash Report</h3>
            <table class="w-full text-sm"><thead><tr class="text-left"><th>Date</th><th>No</th><th>Type</th><th>Category</th><th>Account</th><th class="text-right">Amount</th></tr></thead><tbody>
                @foreach ($transactions as $row)<tr class="border-t dark:border-gray-800"><td>{{ $row->transaction_date?->format('Y-m-d') }}</td><td>{{ $row->transaction_no }}</td><td>{{ ucfirst($row->transaction_type) }}</td><td>{{ $row->category?->name }}</td><td>{{ $row->paymentAccount?->name }}</td><td class="text-right">BDT {{ number_format((float) $row->amount, 2) }}</td></tr>@endforeach
            </tbody></table>
        </div>
        <div class="rounded-lg border bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 font-semibold">Category-wise Expense</h3>
            @foreach ($this->categoryExpense() as $category => $amount)<div class="flex justify-between border-t py-2 dark:border-gray-800"><span>{{ $category }}</span><strong>BDT {{ number_format((float) $amount, 2) }}</strong></div>@endforeach
            <h3 class="mb-3 mt-6 font-semibold">Payment Account Balance</h3>
            @foreach ($this->paymentAccounts() as $account)<div class="flex justify-between border-t py-2 dark:border-gray-800"><span>{{ $account->name }}</span><strong>BDT {{ number_format((float) $account->current_balance, 2) }}</strong></div>@endforeach
        </div>
    </div>
</x-filament-panels::page>
