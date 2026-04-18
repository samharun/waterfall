@extends('delivery.layouts.app')
@section('title', 'Delivery Detail')

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('delivery.today') }}" style="color:#0ea5e9;text-decoration:none;">← Today</a>
    <p class="page-title" style="margin:0;">Delivery Detail</p>
</div>

@php
    $order      = $delivery->order;
    $customer   = $order?->customer;
    $dealer     = $order?->dealer;
    $party      = $customer ?? $dealer;
    $isCustomer = (bool) $customer;
@endphp

{{-- Status & Actions --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
        <div class="del-no" style="font-size:.9rem;">{{ $delivery->delivery_no }}</div>
        <span class="badge badge-{{ $delivery->delivery_status }}" style="font-size:.82rem;">
            {{ ucwords(str_replace('_',' ',$delivery->delivery_status)) }}
        </span>
    </div>

    @foreach([
        ['Assigned At',  $delivery->assigned_at?->format('d M Y H:i') ?? '—'],
        ['Delivered At', $delivery->delivered_at?->format('d M Y H:i') ?? '—'],
    ] as [$label, $value])
    <div class="list-item">
        <span class="list-label">{{ $label }}</span>
        <span class="list-value">{{ $value }}</span>
    </div>
    @endforeach

    @if($delivery->delivery_note)
    <div class="list-item">
        <span class="list-label">Note</span>
        <span class="list-value">{{ $delivery->delivery_note }}</span>
    </div>
    @endif

    @if($delivery->failure_reason)
    <div class="list-item">
        <span class="list-label">Failure Reason</span>
        <span class="list-value" style="color:#ef4444;">{{ $delivery->failure_reason }}</span>
    </div>
    @endif

    {{-- Action buttons --}}
    @if(in_array($delivery->delivery_status, ['assigned','in_progress','pending']))
    <div class="action-row" style="margin-top:.75rem;">
        @if(in_array($delivery->delivery_status, ['assigned','pending']))
        <form method="POST" action="{{ route('delivery.deliveries.mark-in-progress', $delivery) }}">
            @csrf
            <button type="submit" class="btn btn-warning">▶ Mark In Progress</button>
        </form>
        @endif

        <form method="POST" action="{{ route('delivery.deliveries.mark-delivered', $delivery) }}">
            @csrf
            <button type="submit" class="btn btn-success" onclick="return confirm('Mark as delivered?')">✓ Mark Delivered</button>
        </form>
    </div>

    {{-- Fail form --}}
    <div style="margin-top:.75rem;">
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('fail-section').style.display='block';this.style.display='none'">
            ✗ Mark Failed
        </button>
        <div id="fail-section" style="display:none;margin-top:.75rem;">
            <form method="POST" action="{{ route('delivery.deliveries.mark-failed', $delivery) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Failure Reason *</label>
                    <textarea name="failure_reason" class="form-control" rows="3"
                        placeholder="Describe why delivery failed..." required maxlength="500"></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Confirm Failed</button>
            </form>
        </div>
    </div>
    @endif
</div>

{{-- Customer / Dealer Info --}}
<div class="card">
    <div class="card-title">{{ $isCustomer ? 'Customer' : 'Dealer' }} Information</div>
    <div class="party-name">{{ $party?->name ?? '—' }}</div>
    <div class="party-mobile">📞 {{ $party?->mobile ?? '—' }}</div>
    <div class="party-addr">📍 {{ $party?->address ?? '—' }}</div>
    <div style="margin-top:.5rem;">
        @foreach([
            [$isCustomer ? 'Customer ID' : 'Dealer Code', $isCustomer ? $customer?->customer_id : $dealer?->dealer_code],
            ['Zone',    $delivery->zone?->name ?? ($party?->zone?->name ?? '—')],
            ['Current Due', '৳ '.number_format((float)($party?->current_due ?? 0), 2)],
        ] as [$label, $value])
        <div class="list-item">
            <span class="list-label">{{ $label }}</span>
            <span class="list-value">{{ $value }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- Order Info --}}
<div class="card">
    <div class="card-title">Order: {{ $order?->order_no ?? '—' }}</div>
    @foreach([
        ['Order Date',    $order?->order_date?->format('d M Y')],
        ['Delivery Slot', ucfirst($order?->preferred_delivery_slot ?? '—')],
        ['Delivery Time', $order?->preferred_delivery_time?->format('d M Y H:i') ?? '—'],
        ['Payment',       ucfirst($order?->payment_status ?? '—')],
    ] as [$label, $value])
    <div class="list-item">
        <span class="list-label">{{ $label }}</span>
        <span class="list-value">{{ $value }}</span>
    </div>
    @endforeach
</div>

{{-- Order Items --}}
<div class="card">
    <div class="card-title">Items</div>
    @foreach($order?->items ?? [] as $item)
    <div style="padding:.5rem 0;border-bottom:1px solid #f1f5f9;">
        <div style="font-weight:600;">{{ $item->product?->name }}</div>
        <div style="display:flex;justify-content:space-between;font-size:.85rem;color:#64748b;margin-top:.2rem;">
            <span>{{ $item->quantity }} × ৳{{ number_format((float)$item->unit_price, 2) }}</span>
            <span style="font-weight:700;color:#0f172a;">৳{{ number_format((float)$item->line_total, 2) }}</span>
        </div>
    </div>
    @endforeach
    <div class="list-item" style="border-top:2px solid #e2e8f0;margin-top:.25rem;padding-top:.75rem;">
        <span style="font-weight:700;">Total</span>
        <span style="font-weight:700;font-size:1.05rem;color:#0ea5e9;">৳{{ number_format((float)($order?->total_amount ?? 0), 2) }}</span>
    </div>
</div>

{{-- Payment Collection --}}
@php $deliveryPayments = $delivery->payments; $totalCollected = $delivery->totalCollectedAmount(); @endphp
<div class="card">
    <div class="card-title">💰 Payment Collection</div>
    <div class="list-item">
        <span class="list-label">Order Total</span>
        <span class="list-value">৳{{ number_format((float)($order?->total_amount ?? 0), 2) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">Current Due</span>
        <span class="list-value" style="{{ (float)($party?->current_due ?? 0) > 0 ? 'color:#ef4444;' : '' }}">
            ৳{{ number_format((float)($party?->current_due ?? 0), 2) }}
        </span>
    </div>
    <div class="list-item">
        <span class="list-label">Collected (this delivery)</span>
        <span class="list-value" style="{{ $totalCollected > 0 ? 'color:#10b981;font-weight:700;' : '' }}">
            ৳{{ number_format($totalCollected, 2) }}
        </span>
    </div>

    @if($deliveryPayments->count())
    <div style="margin-top:.75rem;">
        @foreach($deliveryPayments as $pay)
        <div style="background:#f0fdf4;border-radius:8px;padding:.6rem .75rem;margin-bottom:.4rem;font-size:.82rem;">
            <div style="display:flex;justify-content:space-between;">
                <span style="font-family:monospace;color:#64748b;">{{ $pay->payment_no }}</span>
                <span style="font-weight:700;color:#10b981;">৳{{ number_format((float)$pay->amount, 2) }}</span>
            </div>
            <div style="color:#64748b;margin-top:.2rem;">
                {{ ucfirst($pay->payment_method) }}
                @if($pay->reference_no) · {{ $pay->reference_no }} @endif
                · {{ $pay->collected_at?->format('H:i') ?? $pay->created_at->format('H:i') }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if(in_array($delivery->delivery_status, ['assigned','in_progress','pending','delivered']))
    <div class="action-row" style="margin-top:.75rem;">
        <a href="{{ route('delivery.deliveries.collect-payment', $delivery) }}" class="btn btn-primary">
            💰 Collect Payment
        </a>
    </div>
    @endif

    {{-- Delivered with Payment --}}
    @if(in_array($delivery->delivery_status, ['assigned','in_progress','pending']))
    <div style="margin-top:.75rem;border-top:1px solid #f1f5f9;padding-top:.75rem;">
        <button type="button" class="btn btn-success btn-sm"
            onclick="document.getElementById('delivered-with-payment').style.display='block';this.style.display='none'">
            ✓ Delivered with Payment
        </button>
        <div id="delivered-with-payment" style="display:none;margin-top:.75rem;">
            <form method="POST" action="{{ route('delivery.deliveries.mark-delivered-with-payment', $delivery) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Amount (৳) *</label>
                    <input type="number" name="amount" class="form-control" min="0.01" step="0.01" required
                        placeholder="Enter collected amount">
                </div>
                <div class="form-group">
                    <label class="form-label">Method *</label>
                    <select name="payment_method" class="form-control" required>
                        @foreach(\App\Models\Payment::methodLabels() as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'cash' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Reference (optional)</label>
                    <input type="text" name="reference_no" class="form-control" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Remarks (optional)</label>
                    <textarea name="remarks" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Mark as delivered and record payment?')">
                    ✓ Confirm Delivered + Payment
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
