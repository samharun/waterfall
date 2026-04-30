@php $rows = $this->getStaffRows(); @endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Staff Activity — Today</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">{{ now()->format('d M Y') }}</span>
    </div>

    @if($rows->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 16px;color:#9ca3af">
            <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
            </svg>
            <p style="font-size:13px;margin-top:8px;color:#9ca3af">No staff activity today.</p>
        </div>
    @else
        {{-- Summary strip --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1px solid #f3f4f6">
            <div style="padding:10px 16px;text-align:center;border-right:1px solid #f3f4f6">
                <div style="font-size:20px;font-weight:700;color:#111827">{{ $rows->count() }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:1px">Active Staff</div>
            </div>
            <div style="padding:10px 16px;text-align:center;border-right:1px solid #f3f4f6">
                <div style="font-size:20px;font-weight:700;color:#16a34a">{{ $rows->sum('delivered') }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:1px">Delivered</div>
            </div>
            <div style="padding:10px 16px;text-align:center">
                <div style="font-size:20px;font-weight:700;color:#059669">৳ {{ number_format($rows->sum('collected'), 0) }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:1px">Collected</div>
            </div>
        </div>

        {{-- Staff rows --}}
        @foreach($rows as $i => $row)
            @php
                $barColor = $row['status_color'] === 'success' ? '#16a34a'
                    : ($row['status_color'] === 'warning' ? '#f59e0b' : '#dc2626');
                $borderTop = $i > 0 ? 'border-top:1px solid #f9fafb' : '';
            @endphp
            <div style="padding:12px 16px;{{ $borderTop }}">

                {{-- Name + collected --}}
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#111827">{{ $row['name'] }}</div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $row['mobile'] }}</div>
                    </div>
                    <div style="text-align:right;flex-shrink:0;margin-left:12px">
                        <div style="font-size:13px;font-weight:700;color:#059669">৳ {{ number_format($row['collected'], 0) }}</div>
                        <div style="font-size:11px;color:#9ca3af">collected</div>
                    </div>
                </div>

                {{-- Pills --}}
                <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:8px">
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#f3f4f6;color:#374151">
                        <span style="width:6px;height:6px;border-radius:50%;background:#9ca3af;display:inline-block"></span>
                        {{ $row['assigned'] }} assigned
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#dcfce7;color:#15803d">
                        <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block"></span>
                        {{ $row['delivered'] }} done
                    </span>
                    @if($row['in_progress'] > 0)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#dbeafe;color:#1d4ed8">
                            <span style="width:6px;height:6px;border-radius:50%;background:#3b82f6;display:inline-block"></span>
                            {{ $row['in_progress'] }} active
                        </span>
                    @endif
                    @if($row['failed'] > 0)
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;background:#fee2e2;color:#b91c1c">
                            <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;display:inline-block"></span>
                            {{ $row['failed'] }} failed
                        </span>
                    @endif
                </div>

                {{-- Progress bar --}}
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="flex:1;height:6px;border-radius:9999px;background:#f3f4f6">
                        <div style="height:6px;border-radius:9999px;background:{{ $barColor }};width:{{ $row['completion_pct'] }}%"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;width:30px;text-align:right;color:{{ $barColor }}">{{ $row['completion_pct'] }}%</span>
                </div>
            </div>
        @endforeach
    @endif
</div>
