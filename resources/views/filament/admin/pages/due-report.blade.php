<x-filament-panels::page>
@php
    $rows        = $this->getRows();
    $customerDue = $rows->where('party_type', 'Customer')->sum('current_due');
    $dealerDue   = $rows->where('party_type', 'Dealer')->sum('current_due');
    $grandTotal  = $customerDue + $dealerDue;
    $withDue     = $rows->where('current_due', '>', 0)->count();
@endphp

{{-- Filter Bar --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Party Type</label>
            <select wire:model.live="party_type"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">All Parties</option>
                <option value="customer">Customers Only</option>
                <option value="dealer">Dealers Only</option>
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Approval Status</label>
            <select wire:model.live="approval_status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="flex items-center gap-2 pb-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="include_zero_due"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span>Include zero due</span>
            </label>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-danger-500 to-danger-600 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Customer Due</div>
        <div class="text-xl font-bold">৳ {{ number_format($customerDue, 2) }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Dealer Due</div>
        <div class="text-xl font-bold">৳ {{ number_format($dealerDue, 2) }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-gray-700 to-gray-800 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Grand Total Due</div>
        <div class="text-xl font-bold">৳ {{ number_format($grandTotal, 2) }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $withDue }}</div>
        <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Parties with Due</div>
    </div>
</div>

{{-- Table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Outstanding Dues</h3>
        <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">{{ $rows->count() }} records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-left font-semibold">ID / Code</th>
                    <th class="px-4 py-3 text-left font-semibold">Name</th>
                    <th class="px-4 py-3 text-left font-semibold">Mobile</th>
                    <th class="px-4 py-3 text-left font-semibold">Zone</th>
                    <th class="px-4 py-3 text-right font-semibold">Current Due (৳)</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($rows as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors {{ $row['current_due'] > 0 ? '' : 'opacity-60' }}">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $row['party_type'] === 'Customer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                {{ $row['party_type'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $row['code'] }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $row['name'] }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['mobile'] }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['zone'] }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($row['current_due'] > 0)
                                <span class="font-bold text-danger-600 dark:text-danger-400">{{ number_format($row['current_due'], 2) }}</span>
                            @else
                                <span class="text-success-600 dark:text-success-400 font-medium">0.00</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize
                                {{ $row['approval_status'] === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                {{ $row['approval_status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-medium">No outstanding dues</p>
                                <p class="text-xs mt-1">All parties are clear!</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->count() > 0)
            <tfoot>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-t-2 border-gray-200 dark:border-gray-700">
                    <td colspan="5" class="px-4 py-3 text-right text-sm font-bold text-gray-700 dark:text-gray-300">Grand Total</td>
                    <td class="px-4 py-3 text-right font-bold text-danger-600 dark:text-danger-400 text-base">৳ {{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</x-filament-panels::page>
