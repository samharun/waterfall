<x-filament-panels::page>
@php
    $rows        = $this->getRows();
    $customerDue = $rows->where('party_type', 'Customer')->sum('current_due');
    $dealerDue   = $rows->where('party_type', 'Dealer')->sum('current_due');
    $grandTotal  = $customerDue + $dealerDue;
    $withDue     = $rows->where('current_due', '>', 0)->count();
@endphp

{{-- Filter Bar --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Party Type</label>
        <select wire:model.live="party_type" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="all">All Parties</option>
            <option value="customer">Customers Only</option>
            <option value="dealer">Dealers Only</option>
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Zone</label>
        <select wire:model.live="zone_id" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Zones</option>
            @foreach(\App\Models\Zone::orderBy('name')->get() as $z)
                <option value="{{ $z->id }}">{{ $z->name }}</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Status</label>
        <select wire:model.live="approval_status" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Statuses</option>
            <option value="approved">Approved</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
    <div style="display:flex;align-items:center;gap:8px;padding-bottom:2px">
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;cursor:pointer">
            <input type="checkbox" wire:model.live="include_zero_due" style="width:14px;height:14px;cursor:pointer">
            Include zero due
        </label>
    </div>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Customer Due</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($customerDue, 2) }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Dealer Due</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($dealerDue, 2) }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#374151,#1f2937);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Grand Total Due</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($grandTotal, 2) }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <div style="font-size:28px;font-weight:800;color:#111827">{{ $withDue }}</div>
        <div style="font-size:11px;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-top:4px">Parties with Due</div>
    </div>
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <span style="font-size:14px;font-weight:600;color:#111827">Outstanding Dues</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $rows->count() }} records</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Type</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">ID / Code</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Name</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Mobile</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Zone</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Current Due (৳)</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr style="border-bottom:1px solid #f9fafb;{{ $row['current_due'] <= 0 ? 'opacity:.6' : '' }}">
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $row['party_type'] === 'Customer' ? 'background:#dbeafe;color:#1d4ed8' : 'background:#fef9c3;color:#92400e' }}">
                                {{ $row['party_type'] }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280">{{ $row['code'] }}</td>
                        <td style="padding:10px 16px;font-weight:600;color:#111827">{{ $row['name'] }}</td>
                        <td style="padding:10px 16px;color:#6b7280">{{ $row['mobile'] }}</td>
                        <td style="padding:10px 16px;color:#6b7280">{{ $row['zone'] }}</td>
                        <td style="padding:10px 16px;text-align:right">
                            @if($row['current_due'] > 0)
                                <span style="font-weight:700;font-size:14px;color:#b91c1c">{{ number_format($row['current_due'], 2) }}</span>
                            @else
                                <span style="font-weight:600;color:#15803d">0.00</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;text-transform:capitalize;{{ $row['approval_status'] === 'approved' ? 'background:#dcfce7;color:#15803d' : 'background:#f3f4f6;color:#6b7280' }}">
                                {{ $row['approval_status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p style="font-size:13px;font-weight:500">No outstanding dues</p>
                            <p style="font-size:12px;margin-top:4px">All parties are clear!</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->count() > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td colspan="5" style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151">Grand Total</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#b91c1c;font-size:15px">৳ {{ number_format($grandTotal, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</x-filament-panels::page>
