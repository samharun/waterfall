<x-filament-panels::page>
@php
    $deliveries   = $this->getDeliveries();
    $total        = $deliveries->count();
    $delivered    = $deliveries->where('delivery_status', 'delivered')->count();
    $pending      = $deliveries->whereIn('delivery_status', ['pending','assigned','in_progress'])->count();
    $failed       = $deliveries->whereIn('delivery_status', ['failed','not_delivered','customer_unavailable'])->count();
    $cancelled    = $deliveries->where('delivery_status', 'cancelled')->count();
    $nonCancelled = $total - $cancelled;
    $successRate  = $nonCancelled > 0 ? round($delivered / $nonCancelled * 100, 1) : 0;

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
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Assigned From</label>
        <input type="date" wire:model="assigned_from" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Assigned Until</label>
        <input type="date" wire:model="assigned_until" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Status</label>
        <select wire:model="delivery_status" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Statuses</option>
            @foreach(\App\Models\Delivery::statusLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Zone</label>
        <select wire:model="zone_id" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Zones</option>
            @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Staff</label>
        <select wire:model="delivery_staff_id" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Staff</option>
            @foreach(\App\Models\User::deliveryStaff()->orderBy('name')->get() as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
    </div>
    <button wire:click="$refresh" style="padding:8px 16px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
        Apply Filter
    </button>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <div style="font-size:28px;font-weight:800;color:#111827">{{ $total }}</div>
        <div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Total</div>
    </div>
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $delivered }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Delivered</div>
    </div>
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $pending }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Active</div>
    </div>
    <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $failed }}</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Failed</div>
    </div>
    <div style="background:linear-gradient(135deg,#0284c7,#0369a1);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:28px;font-weight:800;color:#fff">{{ $successRate }}%</div>
        <div style="font-size:11px;color:rgba(255,255,255,.8);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Success Rate</div>
    </div>
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <span style="font-size:14px;font-weight:600;color:#111827">Delivery Records</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $total }} records</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Delivery No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Order No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Customer / Dealer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Zone</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Staff</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Status</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Assigned</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Delivered</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveries as $d)
                    <tr style="border-bottom:1px solid #f9fafb">
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280;white-space:nowrap">{{ $d->delivery_no }}</td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280;white-space:nowrap">{{ $d->order?->order_no ?? '—' }}</td>
                        <td style="padding:10px 16px;font-weight:600;color:#111827">
                            @if($d->order?->customer) {{ $d->order->customer->name }}
                            @elseif($d->order?->dealer) {{ $d->order->dealer->name }}
                            @else <span style="color:#9ca3af">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;color:#6b7280">{{ $d->zone?->name ?? '—' }}</td>
                        <td style="padding:10px 16px;color:#6b7280">{{ $d->deliveryStaff?->name ?? '—' }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $statusStyle[$d->delivery_status] ?? 'background:#f3f4f6;color:#6b7280' }}">
                                {{ ucwords(str_replace('_', ' ', $d->delivery_status)) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;font-size:11px;color:#6b7280;white-space:nowrap">{{ $d->assigned_at?->format('d M H:i') ?? '—' }}</td>
                        <td style="padding:10px 16px;font-size:11px;white-space:nowrap;{{ $d->delivered_at ? 'color:#15803d;font-weight:600' : 'color:#9ca3af' }}">
                            {{ $d->delivered_at?->format('d M H:i') ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:48px 16px;text-align:center;color:#9ca3af">
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
