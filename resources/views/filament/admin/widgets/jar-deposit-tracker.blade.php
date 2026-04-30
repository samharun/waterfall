@php $data = $this->getData(); @endphp

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.07)">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:28px;height:28px;border-radius:8px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="16" height="16" fill="none" stroke="#0284c7" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
                </svg>
            </div>
            <span style="font-size:14px;font-weight:600;color:#111827">Jar Deposit Tracker</span>
        </div>
        <span style="font-size:11px;color:#9ca3af">{{ now()->format('d M Y') }}</span>
    </div>

    <div style="padding:16px;display:flex;flex-direction:column;gap:14px">

        {{-- Total jars out hero --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <div style="grid-column:span 1;padding:12px;border-radius:10px;background:linear-gradient(135deg,#e0f2fe,#bae6fd);border:1px solid #7dd3fc;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#0c4a6e">{{ number_format($data['total_jars_out']) }}</div>
                <div style="font-size:11px;color:#0369a1;font-weight:500;margin-top:2px">Total Jars Out</div>
            </div>
            <div style="padding:12px;border-radius:10px;background:#f0fdf4;border:1px solid #bbf7d0;text-align:center">
                <div style="font-size:20px;font-weight:700;color:#15803d">{{ number_format($data['customer_jars']) }}</div>
                <div style="font-size:11px;color:#16a34a;margin-top:2px">Customers</div>
            </div>
            <div style="padding:12px;border-radius:10px;background:#faf5ff;border:1px solid #e9d5ff;text-align:center">
                <div style="font-size:20px;font-weight:700;color:#6d28d9">{{ number_format($data['dealer_jars']) }}</div>
                <div style="font-size:11px;color:#7c3aed;margin-top:2px">Dealers</div>
            </div>
        </div>

        {{-- Today's movement --}}
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            <div style="padding:10px 12px;border-radius:8px;background:#fff7ed;border:1px solid #fed7aa;text-align:center">
                <div style="font-size:18px;font-weight:700;color:#c2410c">+{{ $data['issued_today'] }}</div>
                <div style="font-size:11px;color:#9a3412;margin-top:1px">Issued Today</div>
            </div>
            <div style="padding:10px 12px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0;text-align:center">
                <div style="font-size:18px;font-weight:700;color:#15803d">-{{ $data['returned_today'] }}</div>
                <div style="font-size:11px;color:#166534;margin-top:1px">Returned Today</div>
            </div>
            <div style="padding:10px 12px;border-radius:8px;background:{{ $data['net_movement'] > 0 ? '#fff7ed' : ($data['net_movement'] < 0 ? '#f0fdf4' : '#f9fafb') }};border:1px solid {{ $data['net_movement'] > 0 ? '#fed7aa' : ($data['net_movement'] < 0 ? '#bbf7d0' : '#e5e7eb') }};text-align:center">
                <div style="font-size:18px;font-weight:700;color:{{ $data['net_movement'] > 0 ? '#c2410c' : ($data['net_movement'] < 0 ? '#15803d' : '#6b7280') }}">
                    {{ $data['net_movement'] > 0 ? '+' : '' }}{{ $data['net_movement'] }}
                </div>
                <div style="font-size:11px;color:#6b7280;margin-top:1px">Net Movement</div>
            </div>
        </div>

        {{-- Last 7 days mini bar chart --}}
        <div>
            <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Last 7 Days Movement</div>
            <div style="display:flex;align-items:flex-end;gap:4px;height:48px">
                @foreach($data['last_7_days'] as $day)
                    @php
                        $maxVal = max(1, $data['last_7_days']->max(fn($d) => max($d['issued'], $d['returned'])));
                        $issuedH  = max(2, round(($day['issued']  / $maxVal) * 44));
                        $returnedH = max(2, round(($day['returned'] / $maxVal) * 44));
                    @endphp
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:1px">
                        <div style="width:100%;display:flex;align-items:flex-end;gap:1px;height:44px">
                            <div style="flex:1;height:{{ $issuedH }}px;background:#f97316;border-radius:2px 2px 0 0" title="Issued: {{ $day['issued'] }}"></div>
                            <div style="flex:1;height:{{ $returnedH }}px;background:#16a34a;border-radius:2px 2px 0 0" title="Returned: {{ $day['returned'] }}"></div>
                        </div>
                        <div style="font-size:9px;color:#9ca3af">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:12px;margin-top:6px">
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#6b7280">
                    <span style="width:8px;height:8px;border-radius:2px;background:#f97316;display:inline-block"></span> Issued
                </span>
                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;color:#6b7280">
                    <span style="width:8px;height:8px;border-radius:2px;background:#16a34a;display:inline-block"></span> Returned
                </span>
            </div>
        </div>

        {{-- Top customers --}}
        @if($data['top_customers']->isNotEmpty())
            <div>
                <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">Top Jar Holders</div>
                <div style="display:flex;flex-direction:column;gap:4px">
                    @foreach($data['top_customers'] as $i => $customer)
                        @php
                            $maxJars = $data['top_customers']->max('jar_deposit_qty');
                            $pct = $maxJars > 0 ? round(($customer->jar_deposit_qty / $maxJars) * 100) : 0;
                        @endphp
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-size:11px;color:#9ca3af;width:14px;text-align:right;flex-shrink:0">{{ $i + 1 }}</span>
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                                    <span style="font-size:12px;font-weight:500;color:#374151;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $customer->name }}</span>
                                    <span style="font-size:12px;font-weight:700;color:#0284c7;flex-shrink:0;margin-left:8px">{{ $customer->jar_deposit_qty }}</span>
                                </div>
                                <div style="width:100%;height:4px;border-radius:9999px;background:#f3f4f6">
                                    <div style="height:4px;border-radius:9999px;background:#0284c7;width:{{ $pct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
