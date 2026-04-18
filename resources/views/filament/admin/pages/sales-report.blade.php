<x-filament-panels::page>
@php
    $payments = $this->getPayments();
    $total    = $this->getTotalAmount();
    $count    = $this->getPaymentCount();

    $methodBreakdown = $payments->groupBy('payment_method')
        ->map(fn ($g) => $g->sum('amount'))
        ->sortByDesc(fn ($v) => $v);
@endphp

{{-- Filter Bar --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">From</label>
            <input type="date" wire:model="date_from"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Until</label>
            <input type="date" wire:model="date_until"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none">
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Type</label>
            <select wire:model="payment_type"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">All Types</option>
                <option value="customer">Customer</option>
                <option value="dealer">Dealer</option>
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Method</label>
            <select wire:model="payment_method"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 outline-none">
                <option value="">All Methods</option>
                @foreach(\App\Models\Payment::methodLabels() as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-shrink-0">
            <x-filament::button wire:click="$refresh" icon="heroicon-m-funnel" size="sm">
                Apply
            </x-filament::button>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-success-500 to-success-600 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Total Revenue</div>
        <div class="text-2xl font-bold">৳ {{ number_format($total, 2) }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Transactions</div>
        <div class="text-2xl font-bold">{{ $count }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-info-500 to-info-600 p-4 text-white shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide opacity-80 mb-1">Average</div>
        <div class="text-2xl font-bold">{{ $count > 0 ? '৳ '.number_format($total/$count,2) : '—' }}</div>
    </div>
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
        <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">By Method</div>
        @foreach($methodBreakdown->take(3) as $method => $amt)
            <div class="flex justify-between text-xs mb-1">
                <span class="capitalize text-gray-600 dark:text-gray-400">{{ $method }}</span>
                <span class="font-semibold">৳ {{ number_format($amt, 0) }}</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Payment Records</h3>
        <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">{{ $count }} records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-semibold">Payment No</th>
                    <th class="px-4 py-3 text-left font-semibold">Date</th>
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-left font-semibold">Customer / Dealer</th>
                    <th class="px-4 py-3 text-left font-semibold">Invoice</th>
                    <th class="px-4 py-3 text-left font-semibold">Method</th>
                    <th class="px-4 py-3 text-right font-semibold">Amount (৳)</th>
                    <th class="px-4 py-3 text-left font-semibold">Received By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($payments as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $p->payment_no }}</td>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $p->payment_date?->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $p->payment_type === 'customer' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                {{ ucfirst($p->payment_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                            @if($p->customer) {{ $p->customer->customer_id }} — {{ $p->customer->name }}
                            @elseif($p->dealer) {{ $p->dealer->dealer_code }} — {{ $p->dealer->name }}
                            @else <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $p->invoice?->invoice_no ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 capitalize">
                                {{ $p->payment_method }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-success-600 dark:text-success-400">
                            {{ number_format((float)$p->amount, 2) }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $p->receivedBy?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm font-medium">No payments found</p>
                                <p class="text-xs mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($count > 0)
            <tfoot>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-t-2 border-gray-200 dark:border-gray-700">
                    <td colspan="6" class="px-4 py-3 text-right text-sm font-bold text-gray-700 dark:text-gray-300">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-success-600 dark:text-success-400 text-base">৳ {{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</x-filament-panels::page>
