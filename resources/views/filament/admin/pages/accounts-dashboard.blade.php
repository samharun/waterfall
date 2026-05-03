<x-filament-panels::page>
@php
    $stats = $this->stats();

    $cardConfig = [
        'Opening Balance'              => ['color' => '#0077B6', 'bg' => 'linear-gradient(135deg,#0077B6,#005f92)', 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75', 'white' => true],
        'Current Balance'              => ['color' => '#0284c7', 'bg' => 'linear-gradient(135deg,#0284c7,#0369a1)', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'white' => true],
        'Today Income'                 => ['color' => '#16a34a', 'bg' => 'linear-gradient(135deg,#16a34a,#15803d)', 'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941', 'white' => true],
        'Today Expense'                => ['color' => '#dc2626', 'bg' => 'linear-gradient(135deg,#dc2626,#b91c1c)', 'icon' => 'M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181', 'white' => true],
        'This Month Income'            => ['color' => '#059669', 'bg' => '#fff', 'border' => '#bbf7d0', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'white' => false],
        'This Month Expense'           => ['color' => '#dc2626', 'bg' => '#fff', 'border' => '#fecaca', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'white' => false],
        'Net Profit/Loss'              => ['color' => (float)($stats['Net Profit/Loss'] ?? 0) >= 0 ? '#16a34a' : '#dc2626', 'bg' => '#fff', 'border' => (float)($stats['Net Profit/Loss'] ?? 0) >= 0 ? '#bbf7d0' : '#fecaca', 'icon' => 'M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z', 'white' => false],
        'Total Investor Fund'          => ['color' => '#7c3aed', 'bg' => '#fff', 'border' => '#ddd6fe', 'icon' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z', 'white' => false],
        'Supplier Due'                 => ['color' => '#f59e0b', 'bg' => '#fff', 'border' => '#fde68a', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12', 'white' => false],
        'Staff Salary Paid This Month' => ['color' => '#0891b2', 'bg' => '#fff', 'border' => '#a5f3fc', 'icon' => 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z', 'white' => false],
    ];
@endphp

{{-- Header strip --}}
<div style="background:linear-gradient(135deg,#0077B6,#005f92);border-radius:14px;padding:18px 22px;margin-bottom:22px;display:flex;align-items:center;justify-content:space-between">
    <div>
        <div style="font-size:18px;font-weight:800;color:#fff;letter-spacing:-.02em">Accounts Dashboard</div>
        <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:3px">{{ now()->format('F Y') }} — Financial Overview</div>
    </div>
    <div style="font-size:11px;color:rgba(255,255,255,.6)">Updated {{ now()->format('d M Y, H:i') }}</div>
</div>

{{-- Today's snapshot --}}
<div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Today's Snapshot</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    @foreach(['Opening Balance','Current Balance','Today Income','Today Expense'] as $key)
        @php $cfg = $cardConfig[$key]; $val = $stats[$key] ?? 0; @endphp
        <div style="background:{{ $cfg['bg'] }};border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.1)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <div style="font-size:11px;font-weight:700;color:{{ $cfg['white'] ? 'rgba(255,255,255,.75)' : '#64748b' }};text-transform:uppercase;letter-spacing:.05em">{{ $key }}</div>
                <div style="width:28px;height:28px;border-radius:8px;background:{{ $cfg['white'] ? 'rgba(255,255,255,.2)' : '#f8fafc' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <svg width="14" height="14" fill="none" stroke="{{ $cfg['white'] ? '#fff' : $cfg['color'] }}" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:22px;font-weight:800;color:{{ $cfg['white'] ? '#fff' : $cfg['color'] }};letter-spacing:-.02em">
                ৳ {{ number_format((float)$val, 2) }}
            </div>
        </div>
    @endforeach
</div>

{{-- Monthly performance --}}
<div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Monthly Performance</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    @foreach(['This Month Income','This Month Expense','Net Profit/Loss'] as $key)
        @php $cfg = $cardConfig[$key]; $val = $stats[$key] ?? 0; @endphp
        <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] ?? '#e5e7eb' }};border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">{{ $key }}</div>
                <div style="width:28px;height:28px;border-radius:8px;background:#f8fafc;display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:24px;font-weight:800;color:{{ $cfg['color'] }};letter-spacing:-.02em">
                {{ (float)$val < 0 ? '-' : '' }}৳ {{ number_format(abs((float)$val), 2) }}
            </div>
            @if($key === 'Net Profit/Loss')
                <div style="font-size:11px;margin-top:4px;color:{{ (float)$val >= 0 ? '#16a34a' : '#dc2626' }};font-weight:600">
                    {{ (float)$val >= 0 ? '▲ Profit' : '▼ Loss' }}
                </div>
            @endif
        </div>
    @endforeach
</div>

{{-- Other metrics --}}
<div style="margin-bottom:8px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.06em">Other Metrics</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    @foreach(['Total Investor Fund','Supplier Due','Staff Salary Paid This Month'] as $key)
        @php $cfg = $cardConfig[$key]; $val = $stats[$key] ?? 0; @endphp
        <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] ?? '#e5e7eb' }};border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em">{{ $key }}</div>
                <div style="width:28px;height:28px;border-radius:8px;background:#f8fafc;display:flex;align-items:center;justify-content:center">
                    <svg width="14" height="14" fill="none" stroke="{{ $cfg['color'] }}" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <div style="font-size:22px;font-weight:800;color:{{ $cfg['color'] }};letter-spacing:-.02em">
                ৳ {{ number_format((float)$val, 2) }}
            </div>
        </div>
    @endforeach
</div>

{{-- Quick links --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:12px">Quick Actions</div>
    <div style="display:flex;flex-wrap:wrap;gap:8px">
        @foreach([
            ['Accounts Report', '/admin/accounts-report', '#0077B6'],
            ['Transactions', '/admin/account-transactions', '#059669'],
            ['Payment Accounts', '/admin/payment-accounts', '#7c3aed'],
            ['Suppliers', '/admin/suppliers', '#f59e0b'],
            ['Investors', '/admin/investors', '#0891b2'],
            ['Staff Salaries', '/admin/staff-salary-payments', '#dc2626'],
        ] as [$label, $url, $color])
            <a href="{{ $url }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;background:#f8fafc;border:1px solid #e5e7eb;color:{{ $color }};transition:all .15s"
               onmouseover="this.style.background='#f0f9ff';this.style.borderColor='{{ $color }}'"
               onmouseout="this.style.background='#f8fafc';this.style.borderColor='#e5e7eb'">
                {{ $label }} →
            </a>
        @endforeach
    </div>
</div>

</x-filament-panels::page>
