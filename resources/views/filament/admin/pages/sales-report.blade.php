<x-filament-panels::page>
@php
    $payments = $this->getPayments();
    $total    = $this->getTotalAmount();
    $count    = $this->getPaymentCount();
    $methodBreakdown = $payments->groupBy('payment_method')
        ->map(fn ($g) => $g->sum('amount'))
        ->sortByDesc(fn ($v) => $v);
    $methodColors = ['cash'=>'#16a34a','bkash'=>'#db2777','nagad'=>'#f97316','bank'=>'#2563eb','card'=>'#7c3aed','other'=>'#6b7280'];
@endphp

{{-- Filter Bar --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:20px;display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px">
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">From</label>
        <input type="date" wire:model="date_from" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Until</label>
        <input type="date" wire:model="date_until" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box">
    </div>
    <div style="flex:1;min-width:120px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Type</label>
        <select wire:model="payment_type" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Types</option>
            <option value="customer">Customer</option>
            <option value="dealer">Dealer</option>
        </select>
    </div>
    <div style="flex:1;min-width:130px">
        <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em">Method</label>
        <select wire:model="payment_method" style="width:100%;padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;color:#111827;background:#f9fafb;outline:none">
            <option value="">All Methods</option>
            @foreach(\App\Models\Payment::methodLabels() as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <button wire:click="$refresh" style="padding:8px 16px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
        Apply Filter
    </button>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="background:linear-gradient(135deg,#16a34a,#15803d);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Total Revenue</div>
        <div style="font-size:22px;font-weight:800;color:#fff">৳ {{ number_format($total, 2) }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#0077B6,#0369a1);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Transactions</div>
        <div style="font-size:22px;font-weight:800;color:#fff">{{ $count }}</div>
    </div>
    <div style="background:linear-gradient(135deg,#0284c7,#0369a1);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)">
        <div style="font-size:11px;font-weight:600;color:rgba(255,255,255,.8);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">Average</div>
        <div style="font-size:22px;font-weight:800;color:#fff">{{ $count > 0 ? '৳ '.number_format($total/$count,2) : '—' }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px">By Method</div>
        @foreach($methodBreakdown->take(4) as $method => $amt)
            @php $color = $methodColors[$method] ?? '#6b7280'; @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
                <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;color:#374151">
                    <span style="width:7px;height:7px;border-radius:50%;background:{{ $color }};display:inline-block"></span>
                    {{ ucfirst($method) }}
                </span>
                <span style="font-size:11px;font-weight:700;color:#111827">৳ {{ number_format($amt, 0) }}</span>
            </div>
        @endforeach
    </div>
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <span style="font-size:14px;font-weight:600;color:#111827">Payment Records</span>
        <span style="font-size:11px;font-weight:600;padding:3px 10px;border-radius:9999px;background:#f3f4f6;color:#6b7280">{{ $count }} records</span>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;font-size:13px">
            <thead>
                <tr style="background:#f9fafb;border-bottom:1px solid #e5e7eb">
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap">Payment No</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Date</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Type</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Customer / Dealer</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Invoice</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Method</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Amount (৳)</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em">Received By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr style="border-bottom:1px solid #f9fafb">
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280;white-space:nowrap">{{ $p->payment_no }}</td>
                        <td style="padding:10px 16px;color:#374151;white-space:nowrap">{{ $p->payment_date?->format('d M Y') }}</td>
                        <td style="padding:10px 16px">
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;{{ $p->payment_type === 'customer' ? 'background:#dbeafe;color:#1d4ed8' : 'background:#fef9c3;color:#92400e' }}">
                                {{ ucfirst($p->payment_type) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;font-weight:600;color:#111827">
                            @if($p->customer) {{ $p->customer->customer_id }} — {{ $p->customer->name }}
                            @elseif($p->dealer) {{ $p->dealer->dealer_code }} — {{ $p->dealer->name }}
                            @else <span style="color:#9ca3af">—</span>
                            @endif
                        </td>
                        <td style="padding:10px 16px;font-family:monospace;font-size:11px;color:#6b7280">{{ $p->invoice?->invoice_no ?? '—' }}</td>
                        <td style="padding:10px 16px">
                            @php $mc = $methodColors[$p->payment_method] ?? '#6b7280'; @endphp
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;background:#f3f4f6;color:#374151">
                                <span style="width:6px;height:6px;border-radius:50%;background:{{ $mc }};display:inline-block"></span>
                                {{ ucfirst($p->payment_method) }}
                            </span>
                        </td>
                        <td style="padding:10px 16px;text-align:right;font-weight:700;color:#15803d;font-size:14px">
                            {{ number_format((float)$p->amount, 2) }}
                        </td>
                        <td style="padding:10px 16px;color:#6b7280">{{ $p->receivedBy?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:48px 16px;text-align:center;color:#9ca3af">
                            <svg style="width:40px;height:40px;margin:0 auto 12px;opacity:.4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p style="font-size:13px;font-weight:500">No payments found</p>
                            <p style="font-size:12px;margin-top:4px">Try adjusting your filters</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($count > 0)
            <tfoot>
                <tr style="background:#f9fafb;border-top:2px solid #e5e7eb">
                    <td colspan="6" style="padding:12px 16px;text-align:right;font-size:13px;font-weight:700;color:#374151">Total</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:800;color:#15803d;font-size:15px">৳ {{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
</x-filament-panels::page>
