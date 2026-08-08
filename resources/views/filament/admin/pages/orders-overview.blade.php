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
                    <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap">Confirmation</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $pendingDelivery = $order->deliveries->firstWhere('delivery_status', 'pending');
                        $assignedDelivery = $order->deliveries->first(fn ($delivery) => in_array($delivery->delivery_status, ['assigned', 'in_progress'], true));
                    @endphp
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
                        <td style="padding:10px 16px;text-align:center;white-space:nowrap">
                            @if($order->order_status === 'pending' && auth()->user()?->can('orders.update'))
                                <button
                                    type="button"
                                    wire:click="confirmOrder({{ $order->id }})"
                                    wire:confirm="Are you sure you want to confirm this order? A pending delivery will be created for staff assignment."
                                    wire:loading.attr="disabled"
                                    wire:target="confirmOrder({{ $order->id }})"
                                    style="display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:6px 12px;border:0;border-radius:7px;background:#16a34a;color:#fff;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap">
                                    <span wire:loading.remove wire:target="confirmOrder({{ $order->id }})">Confirm it</span>
                                    <span wire:loading wire:target="confirmOrder({{ $order->id }})">Confirming...</span>
                                </button>
                            @elseif($order->order_status === 'confirmed')
                                @if($pendingDelivery && auth()->user()?->can('deliveries.assign'))
                                    <button
                                        type="button"
                                        wire:click="openAssignment({{ $order->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="openAssignment({{ $order->id }})"
                                        style="display:inline-flex;align-items:center;justify-content:center;min-height:32px;padding:6px 12px;border:1px solid #bfdbfe;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap">
                                        Assign staff
                                    </button>
                                @else
                                    <span style="font-size:11px;font-weight:600;color:#15803d">Confirmed</span>
                                @endif
                            @elseif($assignedDelivery)
                                <span style="display:inline-flex;flex-direction:column;gap:2px;font-size:11px;color:#475467">
                                    <strong style="color:#15803d">Assigned</strong>
                                    <span>{{ $assignedDelivery->deliveryStaff?->name ?? 'Staff selected' }}</span>
                                </span>
                            @else
                                <span style="color:#cbd5e1">—</span>
                            @endif
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
                            <td colspan="11" style="padding:48px 16px;text-align:center;color:#9ca3af">
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
                    <td colspan="4"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@if($isAssignmentOpen)
    @php
        $assignmentStaffGroups = $this->getAssignmentStaffGroups();
        $assignmentZones = $this->getAssignmentZones();
    @endphp

    <div
        role="dialog"
        aria-modal="true"
        aria-labelledby="assignment-dialog-title"
        style="position:fixed;z-index:9999;inset:0;display:flex;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)"
        wire:keydown.escape.window="closeAssignment">
        <div style="width:100%;max-width:560px;max-height:calc(100vh - 32px);overflow-y:auto;border:1px solid #e5e7eb;border-radius:16px;background:#fff;box-shadow:0 24px 64px rgba(15,23,42,.26)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid #eef2f6;background:linear-gradient(135deg,#f8fbff,#eef4ff)">
                <div>
                    <div style="display:inline-flex;align-items:center;gap:6px;margin-bottom:7px;padding:3px 8px;border-radius:9999px;background:#dcfce7;color:#15803d;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em">
                        Order confirmed
                    </div>
                    <h2 id="assignment-dialog-title" style="margin:0;color:#101828;font-size:20px;font-weight:700">Assign delivery staff</h2>
                    <p style="margin:5px 0 0;color:#667085;font-size:12px">{{ $assignmentOrderLabel }}</p>
                </div>
                <button type="button" wire:click="closeAssignment" aria-label="Close assignment dialog" style="display:flex;width:34px;height:34px;align-items:center;justify-content:center;border:1px solid #d0d5dd;border-radius:8px;background:#fff;color:#475467;font-size:20px;cursor:pointer">×</button>
            </div>

            <form wire:submit="assignDeliveryStaff" style="padding:22px">
                <div style="display:grid;gap:18px">
                    <div>
                        <label for="assignment-zone" style="display:block;margin-bottom:6px;color:#344054;font-size:12px;font-weight:700">Customer zone / line</label>
                        <select id="assignment-zone" wire:model.live="assignmentZoneId" style="width:100%;min-height:42px;padding:8px 11px;border:1px solid #d0d5dd;border-radius:9px;background:#fff;color:#101828;font-size:13px;outline:none">
                            @foreach($assignmentZones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                        <p style="margin:6px 0 0;color:#667085;font-size:11px">Default: {{ $assignmentZoneLabel }}. Changing this only changes staff prioritization; the delivery remains in the customer’s zone.</p>
                    </div>

                    <div>
                        <label for="assignment-staff" style="display:block;margin-bottom:6px;color:#344054;font-size:12px;font-weight:700">Delivery staff</label>
                        <select id="assignment-staff" wire:model="assignmentStaffId" required style="width:100%;min-height:42px;padding:8px 11px;border:1px solid #d0d5dd;border-radius:9px;background:#fff;color:#101828;font-size:13px;outline:none">
                            <option value="">Select delivery staff</option>

                            @if($assignmentStaffGroups['zone']->isNotEmpty())
                                <optgroup label="Recommended — selected zone / line">
                                    @foreach($assignmentStaffGroups['zone'] as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} — {{ $staff->mobile ?: $staff->email }}</option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($assignmentStaffGroups['other']->isNotEmpty())
                                <optgroup label="Other delivery staff">
                                    @foreach($assignmentStaffGroups['other'] as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} — {{ $staff->mobile ?: $staff->email }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        @error('assignmentStaffId')
                            <p style="margin:6px 0 0;color:#dc2626;font-size:11px">{{ $message }}</p>
                        @enderror
                        <p style="margin:6px 0 0;color:#667085;font-size:11px">Recommended staff are listed first. Administrators may select any staff member from the second group.</p>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px;padding-top:18px;border-top:1px solid #eef2f6">
                    <button type="button" wire:click="closeAssignment" style="min-height:40px;padding:8px 16px;border:1px solid #d0d5dd;border-radius:8px;background:#fff;color:#344054;font-size:13px;font-weight:700;cursor:pointer">Cancel</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="assignDeliveryStaff" style="display:inline-flex;min-height:40px;align-items:center;justify-content:center;padding:8px 18px;border:0;border-radius:8px;background:#465fff;color:#fff;font-size:13px;font-weight:700;cursor:pointer">
                        <span wire:loading.remove wire:target="assignDeliveryStaff">Assign staff</span>
                        <span wire:loading wire:target="assignDeliveryStaff">Assigning...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
</x-filament-panels::page>
