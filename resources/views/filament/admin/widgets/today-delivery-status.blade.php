@php
    $stats    = $this->getStats();
    $pct      = $stats['completion_pct'];
    $barColor = $pct >= 80 ? '#16a34a' : ($pct >= 40 ? '#f59e0b' : '#dc2626');
    $barBg    = $pct >= 80 ? '#dcfce7' : ($pct >= 40 ? '#fef9c3' : '#fee2e2');
@endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#2563eb" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Today's Delivery Status</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">{{ $stats['date_label'] }}</span>
    </div>

    <div style="padding:16px;display:flex;flex-direction:column;gap:14px">

        {{-- Progress --}}
        <div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span style="font-size:12px;color:#6b7280">Completion Progress</span>
                <span style="font-size:12px;font-weight:700;color:{{ $barColor }}">{{ $pct }}% &nbsp;({{ $stats['delivered'] }} / {{ $stats['total'] }})</span>
            </div>
            <div style="width:100%;height:8px;border-radius:9999px;background:{{ $barBg }}">
                <div style="height:8px;border-radius:9999px;background:{{ $barColor }};width:{{ $pct }}%"></div>
            </div>
        </div>

        {{-- 4 stat cards --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px">

            <div style="border:1px solid #e5e7eb;border-radius:10px;background:#f9fafb;padding:10px 12px">
                <div style="display:flex;align-items:center;gap:5px;margin-bottom:4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#9ca3af;display:inline-block"></span>
                    <span style="font-size:11px;color:#6b7280">Pending</span>
                </div>
                <div style="font-size:24px;font-weight:700;color:#374151">{{ $stats['pending'] }}</div>
            </div>

            <div style="border:1px solid #bfdbfe;border-radius:10px;background:#eff6ff;padding:10px 12px">
                <div style="display:flex;align-items:center;gap:5px;margin-bottom:4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;display:inline-block"></span>
                    <span style="font-size:11px;color:#2563eb">In Progress</span>
                </div>
                <div style="font-size:24px;font-weight:700;color:#1d4ed8">{{ $stats['assigned'] + $stats['in_progress'] }}</div>
            </div>

            <div style="border:1px solid #bbf7d0;border-radius:10px;background:#f0fdf4;padding:10px 12px">
                <div style="display:flex;align-items:center;gap:5px;margin-bottom:4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block"></span>
                    <span style="font-size:11px;color:#16a34a">Delivered</span>
                </div>
                <div style="font-size:24px;font-weight:700;color:#15803d">{{ $stats['delivered'] }}</div>
            </div>

            <div style="border:1px solid #fecaca;border-radius:10px;background:#fef2f2;padding:10px 12px">
                <div style="display:flex;align-items:center;gap:5px;margin-bottom:4px">
                    <span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block"></span>
                    <span style="font-size:11px;color:#dc2626">Failed</span>
                </div>
                <div style="font-size:24px;font-weight:700;color:#b91c1c">{{ $stats['failed'] }}</div>
            </div>
        </div>

        {{-- Bottom row --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap">

            @if($stats['unassigned'] > 0)
                <div style="flex:1;min-width:180px;display:flex;align-items:center;gap:8px;border:1px solid #fcd34d;border-radius:8px;background:#fffbeb;padding:8px 12px">
                    <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <span style="font-size:12px;color:#92400e"><strong>{{ $stats['unassigned'] }}</strong> deliveries have no staff assigned</span>
                </div>
            @else
                <div style="flex:1;min-width:180px;display:flex;align-items:center;gap:8px;border:1px solid #bbf7d0;border-radius:8px;background:#f0fdf4;padding:8px 12px">
                    <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    <span style="font-size:12px;color:#166534">All deliveries are assigned</span>
                </div>
            @endif

            <div style="display:flex;align-items:center;gap:8px;border:1px solid #a7f3d0;border-radius:8px;background:#ecfdf5;padding:8px 12px">
                <svg width="14" height="14" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
                </svg>
                <div>
                    <div style="font-size:11px;color:#059669">Today's Collection</div>
                    <div style="font-size:14px;font-weight:700;color:#065f46">৳ {{ number_format($stats['today_collection'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
