@php $activities = $this->getActivities(); @endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#fce7f3;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#db2777" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Recent Activity</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">Live feed</span>
    </div>

    @if($activities->isEmpty())
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;color:#9ca3af">
            <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p style="font-size:13px;margin-top:8px">No recent activity.</p>
        </div>
    @else
        <div style="max-height:420px;overflow-y:auto">
            @foreach($activities as $i => $activity)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 16px;{{ $i > 0 ? 'border-top:1px solid #f9fafb' : '' }}">

                    {{-- Dot indicator --}}
                    <div style="flex-shrink:0;margin-top:3px">
                        <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $activity['colors']['dot'] }}"></span>
                    </div>

                    {{-- Content --}}
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px">
                            <div style="min-width:0">
                                <div style="font-size:12px;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $activity['title'] }}
                                </div>
                                <div style="font-size:11px;color:#6b7280;margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    {{ $activity['subtitle'] }}
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0">
                                <div style="font-size:10px;font-family:monospace;color:#9ca3af;background:#f3f4f6;padding:1px 5px;border-radius:3px;white-space:nowrap">
                                    {{ $activity['meta'] }}
                                </div>
                                <div style="font-size:10px;color:#9ca3af;margin-top:2px;white-space:nowrap">
                                    {{ $activity['time']?->diffForHumans() ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
