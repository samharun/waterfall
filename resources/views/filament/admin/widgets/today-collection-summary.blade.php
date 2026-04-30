@php
    $data = $this->getSummary();
    $methodColors = [
        'cash'  => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#15803d', 'dot' => '#16a34a'],
        'bkash' => ['bg' => '#fdf2f8', 'border' => '#f9a8d4', 'text' => '#9d174d', 'dot' => '#ec4899'],
        'nagad' => ['bg' => '#fff7ed', 'border' => '#fed7aa', 'text' => '#9a3412', 'dot' => '#f97316'],
        'bank'  => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1d4ed8', 'dot' => '#3b82f6'],
        'card'  => ['bg' => '#f5f3ff', 'border' => '#ddd6fe', 'text' => '#5b21b6', 'dot' => '#7c3aed'],
        'other' => ['bg' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151', 'dot' => '#9ca3af'],
    ];
@endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#d1fae5;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Today's Collection</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">{{ $data['date_label'] }}</span>
    </div>

    <div style="padding:16px;display:flex;flex-direction:column;gap:14px">

        {{-- Total collected hero --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-radius:10px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1px solid #a7f3d0">
            <div>
                <div style="font-size:11px;color:#059669;font-weight:500;margin-bottom:3px">Total Collected Today</div>
                <div style="font-size:28px;font-weight:800;color:#065f46">৳ {{ number_format($data['total_collected'], 2) }}</div>
                <div style="font-size:11px;color:#6b7280;margin-top:2px">{{ $data['total_count'] }} payment{{ $data['total_count'] !== 1 ? 's' : '' }} by delivery staff</div>
            </div>
            @if($data['pending_review'] > 0)
                <div style="text-align:right">
                    <div style="font-size:11px;color:#d97706;font-weight:500">Pending Review</div>
                    <div style="font-size:16px;font-weight:700;color:#92400e">৳ {{ number_format($data['pending_review'], 2) }}</div>
                </div>
            @endif
        </div>

        @if($data['total_collected'] > 0)

            {{-- By payment method --}}
            @if($data['by_method']->isNotEmpty())
                <div>
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">By Payment Method</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @foreach($data['by_method'] as $method)
                            @php
                                $key    = $method['payment_method'] ?? 'other';
                                $colors = $methodColors[$key] ?? $methodColors['other'];
                                $label  = \App\Filament\Admin\Widgets\TodayCollectionSummaryWidget::methodLabel($key);
                            @endphp
                            <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;border:1px solid {{ $colors['border'] }};background:{{ $colors['bg'] }}">
                                <span style="width:7px;height:7px;border-radius:50%;background:{{ $colors['dot'] }};display:inline-block;flex-shrink:0"></span>
                                <span style="font-size:12px;font-weight:600;color:{{ $colors['text'] }}">{{ $label }}</span>
                                <span style="font-size:12px;color:{{ $colors['text'] }};opacity:.8">৳ {{ number_format($method['amount'], 0) }}</span>
                                <span style="font-size:10px;color:{{ $colors['text'] }};opacity:.6">({{ $method['count'] }})</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- By zone --}}
            @if($data['by_zone']->isNotEmpty() && $data['by_zone']->count() > 1)
                <div>
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">By Zone</div>
                    <div style="border:1px solid #f3f4f6;border-radius:8px;overflow:hidden">
                        @foreach($data['by_zone'] as $i => $zone)
                            @php
                                $pct = $data['total_collected'] > 0
                                    ? round(($zone['amount'] / $data['total_collected']) * 100)
                                    : 0;
                            @endphp
                            <div style="padding:8px 12px;{{ $i > 0 ? 'border-top:1px solid #f9fafb' : '' }}">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                                    <span style="font-size:12px;font-weight:500;color:#374151">{{ $zone['zone'] }}</span>
                                    <span style="font-size:12px;font-weight:700;color:#059669">৳ {{ number_format($zone['amount'], 0) }}</span>
                                </div>
                                <div style="width:100%;height:4px;border-radius:9999px;background:#f3f4f6">
                                    <div style="height:4px;border-radius:9999px;background:#10b981;width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        @else
            <div style="text-align:center;padding:16px;color:#9ca3af;font-size:13px">
                No collections recorded yet today.
            </div>
        @endif
    </div>
</div>
