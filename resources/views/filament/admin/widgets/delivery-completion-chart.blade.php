@php
    $data    = $this->getChartData();
    $days    = $data['days'];
    $maxVal  = max(1, $days->max('total'));
    $avgPct  = $data['avg_pct'];
    $avgColor = $avgPct >= 80 ? '#16a34a' : ($avgPct >= 50 ? '#f59e0b' : '#dc2626');
@endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#ca8a04" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Delivery Completion — Last 14 Days</span>
        </div>
        <div style="text-align:right">
            <div style="font-size:16px;font-weight:700;color:{{ $avgColor }}">{{ $avgPct }}%</div>
            <div style="font-size:10px;color:#9ca3af">avg completion</div>
        </div>
    </div>

    <div style="padding:16px;display:flex;flex-direction:column;gap:14px">

        {{-- Summary strip --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <div style="padding:10px 12px;border-radius:8px;background:#f9fafb;border:1px solid #e5e7eb;text-align:center">
                <div style="font-size:18px;font-weight:700;color:#374151">{{ number_format($data['total_assigned_period']) }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:1px">Total Assigned</div>
            </div>
            <div style="padding:10px 12px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;text-align:center">
                <div style="font-size:18px;font-weight:700;color:#15803d">{{ number_format($data['total_delivered_period']) }}</div>
                <div style="font-size:11px;color:#16a34a;margin-top:1px">Delivered</div>
            </div>
            <div style="padding:10px 12px;border-radius:8px;background:{{ $avgPct >= 80 ? '#f0fdf4' : ($avgPct >= 50 ? '#fef9c3' : '#fef2f2') }};border:1px solid {{ $avgPct >= 80 ? '#bbf7d0' : ($avgPct >= 50 ? '#fde68a' : '#fecaca') }};text-align:center">
                <div style="font-size:18px;font-weight:700;color:{{ $avgColor }}">{{ $avgPct }}%</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:1px">Avg Rate</div>
            </div>
        </div>

        {{-- Bar chart --}}
        <div>
            <div style="display:flex;align-items:flex-end;gap:3px;height:80px">
                @foreach($days as $day)
                    @php
                        $totalH    = $day['total'] > 0 ? max(4, round(($day['total'] / $maxVal) * 76)) : 0;
                        $doneH     = $day['total'] > 0 ? max(0, round(($day['done']  / $maxVal) * 76)) : 0;
                        $failedH   = $day['total'] > 0 ? max(0, round(($day['failed']/ $maxVal) * 76)) : 0;
                        $pctColor  = $day['pct'] >= 80 ? '#16a34a' : ($day['pct'] >= 50 ? '#f59e0b' : '#dc2626');
                        $todayBg   = $day['is_today'] ? 'background:#eff6ff;border-radius:4px 4px 0 0' : '';
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:0;{{ $todayBg }}">
                        {{-- Stacked bar --}}
                        <div style="width:100%;display:flex;flex-direction:column;justify-content:flex-end;height:76px;gap:0">
                            @if($day['total'] > 0)
                                {{-- Failed portion --}}
                                @if($failedH > 0)
                                    <div style="width:100%;height:{{ $failedH }}px;background:#fca5a5" title="Failed: {{ $day['failed'] }}"></div>
                                @endif
                                {{-- Pending portion (total - done - failed) --}}
                                @php $pendingH = max(0, $totalH - $doneH - $failedH); @endphp
                                @if($pendingH > 0)
                                    <div style="width:100%;height:{{ $pendingH }}px;background:#e5e7eb" title="Pending: {{ $day['total'] - $day['done'] - $day['failed'] }}"></div>
                                @endif
                                {{-- Delivered portion --}}
                                @if($doneH > 0)
                                    <div style="width:100%;height:{{ $doneH }}px;background:{{ $pctColor }};border-radius:2px 2px 0 0" title="Delivered: {{ $day['done'] }}"></div>
                                @endif
                            @else
                                <div style="width:100%;height:4px;background:#f3f4f6;border-radius:2px"></div>
                            @endif
                        </div>
                        {{-- Label --}}
                        <div style="font-size:9px;color:{{ $day['is_today'] ? '#2563eb' : '#9ca3af' }};font-weight:{{ $day['is_today'] ? '700' : '400' }};margin-top:2px;text-align:center">
                            {{ $day['label'] }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Legend --}}
            <div style="display:flex;gap:12px;margin-top:8px;flex-wrap:wrap">
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#6b7280">
                    <span style="width:8px;height:8px;border-radius:2px;background:#16a34a;display:inline-block"></span> Delivered
                </span>
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#6b7280">
                    <span style="width:8px;height:8px;border-radius:2px;background:#e5e7eb;display:inline-block"></span> Pending
                </span>
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#6b7280">
                    <span style="width:8px;height:8px;border-radius:2px;background:#fca5a5;display:inline-block"></span> Failed
                </span>
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#2563eb;font-weight:600">
                    <span style="width:8px;height:8px;border-radius:2px;background:#eff6ff;border:1px solid #bfdbfe;display:inline-block"></span> Today
                </span>
            </div>
        </div>

        {{-- Per-day completion % row --}}
        <div>
            <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Daily Completion Rate</div>
            <div style="display:flex;gap:3px;align-items:center">
                @foreach($days as $day)
                    @php
                        $c = $day['pct'] >= 80 ? '#16a34a' : ($day['pct'] >= 50 ? '#f59e0b' : ($day['total'] > 0 ? '#dc2626' : '#e5e7eb'));
                    @endphp
                    <div style="flex:1;height:6px;border-radius:2px;background:{{ $c }}" title="{{ $day['label'] }}: {{ $day['pct'] }}%"></div>
                @endforeach
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:3px">
                <span style="font-size:9px;color:#9ca3af">{{ $days->first()['label'] }}</span>
                <span style="font-size:9px;color:#2563eb;font-weight:600">Today</span>
            </div>
        </div>
    </div>
</div>
