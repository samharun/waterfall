@extends('delivery.layouts.app')
@section('title', "Today's Deliveries")

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p class="page-title" style="margin:0;">📦 Today's Deliveries</p>
    <span style="font-size:.82rem;color:#64748b;">{{ $deliveries->count() }} total</span>
</div>

@php
    $active = $deliveries->whereIn('delivery_status', ['assigned','in_progress','pending']);
    $done   = $deliveries->whereIn('delivery_status', ['delivered','failed','cancelled']);
@endphp

@if($active->isNotEmpty())
<form method="POST" action="{{ route('delivery.deliveries.bulk-mark-delivered') }}" id="bulk-form">
    @csrf

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
        <label class="checkbox-wrap" style="font-size:.85rem;font-weight:600;cursor:pointer;">
            <input type="checkbox" id="select-all" onchange="toggleAll(this)"> Select All
        </label>
        <button type="submit" class="btn btn-success btn-sm" onclick="return confirmBulk()">
            ✓ Mark Selected Delivered
        </button>
    </div>

    @foreach($active as $delivery)
        @php
            $order  = $delivery->order;
            $party  = $order?->customer ?? $order?->dealer;
            $isCustomer = (bool) $order?->customer;
            $items  = $order?->items ?? collect();
            $totalQty = $items->sum('quantity');
        @endphp
        <div class="delivery-card status-{{ $delivery->delivery_status }}">
            <div class="checkbox-wrap" style="margin-bottom:.5rem;">
                <input type="checkbox" name="delivery_ids[]" value="{{ $delivery->id }}" class="delivery-check">
                <span class="del-no">{{ $delivery->delivery_no }}</span>
                <span class="badge badge-{{ $delivery->delivery_status }}" style="margin-left:auto;">
                    {{ ucwords(str_replace('_',' ',$delivery->delivery_status)) }}
                </span>
            </div>

            <div class="party-name">{{ $party?->name ?? '—' }}</div>
            <div class="party-mobile">📞 {{ $party?->mobile ?? '—' }}</div>
            <div class="party-addr">📍 {{ $party?->address ?? '—' }}</div>
            @if($delivery->zone)
                <div style="font-size:.78rem;color:#94a3b8;margin-top:.15rem;">Zone: {{ $delivery->zone->name }}</div>
            @endif

            <div style="display:flex;gap:1rem;margin-top:.5rem;font-size:.82rem;color:#475569;">
                <span>🕐 {{ ucfirst($order?->preferred_delivery_slot ?? '—') }}</span>
                <span>📦 {{ $totalQty }} jar(s)</span>
                <span>৳{{ number_format((float)($order?->total_amount ?? 0), 2) }}</span>
                <span class="badge badge-{{ $order?->payment_status ?? 'unpaid' }}">{{ ucfirst($order?->payment_status ?? 'unpaid') }}</span>
            </div>
            @php $collected = $delivery->payments->sum('amount'); @endphp
            @if($collected > 0)
            <div style="font-size:.78rem;color:#10b981;margin-top:.2rem;font-weight:600;">
                ✓ Collected: ৳{{ number_format((float)$collected, 2) }}
            </div>
            @elseif((float)($order?->customer?->current_due ?? $order?->dealer?->current_due ?? 0) > 0)
            <div style="font-size:.78rem;color:#f87171;margin-top:.2rem;">
                Due: ৳{{ number_format((float)($order?->customer?->current_due ?? $order?->dealer?->current_due ?? 0), 2) }}
            </div>
            @endif

            @if($order?->preferred_delivery_slot === 'custom' && $order?->preferred_delivery_time)
                <div style="font-size:.78rem;color:#0ea5e9;margin-top:.2rem;">
                    ⏰ {{ $order->preferred_delivery_time->format('d M H:i') }}
                </div>
            @endif

            <div class="action-row">
                <a href="{{ route('delivery.deliveries.show', $delivery) }}" class="btn btn-outline btn-sm">View</a>

                @if(in_array($delivery->delivery_status, ['assigned','pending']))
                <form method="POST" action="{{ route('delivery.deliveries.mark-in-progress', $delivery) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm">▶ Start</button>
                </form>
                @endif

                @if(in_array($delivery->delivery_status, ['assigned','in_progress','pending']))
                <form method="POST" action="{{ route('delivery.deliveries.mark-delivered', $delivery) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Mark as delivered?')">✓ Delivered</button>
                </form>

                <button type="button" class="btn btn-danger btn-sm"
                    onclick="showFailForm({{ $delivery->id }})">✗ Failed</button>
                @endif
            </div>

            {{-- Fail form (hidden) --}}
            <div id="fail-form-{{ $delivery->id }}" style="display:none;margin-top:.75rem;">
                <form method="POST" action="{{ route('delivery.deliveries.mark-failed', $delivery) }}">
                    @csrf
                    <div class="form-group" style="margin-bottom:.5rem;">
                        <textarea name="failure_reason" class="form-control" rows="2"
                            placeholder="Reason for failure..." required maxlength="500"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Failed</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="hideFailForm({{ $delivery->id }})">Cancel</button>
                </form>
            </div>
        </div>
    @endforeach
</form>
@endif

@if($done->isNotEmpty())
<p style="font-size:.85rem;font-weight:600;color:#64748b;margin:.75rem 0 .5rem;">COMPLETED TODAY</p>
@foreach($done as $delivery)
    @php
        $order = $delivery->order;
        $party = $order?->customer ?? $order?->dealer;
    @endphp
    <div class="delivery-card status-{{ $delivery->delivery_status }}">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div class="del-no">{{ $delivery->delivery_no }}</div>
                <div class="party-name" style="font-size:.9rem;">{{ $party?->name ?? '—' }}</div>
            </div>
            <span class="badge badge-{{ $delivery->delivery_status }}">
                {{ ucwords(str_replace('_',' ',$delivery->delivery_status)) }}
            </span>
        </div>
        @if($delivery->delivered_at)
            <div style="font-size:.78rem;color:#10b981;margin-top:.3rem;">✓ {{ $delivery->delivered_at->format('H:i') }}</div>
        @endif
        @if($delivery->failure_reason)
            <div style="font-size:.78rem;color:#ef4444;margin-top:.3rem;">{{ $delivery->failure_reason }}</div>
        @endif
    </div>
@endforeach
@endif

@if($deliveries->isEmpty())
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">
    No deliveries assigned for today.
</div>
@endif

<script>
function toggleAll(master) {
    document.querySelectorAll('.delivery-check').forEach(cb => cb.checked = master.checked);
}
function confirmBulk() {
    const checked = document.querySelectorAll('.delivery-check:checked').length;
    if (checked === 0) { alert('Please select at least one delivery.'); return false; }
    return confirm(`Mark ${checked} delivery(s) as delivered?`);
}
function showFailForm(id) {
    document.getElementById('fail-form-' + id).style.display = 'block';
}
function hideFailForm(id) {
    document.getElementById('fail-form-' + id).style.display = 'none';
}
</script>
@endsection
