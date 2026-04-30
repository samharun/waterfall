<x-filament-panels::page>
@php
    $orders = $this->getOrders();
    $stats  = $this->getStats();
    $zones  = $this->getZones();

    $statusStyle = [
        'pending'   => 'background:#fef9c3;color:#92400e',
        'confirmed' => 'background:#dbeafe;color:#1d4ed8',
        'assigned'  => 'background:#e0e7ff;color:#3730a3',
        'delivered' => 'background:#dcfce7;color:#15803d',
        'cancelled' => 'background:#f3f4f6;color:#9ca3af',
    ];
    $payStyle = [
        'unpaid'  => 'background:#fee2e2;color:#b91c1c',
        'partial' => 'background:#fef9c3;color:#92400e',
        'paid'    => 'background:#dcfce7;color:#15803d',
    ];
    $slotStyle = [
        'now'       => 'background:#dcfce7;color:#15803d',
        'morning'   => 'background:#dbeafe;color:#1d4ed8',
        'afternoon' => 'background:#fef9c3;color:#92400e',
        'evening'   => 'background:#f3e8ff;color:#6d28d9',
        'custom'    => 'background:#f3f4f6;color:#374151',
    ];
@endphp

{{-- Filter Bar --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">FROM</label>
        <input type="date" wire:model="date_from" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">UNTIL</label>
        <input type="date" wire:model="date_until" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">STATUS</label>
        <select wire:model="order_status" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Statuses</option>
            @foreach(\App\Models\Order::orderStatusLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:120px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">TYPE</label>
        <select wire:model="order_type" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Types</option>
            <option value="customer">Customer</option>
            <option value="dealer">Dealer</option>
        </select>
    </div>
    <div style="flex:1;min-width:120px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">PAYMENT</label>
        <select wire:model="payment_status" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All</option>
            @foreach(\App\Models\Order::paymentStatusLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">ZONE</label>
        <select wire:model="zone_id" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Zones</option>
            @foreach($zones as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
            @endforeach
        </select>
    </div>
    <button wire:click="$refresh" style="padding:8px 18px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;height:38px">
        Apply Filter
    </button>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#0077B6,#005f92);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,119,182,.2)">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Total Orders</div>
        <div style="font-size:30px;font-weight:800;color:#fff">{{ $stats['total'] }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(22,163,74,.2)">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Delivered</div>
        <div style="font-size:30px;font-weight:800;color:#fff">{{ $stats['delivered'] }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(245,158,11,.2)">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Pending / Confirmed</div>
        <div style="font-size:30px;font-weight:800;color:#fff">{{ $stats['pending'] + $stats['confirmed'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Total Revenue</div>
        <div style="font-size:22px;font-weight:800;color:#0077B6">৳ {{ number_format($stats['revenue'], 2) }}</div>
        @if($stats['unpaid'] > 0)
            <div style="font-size:11px;color:#dc2626;margin-top:4px">{{ $stats['unpaid'] }} unpaid orders</div>
        @endif
    </div>
</div>

{{-- Orders Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f1f5f9;background:#f8fafc">
        <span style="font-size:14px;font-weight:700;color:#0f172a">Order Records</span>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $orders->count() }} records</span>
            <a href="{{ route('filament.admin.resources.orders.create') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:#0077B6;color:#fff;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none">
                + New Order
            </a>
        </div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Order No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Date</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Type</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Customer / Dealer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Zone</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Slot</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Amount (৳)</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Payment</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Status</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr style="border-bottom:1px solid #f1f5f9;transition:background .1s" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background=''">
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#64748b;white-space:nowrap">{{ $order->order_no }}</td>
                        <td style="padding:10px 16px;color:#374151;white-space:nowrap">{{ $order->order_date?->format('d M Y') }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $order->order_type === 'customer' ? 'background:#dbeafe;color:#1d4ed8' : 'background:#fef9c3;color:#92400e' }}">
                                {{ ucfirst($order->order_type) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px">
                            @if($order->customer)
                                <div style="font-weight:600;color:#0f172a;font-size:13px">{{ $order->customer->name }}</div>
                                <div style="font-size:11px;color:#64748b;font-family:monospace">{{ $order->customer->customer_id }}</div>
                            @elseif($order->dealer)
                                <div style="font-weight:600;color:#0f172a;font-size:13px">{{ $order->dealer->name }}</div>
                                <div style="font-size:11px;color:#64748b;font-family:monospace">{{ $order->dealer->dealer_code }}</div>
                            @else
                                <span style="color:#9ca3af">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;color:#64748b;font-size:12px">{{ $order->zone?->name ?? '—' }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $slotStyle[$order->preferred_delivery_slot] ?? 'background:#f3f4f6;color:#374151' }}">
                                {{ ucfirst($order->preferred_delivery_slot) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:#0f172a;font-size:14px">
                            {{ number_format((float)$order->total_amount, 2) }}
                        </td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $payStyle[$order->payment_status] ?? 'background:#f3f4f6;color:#374151' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $statusStyle[$order->order_status] ?? 'background:#f3f4f6;color:#374151' }}">
                                {{ ucfirst($order->order_status) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:center">
                            <a href="{{ route('filament.admin.resources.orders.edit', $order) }}"
                               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#f0f9ff;border:1px solid #bfdbfe;color:#2563eb;text-decoration:none" title="Edit">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                            <p style="font-size:13px;font-weight:500">No orders found</p>
                            <p style="font-size:12px;margin-top:4px">Try adjusting your filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($orders->count() > 0)
            <tfoot>
                <tr style="background:#f8fafc;border-top:2px solid #e5e7eb">
                    <td colspan="6" style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151">Total</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#0077B6;font-size:15px">৳ {{ number_format($stats['revenue'], 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</x-filament-panels::page>
