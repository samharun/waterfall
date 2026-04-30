@php
    $deliveries     = $this->getUnassignedDeliveries();
    $totalUnassigned = $this->getTotalUnassigned();
@endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:{{ $totalUnassigned > 0 ? '#fee2e2' : '#f0fdf4' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="{{ $totalUnassigned > 0 ? '#dc2626' : '#16a34a' }}" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Unassigned Deliveries</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            @if($totalUnassigned > 0)
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:9999px;background:#fee2e2;color:#b91c1c">
                    {{ $totalUnassigned }} unassigned
                </span>
            @endif
            <a href="/admin/deliveries?tableFilters[delivery_status][value]=pending"
               style="font-size:11px;color:#2563eb;text-decoration:none;font-weight:500">
                View all →
            </a>
        </div>
    </div>

    @if($deliveries->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;color:#9ca3af">
            <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p style="font-size:13px;margin-top:8px;color:#9ca3af">All deliveries are assigned!</p>
        </div>
    @else
        {{-- Alert banner --}}
        <div style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#fef2f2;border-bottom:1px solid #fecaca">
            <svg width="14" height="14" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <span style="font-size:12px;color:#b91c1c;font-weight:500">
                {{ $totalUnassigned }} {{ $totalUnassigned === 1 ? 'delivery needs' : 'deliveries need' }} staff assignment. Assign them to avoid delays.
            </span>
        </div>

        {{-- Delivery rows --}}
        @foreach($deliveries as $i => $delivery)
            <div style="padding:10px 16px;{{ $i > 0 ? 'border-top:1px solid #f9fafb' : '' }}">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">

                    {{-- Delivery info --}}
                    <div style="min-width:0;flex:1">
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                            <span style="font-size:11px;font-family:monospace;color:#6b7280;background:#f3f4f6;padding:1px 6px;border-radius:4px">
                                {{ $delivery['delivery_no'] }}
                            </span>
                            <span style="font-size:10px;padding:1px 6px;border-radius:9999px;background:#fef3c7;color:#92400e">
                                {{ ucfirst($delivery['status']) }}
                            </span>
                            @if($delivery['zone'] !== '—')
                                <span style="font-size:10px;color:#0d9488">📍 {{ $delivery['zone'] }}</span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:10px;margin-top:3px">
                            <span style="font-size:12px;font-weight:600;color:#111827">{{ $delivery['party_name'] }}</span>
                            <span style="font-size:11px;color:#9ca3af">{{ $delivery['party_mobile'] }}</span>
                            <span style="font-size:10px;color:#9ca3af">{{ $delivery['created_at'] }}</span>
                        </div>
                    </div>

                    {{-- Assign button --}}
                    <a href="/admin/deliveries/{{ $delivery['id'] }}/edit"
                       style="flex-shrink:0;font-size:11px;font-weight:600;padding:5px 10px;border-radius:6px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;text-decoration:none;white-space:nowrap">
                        Assign →
                    </a>
                </div>
            </div>
        @endforeach

        @if($totalUnassigned > $deliveries->count())
            <div style="padding:10px 16px;border-top:1px solid #f3f4f6;text-align:center;background:#f9fafb">
                <a href="/admin/deliveries?tableFilters[delivery_status][value]=pending"
                   style="font-size:12px;color:#2563eb;text-decoration:none;font-weight:500">
                    + {{ $totalUnassigned - $deliveries->count() }} more unassigned deliveries →
                </a>
            </div>
        @endif
    @endif
</div>
