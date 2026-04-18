<x-filament-panels::page>
@php
    $deliveries   = $this->getDeliveries();
    $total        = $deliveries->count();
    $delivered    = $deliveries->where('delivery_status', 'delivered')->count();
    $pending      = $deliveries->whereIn('delivery_status', ['pending','assigned','in_progress'])->count();
    $failed       = $deliveries->where('delivery_status', 'failed')->count();
    $cancelled    = $deliveries->where('delivery_status', 'cancelled')->count();
    $nonCancelled = $total - $cancelled;
    $successRate  = $nonCancelled > 0 ? round($delivered / $nonCancelled * 100, 1) : 0;

    $statusColors = [
        'pending'     => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
        'assigned'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
        'delivered'   => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'failed'      => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'cancelled'   => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500',
    ];
@endphp

{{-- Filter Bar --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Assigned From</label>
            <input type="date" wire:model="assigned_from"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Assigned Until</label>
            <input type="date" wire:model="assigned_until"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Status</label>
            <select wire:model="delivery_status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Delivery::statusLabels() as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-shrink-0">
            <x-filament::button wire:click="$refresh" icon="heroicon-m-funnel" size="sm">Apply</x-filament::button>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 gap-4 sm:grid-cols-5 mb-5">
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $total }}</div>
        <div class="text-xs text-gray-500 mt-1 font-medium uppercase tracking-wide">Total</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-success-500 to-success-600 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $delivered }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Delivered</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $pending }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Active</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-danger-500 to-danger-600 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $failed }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Failed</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-info-500 to-info-600 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $successRate }}%</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Success Rate</div>
    </div>
</div>

{{-- Table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Delivery Records</h3>
        <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">{{ $total }} records</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-semibold">Delivery No</th>
                    <th class="px-4 py-3 text-left font-semibold">Order No</th>
                    <th class="px-4 py-3 text-left font-semibold">Customer / Dealer</th>
                    <th class="px-4 py-3 text-left font-semibold">Zone</th>
                    <th class="px-4 py-3 text-left font-semibold">Staff</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-left font-semibold">Assigned</th>
                    <th class="px-4 py-3 text-left font-semibold">Delivered</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($deliveries as $d)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $d->delivery_no }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $d->order?->order_no ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                            @if($d->order?->customer) {{ $d->order->customer->name }}
                            @elseif($d->order?->dealer) {{ $d->order->dealer->name }}
                            @else <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $d->zone?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $d->deliveryStaff?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$d->delivery_status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucwords(str_replace('_', ' ', $d->delivery_status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $d->assigned_at?->format('d M H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs {{ $d->delivered_at ? 'text-success-600 dark:text-success-400 font-medium' : 'text-gray-400' }}">
                            {{ $d->delivered_at?->format('d M H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"/></svg>
                                <p class="text-sm font-medium">No deliveries found</p>
                                <p class="text-xs mt-1">Try adjusting your filters</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-filament-panels::page>
