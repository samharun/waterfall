@extends('dealer.layouts.app')
@section('title', 'Order Details')
@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('dealer.orders.index') }}" style="color:#1e3a5f;text-decoration:none;">← Orders</a>
    <p class="page-title" style="margin:0;">Order Details</p>
</div>

<div class="card">
    <div class="card-title" style="font-family:monospace;">{{ $order->order_no }}</div>
    @foreach([
        ['Order Date',    $order->order_date?->format('d M Y')],
        ['Delivery Slot', ucfirst($order->preferred_delivery_slot)],
        ['Delivery Time', $order->preferred_delivery_time?->format('d M Y H:i') ?? '—'],
        ['Zone',          $order->zone?->name ?? '—'],
        ['Remarks',       $order->remarks ?? '—'],
    ] as [$label, $value])
    <div class="list-item"><span class="list-label">{{ $label }}</span><span class="list-value">{{ $value }}</span></div>
    @endforeach
    <div class="list-item"><span class="list-label">Order Status</span><span class="badge badge-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span></div>
    <div class="list-item"><span class="list-label">Payment Status</span><span class="badge badge-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></div>
</div>

<div class="card">
    <div class="card-title">Items</div>
    @foreach($order->items as $item)
    <div style="padding:.6rem 0;border-bottom:1px solid #f1f5f9;">
        <div style="font-weight:600;">{{ $item->product?->name }}</div>
        <div style="font-size:.8rem;color:#64748b;font-family:monospace;">{{ $item->product?->sku }}</div>
        <div style="display:flex;justify-content:space-between;margin-top:.3rem;font-size:.9rem;">
            <span>{{ $item->quantity }} × ৳{{ number_format((float)$item->unit_price, 2) }}</span>
            <span style="font-weight:700;">৳{{ number_format((float)$item->line_total, 2) }}</span>
        </div>
    </div>
    @endforeach
    <div style="margin-top:.75rem;">
        @foreach([['Subtotal','৳'.number_format((float)$order->subtotal,2)],['Discount','৳'.number_format((float)$order->discount,2)],['Delivery Charge','৳'.number_format((float)$order->delivery_charge,2)]] as [$l,$v])
        <div class="list-item"><span class="list-label">{{ $l }}</span><span>{{ $v }}</span></div>
        @endforeach
        <div class="list-item" style="border-top:2px solid #e2e8f0;margin-top:.25rem;padding-top:.75rem;">
            <span style="font-weight:700;">Total Amount</span>
            <span style="font-weight:700;font-size:1.1rem;color:#1e3a5f;">৳{{ number_format((float)$order->total_amount, 2) }}</span>
        </div>
    </div>
</div>

@if($order->delivery)
<div class="card">
    <div class="card-title">Delivery</div>
    <div class="list-item"><span class="list-label">Delivery No</span><span class="list-value" style="font-family:monospace;font-size:.82rem;">{{ $order->delivery->delivery_no }}</span></div>
    <div class="list-item"><span class="list-label">Status</span><span class="badge badge-{{ $order->delivery->delivery_status }}">{{ ucwords(str_replace('_',' ',$order->delivery->delivery_status)) }}</span></div>
    @if($order->delivery->delivery_note)<div class="list-item"><span class="list-label">Note</span><span class="list-value">{{ $order->delivery->delivery_note }}</span></div>@endif
    @if($order->delivery->delivered_at)<div class="list-item"><span class="list-label">Delivered At</span><span class="list-value">{{ $order->delivery->delivered_at->format('d M Y H:i') }}</span></div>@endif
</div>
@endif
@endsection
