<x-filament-panels::page>
@php
    $products      = $this->getProducts();
    $totalActive   = $products->where('status', 'active')->count();
    $lowStockCount = $products->filter(fn ($p) => $p->isLowStock())->count();
    $totalStock    = $products->where('status', 'active')->sum('current_stock');

    $typeColors = [
        'jar'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'bottle'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'accessory' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
    ];
@endphp

{{-- Filter Bar --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm p-4 mb-5">
    <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Product Type</label>
            <select wire:model.live="product_type"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Types</option>
                @foreach(\App\Models\Product::typeLabels() as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wide">Status</label>
            <select wire:model.live="status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All</option>
                @foreach(\App\Models\Product::statusLabels() as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pb-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
                <input type="checkbox" wire:model.live="low_stock_only"
                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span>Low stock only</span>
            </label>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $totalActive }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Active Products</div>
    </div>
    <div class="rounded-xl {{ $lowStockCount > 0 ? 'bg-gradient-to-br from-danger-500 to-danger-600' : 'bg-gradient-to-br from-success-500 to-success-600' }} p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ $lowStockCount }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">{{ $lowStockCount > 0 ? '⚠ Low Stock' : '✓ Low Stock' }}</div>
    </div>
    <div class="rounded-xl bg-gradient-to-br from-info-500 to-info-600 p-4 text-white shadow-sm text-center">
        <div class="text-2xl font-bold">{{ number_format($totalStock) }}</div>
        <div class="text-xs opacity-80 mt-1 font-medium uppercase tracking-wide">Total Stock Qty</div>
    </div>
</div>

{{-- Table --}}
<div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-800">
        <h3 class="font-semibold text-gray-800 dark:text-gray-200">Product Stock</h3>
        <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">{{ $products->count() }} products</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-semibold">SKU</th>
                    <th class="px-4 py-3 text-left font-semibold">Product Name</th>
                    <th class="px-4 py-3 text-left font-semibold">Type</th>
                    <th class="px-4 py-3 text-right font-semibold">Current Stock</th>
                    <th class="px-4 py-3 text-right font-semibold">Alert Qty</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-center font-semibold">Stock Level</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($products as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors {{ $p->isLowStock() ? 'bg-danger-50/50 dark:bg-danger-900/10' : '' }}">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $p->sku }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $p->name }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$p->product_type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($p->product_type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-lg {{ $p->isLowStock() ? 'text-danger-600 dark:text-danger-400' : 'text-gray-800 dark:text-gray-200' }}">
                                {{ $p->current_stock }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $p->stock_alert_qty }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $p->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($p->isLowStock())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400">
                                    ⚠ Low Stock
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                    ✓ OK
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <p class="text-sm font-medium">No products found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-filament-panels::page>
