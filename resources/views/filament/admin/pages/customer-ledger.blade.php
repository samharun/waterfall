<x-filament-panels::page>
@php
    $customer    = $this->getCustomer();
    $entries     = $this->getLedgerEntries();
    $totalDebit  = $entries->sum('debit');
    $totalCredit = $entries->sum('credit');
    $netBalance  = $totalDebit - $totalCredit;
    $balance     = 0.0;
@endphp

{{-- Customer Selector --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:2;min-width:250px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Customer *</label>
        <select wire:model="customer_id" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">— Select a Customer —</option>
            @foreach(\App\Models\Customer::orderBy('name')->get() as $c)
                <option value="{{ $c->id }}">{{ $c->customer_id }} — {{ $c->name }} ({{ $c->mobile }})</option>
            @endforeach
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">From Date</label>
        <input type="date" wire:model="date_from" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Until Date</label>
        <input type="date" wire:model="date_until" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <button wire:click="$refresh" style="padding:8px 16px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
        Load Ledger
    </button>
</div>

@if($customer)

{{-- Customer Info Card --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:flex-start;gap:14px">
        <div style="width:44px;height:44px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;color:#1d4ed8;flex-shrink:0">
            {{ strtoupper(substr($customer->name, 0, 2)) }}
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:#111827">{{ $customer->name }}</div>
            <div style="font-size:11px;font-family:monospace;color:#6b7280;margin-top:2px">{{ $customer->customer_id }}</div>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px;font-size:12px;color:#6b7280">
                <span>📞 {{ $customer->mobile }}</span>
                @if($customer->zone) <span>📍 {{ $customer->zone->name }}</span> @endif
                <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;text-transform:capitalize;{{ $customer->approval_status === 'approved' ? 'background:#dcfce7;color:#15803d' : 'background:#f3f4f6;color:#6b7280' }}">
                    {{ $customer->approval_status }}
                </span>
            </div>
        </div>
    </div>
    <div style="text-align:right">
        <div style="font-size:11px;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Current Due</div>
        <div style="font-size:24px;font-weight:800;{{ (float)$customer->current_due > 0 ? 'color:#b91c1c' : 'color:#15803d' }}">
            ৳ {{ number_format((float)$customer->current_due, 2) }}
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#dc2626,#b91c1c);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Total Debit</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($totalDebit, 2) }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Total Credit</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($totalCredit, 2) }}</div>
    </div>
    <div style="background:{{ $netBalance > 0 ? 'linear-gradient(135deg,#f59e0b,#d97706)' : 'linear-gradient(135deg,#374151,#1f2937)' }};border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Net Balance</div>
        <div style="font-size:20px;font-weight:800;color:#fff">৳ {{ number_format($netBalance, 2) }}</div>
    </div>
</div>

{{-- Ledger Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <span style="font-size:14px;font-weight:600;color:#111827">Ledger Entries</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $entries->count() }} entries</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Date</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Reference</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Description</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#b91c1c;text-transform:uppercase;letter-spacing:.05em">Debit (৳)</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:.05em">Credit (৳)</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Balance (৳)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    @php $balance += $entry['debit'] - $entry['credit']; @endphp
                    <tr style="border-bottom:1px solid #f9fafb">
                        <td style="padding:10px 16px;color:#6b7280;font-size:12px;white-space:nowrap">
                            {{ \Carbon\Carbon::parse($entry['date'])->format('d M Y') }}
                        </td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280">{{ $entry['reference'] }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $entry['type'] === 'invoice' ? 'background:#fee2e2;color:#b91c1c' : 'background:#dcfce7;color:#15803d' }}">
                                {{ $entry['description'] }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:right">
                            @if($entry['debit'] > 0)
                                <span style="font-weight:600;color:#b91c1c">{{ number_format($entry['debit'], 2) }}</span>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;text-align:right">
                            @if($entry['credit'] > 0)
                                <span style="font-weight:600;color:#15803d">{{ number_format($entry['credit'], 2) }}</span>
                            @else
                                <span style="color:#d1d5db">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;{{ $balance > 0 ? 'color:#b91c1c' : 'color:#15803d' }}">
                            {{ number_format($balance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p style="font-size:13px;font-weight:500">No ledger entries found</p>
                            <p style="font-size:12px;margin-top:4px">Try adjusting the date range</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($entries->count() > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td colspan="3" style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151">Totals</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#b91c1c">{{ number_format($totalDebit, 2) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#15803d">{{ number_format($totalCredit, 2) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;{{ $netBalance > 0 ? 'color:#b91c1c' : 'color:#15803d' }}">{{ number_format($netBalance, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@else
<div style="border:2px dashed #e5e7eb;border-radius:12px;padding:48px 16px;text-align:center">
    <svg style="width:48px;height:48px;margin:0 auto 16px;color:#d1d5db;opacity:.6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
    </svg>
    <p style="font-size:14px;font-weight:600;color:#6b7280">Select a customer above to view their ledger</p>
    <p style="font-size:12px;color:#9ca3af;margin-top:6px">All invoices and payments will appear here</p>
</div>
@endif
</x-filament-panels::page>
