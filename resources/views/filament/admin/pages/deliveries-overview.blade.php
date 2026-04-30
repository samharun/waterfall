<x-filament-panels::page>
@php
    $deliveries = $this->getDeliveries();
    $stats      = $this->getStats();
    $zones      = $this->getZones();
    $staffList  = $this->getStaffList();

    $statusStyle = [
        'pending'              => 'background:#f3f4f6;color:#374151',
        'assigned'             => 'background:#dbeafe;color:#1d4ed8',
        'in_progress'          => 'background:#fef9c3;color:#92400e',
        'delivered'            => 'background:#dcfce7;color:#15803d',
        'partial_delivered'    => 'background:#d1fae5;color:#065f46',
        'not_delivered'        => 'background:#fee2e2;color:#b91c1c',
        'customer_unavailable' => 'background:#fff7ed;color:#9a3412',
        'failed'               => 'background:#fee2e2;color:#b91c1c',
        'cancelled'            => 'background:#f3f4f6;color:#9ca3af',
    ];
@endphp

{{-- Filter Bar --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">ASSIGNED FROM</label>
        <input type="date" wire:model="date_from" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">ASSIGNED UNTIL</label>
        <input type="date" wire:model="date_until" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">STATUS</label>
        <select wire:model="delivery_status" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Statuses</option>
            @foreach(\App\Models\Delivery::statusLabels() as $k => $v)
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
    <div style="flex:1;min-width:140px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">STAFF</label>
        <select wire:model="delivery_staff_id" style="width:100%;padding:8px 10px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Staff</option>
            @foreach($staffList as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>
    </div>
    <button wire:click="$refresh" style="padding:8px 18px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;height:38px">
        Apply Filter
    </button>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#0077B6,#005f92);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(0,119,182,.2)">
        <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Total</div>
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $stats['total'] }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(22,163,74,.2)">
        <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Delivered</div>
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $stats['delivered'] }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(245,158,11,.2)">
        <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Active</div>
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $stats['pending'] }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);border-radius:12px;padding:14px 16px;box-shadow:0 2px 8px rgba(220,38,38,.2)">
        <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Failed</div>
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $stats['failed'] }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:14px 16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Success Rate</div>
        <div style="font-size:24px;font-weight:800;color:{{ $stats['successRate'] >= 80 ? '#16a34a' : ($stats['successRate'] >= 50 ? '#f59e0b' : '#dc2626') }}">{{ $stats['successRate'] }}%</div>
        @if($stats['unassigned'] > 0)
            <div style="font-size:11px;color:#dc2626;margin-top:3px">{{ $stats['unassigned'] }} unassigned</div>
        @endif
    </div>
</div>

{{-- Collection strip --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
        </svg>
        <span style="font-size:13px;font-weight:600;color:#374151">Total Collection (period)</span>
    </div>
    <span style="font-size:18px;font-weight:800;color:#059669">৳ {{ number_format($stats['collection'], 2) }}</span>
</div>

{{-- Deliveries Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f1f5f9;background:#f8fafc">
        <span style="font-size:14px;font-weight:700;color:#0f172a">Delivery Records</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $deliveries->count() }} records</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Delivery No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Order No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Customer / Dealer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Zone</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Staff</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Status</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Assigned</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Delivered</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $d)
                    <tr style="border-bottom:1px solid #f1f5f9" onmouseover="this.style.background='#f0f9ff'" onmouseout="this.style.background=''">
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#64748b;white-space:nowrap">{{ $d->delivery_no }}</td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#64748b;white-space:nowrap">{{ $d->order?->order_no ?? '—' }}</td>
                        <td style="padding:10px 16px">
                            @if($d->order?->customer)
                                <div style="font-weight:600;color:#0f172a">{{ $d->order->customer->name }}</div>
                                <div style="font-size:11px;color:#64748b;font-family:monospace">{{ $d->order->customer->customer_id }}</div>
                            @elseif($d->order?->dealer)
                                <div style="font-weight:600;color:#0f172a">{{ $d->order->dealer->name }}</div>
                                <div style="font-size:11px;color:#64748b;font-family:monospace">{{ $d->order->dealer->dealer_code }}</div>
                            @else
                                <span style="color:#9ca3af">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;color:#64748b;font-size:12px">{{ $d->zone?->name ?? '—' }}</td>
                        <td style="padding:10px 16px;color:#374151;font-size:12px">{{ $d->deliveryStaff?->name ?? '—' }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $statusStyle[$d->delivery_status] ?? 'background:#f3f4f6;color:#374151' }}">
                                {{ ucwords(str_replace('_', ' ', $d->delivery_status)) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;font-size:11px;color:#64748b;white-space:nowrap">{{ $d->assigned_at?->format('d M H:i') ?? '—' }}</td>
                        <td style="padding:10px 16px;font-size:11px;white-space:nowrap;{{ $d->delivered_at ? 'color:#15803d;font-weight:600' : 'color:#9ca3af' }}">
                            {{ $d->delivered_at?->format('d M H:i') ?? '—' }}
                        </td>
                        <td style="padding:10px 16px;text-align:center">
                            <a href="{{ route('filament.admin.resources.deliveries.edit', $d) }}"
                               style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;background:#f0f9ff;border:1px solid #bfdbfe;color:#2563eb;text-decoration:none" title="Edit">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                            <p style="font-size:13px;font-weight:500">No deliveries found</p>
                            <p style="font-size:12px;margin-top:4px">Try adjusting your filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-filament-panels::page>
