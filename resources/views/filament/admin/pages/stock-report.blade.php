<x-filament-panels::page>
@php
    $products      = $this->getProducts();
    $totalActive   = $products->where('status', 'active')->count();
    $lowStockCount = $products->filter(fn ($p) => $p->isLowStock())->count();
    $totalStock    = $products->where('status', 'active')->sum('current_stock');
    $typeColors    = ['jar'=>'#2563eb','bottle'=>'#16a34a','accessory'=>'#f59e0b'];
@endphp

{{-- Filter Bar --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Product Type</label>
        <select wire:model.live="product_type" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Types</option>
            @foreach(\App\Models\Product::typeLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Status</label>
        <select wire:model.live="status" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All</option>
            @foreach(\App\Models\Product::statusLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div style="display:flex;align-items:center;gap:8px;padding-bottom:2px">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer">
            <input type="checkbox" wire:model.live="low_stock_only" style="width:14px;height:14px;cursor:pointer">
            Low stock only
        </label>
    </div>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#0077B6,#0369a1);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $totalActive }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Active Products</div>
    </div>
    <div style="background:{{ $lowStockCount > 0 ? 'linear-gradient(135deg,#dc2626,#b91c1c)' : 'linear-gradient(135deg,#16a34a,#15803d)' }};border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $lowStockCount }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">{{ $lowStockCount > 0 ? '⚠ Low Stock' : '✓ All OK' }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#0284c7,#0369a1);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ number_format($totalStock) }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Total Stock Qty</div>
    </div>
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <span style="font-size:14px;font-weight:600;color:#111827">Product Stock</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $products->count() }} products</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">SKU</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Product Name</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Type</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Current Stock</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Alert Qty</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Status</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Stock Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                    <tr style="border-bottom:1px solid #f9fafb;{{ $p->isLowStock() ? 'background:#fff5f5' : '' }}">
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280">{{ $p->sku }}</td>
                        <td style="padding:10px 16px;font-weight:600;color:#111827">{{ $p->name }}</td>
                        <td style="padding:10px 16px">
                            @php $tc = $typeColors[$p->product_type] ?? '#6b7280'; @endphp
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $tc }};display:inline-block"></span>
                                {{ ucfirst($p->product_type) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:right">
                            <span style="font-size:18px;font-weight:800;{{ $p->isLowStock() ? 'color:#b91c1c' : 'color:#111827' }}">{{ $p->current_stock }}</span>
                        </td>
                        <td style="padding:10px 16px;text-align:right;color:#9ca3af">{{ $p->stock_alert_qty }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $p->status === 'active' ? 'background:#dcfce7;color:#15803d' : 'background:#f3f4f6;color:#9ca3af' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:center">
                            @if($p->isLowStock())
                                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:700;background:#fee2e2;color:#b91c1c">⚠ Low Stock</span>
                            @else
                                <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d">✓ OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <p style="font-size:13px;font-weight:500">No products found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-filament-panels::page>
