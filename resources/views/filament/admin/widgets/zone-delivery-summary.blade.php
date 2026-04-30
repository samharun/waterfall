@php $rows = $this->getZoneRows(); @endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#ccfbf1;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#0d9488" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Zone-wise Summary — Today</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">{{ now()->format('d M Y') }}</span>
    </div>

    @if($rows->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 16px;color:#9ca3af">
            <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <p style="font-size:13px;margin-top:8px;color:#9ca3af">No zone delivery data for today.</p>
        </div>
    @else
        {{-- Zone rows --}}
        @foreach($rows as $i => $row)
            @php
                $barColor  = $row['status_color'] === 'success' ? '#0d9488'
                    : ($row['status_color'] === 'warning' ? '#f59e0b' : '#dc2626');
                $badgeBg   = $row['status_color'] === 'success' ? 'background:#ccfbf1;color:#0f766e'
                    : ($row['status_color'] === 'warning' ? 'background:#fef9c3;color:#a16207' : 'background:#fee2e2;color:#b91c1c');
                $borderTop = $i > 0 ? 'border-top:1px solid #f9fafb' : '';
            @endphp
            <div style="padding:12px 16px;{{ $borderTop }}">

                {{-- Zone name + badge --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px">
                    <div>
                        <div style="display:flex;align-items:center;gap:6px">
                            <span style="font-size:13px;font-weight:700;color:#111827">{{ $row['zone_name'] }}</span>
                            <span style="font-size:11px;color:#9ca3af;font-family:monospace">{{ $row['zone_code'] }}</span>
                        </div>
                        @if($row['manager'] !== '—')
                            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Manager: {{ $row['manager'] }}</div>
                        @endif
                    </div>
                    <span style="flex-shrink:0;margin-left:12px;font-size:11px;font-weight:700;padding:2px 10px;border-radius:9999px;{{ $badgeBg }}">
                        {{ $row['completion_pct'] }}%
                    </span>
                </div>

                {{-- Progress bar --}}
                <div style="width:100%;height:6px;border-radius:9999px;background:#f3f4f6;margin-bottom:8px">
                    <div style="height:6px;border-radius:9999px;background:{{ $barColor }};width:{{ $row['completion_pct'] }}%"></div>
                </div>

                {{-- Stats inline --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;font-size:12px">
                    <span style="color:#6b7280"><strong style="color:#374151">{{ $row['total'] }}</strong> total</span>
                    <span style="color:#16a34a"><strong>{{ $row['delivered'] }}</strong> done</span>
                    <span style="color:#2563eb"><strong>{{ $row['pending'] }}</strong> pending</span>
                    @if($row['failed'] > 0)
                        <span style="color:#dc2626"><strong>{{ $row['failed'] }}</strong> failed</span>
                    @endif
                    <span style="color:#9ca3af"><strong style="color:#6b7280">{{ $row['active_staff'] }}</strong> staff</span>
                    @if($row['unassigned'] > 0)
                        <span style="display:inline-flex;align-items:center;gap:3px;color:#d97706;font-weight:600">
                            <svg width="11" height="11" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                            </svg>
                            {{ $row['unassigned'] }} unassigned
                        </span>
                    @endif
                    <span style="margin-left:auto;font-weight:700;color:#059669">৳ {{ number_format($row['collection'], 0) }}</span>
                </div>
            </div>
        @endforeach

        {{-- Footer totals --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);border-top:1px solid #f3f4f6;background:#f9fafb">
            <div style="padding:10px 12px;text-align:center;border-right:1px solid #f3f4f6">
                <div style="font-size:14px;font-weight:700;color:#374151">{{ $rows->sum('total') }}</div>
                <div style="font-size:11px;color:#9ca3af">Total</div>
            </div>
            <div style="padding:10px 12px;text-align:center;border-right:1px solid #f3f4f6">
                <div style="font-size:14px;font-weight:700;color:#16a34a">{{ $rows->sum('delivered') }}</div>
                <div style="font-size:11px;color:#9ca3af">Delivered</div>
            </div>
            <div style="padding:10px 12px;text-align:center;border-right:1px solid #f3f4f6">
                <div style="font-size:14px;font-weight:700;color:#f59e0b">{{ $rows->sum('pending') }}</div>
                <div style="font-size:11px;color:#9ca3af">Pending</div>
            </div>
            <div style="padding:10px 12px;text-align:center">
                <div style="font-size:14px;font-weight:700;color:#059669">৳ {{ number_format($rows->sum('collection'), 0) }}</div>
                <div style="font-size:11px;color:#9ca3af">Collected</div>
            </div>
        </div>
    @endif
</div>
