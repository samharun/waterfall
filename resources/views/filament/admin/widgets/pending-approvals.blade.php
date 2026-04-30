@php
    $customers    = $this->getPendingCustomers();
    $totalPending = $this->getTotalPending();
@endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Pending Approvals</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            @if($totalPending > 0)
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:9999px;background:#fef3c7;color:#92400e">
                    {{ $totalPending }} pending
                </span>
            @endif
            <a href="/admin/customers?tableFilters[approval_status][value]=pending"
               style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:500">
                View all →
            </a>
        </div>
    </div>

    @if($customers->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;color:#9ca3af">
            <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p style="font-size:13px;margin-top:8px;color:#9ca3af">No pending approvals. All caught up!</p>
        </div>
    @else
        <div style="divide-y:1px solid #f9fafb">
            @foreach($customers as $i => $customer)
                <div style="padding:12px 16px;{{ $i > 0 ? 'border-top:1px solid #f9fafb' : '' }}">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">

                        {{-- Customer info --}}
                        <div style="min-width:0;flex:1">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <span style="font-size:13px;font-weight:600;color:#111827">{{ $customer->name }}</span>
                                <span style="font-size:10px;font-family:monospace;color:#6b7280;background:#f3f4f6;padding:1px 6px;border-radius:4px">
                                    {{ $customer->customer_id }}
                                </span>
                                <span style="font-size:10px;padding:1px 6px;border-radius:9999px;background:#dbeafe;color:#1d4ed8">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;margin-top:4px;flex-wrap:wrap">
                                <span style="font-size:11px;color:#6b7280">{{ $customer->mobile }}</span>
                                @if($customer->zone)
                                    <span style="font-size:11px;color:#0d9488">
                                        📍 {{ $customer->zone->name }}
                                    </span>
                                @else
                                    <span style="font-size:11px;color:#dc2626">⚠ No zone</span>
                                @endif
                                <span style="font-size:11px;color:#9ca3af">
                                    {{ $customer->created_at?->diffForHumans() }}
                                </span>
                            </div>
                            @if(!$customer->address)
                                <div style="font-size:11px;color:#dc2626;margin-top:3px">⚠ No address — cannot approve</div>
                            @endif
                        </div>

                        {{-- Action buttons --}}
                        <div style="display:flex;gap:6px;flex-shrink:0">
                            @if($customer->zone_id && $customer->address)
                                <button
                                    wire:click="approve({{ $customer->id }})"
                                    wire:loading.attr="disabled"
                                    style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:6px;border:none;background:#16a34a;color:#fff;cursor:pointer;white-space:nowrap">
                                    ✓ Approve
                                </button>
                            @else
                                <a href="/admin/customers/{{ $customer->id }}/edit"
                                   style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:6px;border:1px solid #d1d5db;background:#fff;color:#374151;cursor:pointer;text-decoration:none;white-space:nowrap">
                                    Edit
                                </a>
                            @endif
                            <button
                                wire:click="reject({{ $customer->id }})"
                                wire:loading.attr="disabled"
                                wire:confirm="Reject this customer?"
                                style="font-size:11px;font-weight:600;padding:5px 10px;border-radius:6px;border:1px solid #fecaca;background:#fef2f2;color:#b91c1c;cursor:pointer;white-space:nowrap">
                                ✕ Reject
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($totalPending > $customers->count())
                <div style="padding:10px 16px;border-top:1px solid #f3f4f6;text-align:center;background:#f9fafb">
                    <a href="/admin/customers?tableFilters[approval_status][value]=pending"
                       style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:500">
                        + {{ $totalPending - $customers->count() }} more pending customers →
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
