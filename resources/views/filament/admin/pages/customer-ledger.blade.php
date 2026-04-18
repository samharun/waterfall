<x-filament-panels::page>
@php
    $customer    = $this->getCustomer();
    $entries     = $this->getLedgerEntries();
    $totalDebit  = $entries->sum('debit');
    $totalCredit = $entries->sum('credit');
    $netBalance  = $totalDebit - $totalCredit;
    $balance     = 0.0;
@endphp

{{-- Customer Selector --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[250px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Customer *</label>
            <select wire:model="customer_id"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">— Select a Customer —</option>
                @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->customer_id }} — {{ $c->name }} ({{ $c->mobile }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">From Date</label>
            <input type="date" wire:model="date_from"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Until Date</label>
            <input type="date" wire:model="date_until"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="flex-shrink-0">
            <x-filament::button wire:click="$refresh" icon="heroicon-m-document-text" size="sm">Load Ledger</x-filament::button>
        </div>
    </div>
</div>

@if($customer)

{{-- Customer Info Card --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-5 mb-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <div class="h-10 w-10 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-sm">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-gray-100 text-lg">{{ $customer->name }}</h3>
                    <p class="text-xs font-mono text-gray-500">{{ $customer->customer_id }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-600 dark:text-gray-400">
                <span>📞 {{ $customer->mobile }}</span>
                @if($customer->zone) <span>📍 {{ $customer->zone->name }}</span> @endif
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize
                    {{ $customer->approval_status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600' }}">
                    {{ $customer->approval_status }}
                </span>
            </div>
        </div>
        <div class="text-right">
            <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Current Due</div>
            <div class="text-2xl font-bold {{ (float)$customer->current_due > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                ৳ {{ number_format((float)$customer->current_due, 2) }}
            </div>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-danger-500 to-danger-600 p-4 text-white shadow-sm text-center">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Total Debit</div>
        <div class="text-xl font-bold">৳ {{ number_format($totalDebit, 2) }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-success-500 to-success-600 p-4 text-white shadow-sm text-center">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Total Credit</div>
        <div class="text-xl font-bold">৳ {{ number_format($totalCredit, 2) }}</div>
    </div>
    <div class="rounded-xl {{ $netBalance > 0 ? 'bg-gradient-to-br from-amber-500 to-amber-600' : 'bg-gradient-to-br from-gray-600 to-gray-700' }} p-4 text-white shadow-sm text-center">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Net Balance</div>
        <div class="text-xl font-bold">৳ {{ number_format($netBalance, 2) }}</div>
    </div>
</div>

{{-- Ledger Table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Ledger Entries</h3>
        <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">{{ $entries->count() }} entries</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Reference</th>
                    <th class="px-4 py-3 text-left font-semibold">Description</th>
                    <th class="px-4 py-3 text-right font-semibold text-danger-600 dark:text-danger-400">Debit (৳)</th>
                    <th class="px-4 py-3 text-right font-semibold text-success-600 dark:text-success-400">Credit (৳)</th>
                    <th class="px-4 py-3 text-right font-semibold">Balance (৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($entries as $entry)
                    @php $balance += $entry['debit'] - $entry['credit']; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                            {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $entry['reference'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $entry['type'] === 'invoice' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}">
                                {{ $entry['description'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($entry['debit'] > 0)
                                <span class="font-semibold text-danger-600 dark:text-danger-400">{{ number_format($entry['debit'], 2) }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-700">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($entry['credit'] > 0)
                                <span class="font-semibold text-success-600 dark:text-success-400">{{ number_format($entry['credit'], 2) }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-700">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $balance > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                            {{ number_format($balance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-medium">No ledger entries found</p>
                                <p class="text-xs mt-1">Try adjusting the date range</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($entries->count() > 0)
            <tfoot>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-t-2 border-gray-200 dark:border-gray-700 font-bold">
                    <td colspan="3" class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">Totals</td>
                    <td class="px-4 py-3 text-right text-danger-600 dark:text-danger-400">{{ number_format($totalDebit, 2) }}</td>
                    <td class="px-4 py-3 text-right text-success-600 dark:text-success-400">{{ number_format($totalCredit, 2) }}</td>
                    <td class="px-4 py-3 text-right {{ $netBalance > 0 ? 'text-danger-600 dark:text-danger-400' : 'text-success-600 dark:text-success-400' }}">
                        {{ number_format($netBalance, 2) }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@else
<div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-12 text-center">
    <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    <p class="text-gray-500 dark:text-gray-400 font-medium">Select a customer above to view their ledger</p>
    <p class="text-gray-400 dark:text-gray-600 text-sm mt-1">All invoices and payments will appear here</p>
</div>
@endif
</x-filament-panels::page>
