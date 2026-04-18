@extends('dealer.layouts.app')
@section('title', 'Dashboard')
@section('content')
<p class="page-title">Welcome, {{ $dealer->name }} 👋</p>
<p style="font-size:.82rem;color:#64748b;margin-bottom:1rem;font-family:monospace;">{{ $dealer->dealer_code }}</p>

<a href="{{ route('dealer.orders.create') }}" class="btn-order">📦 Place Bulk Order</a>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value" style="{{ (float)$dealer->current_due > 0 ? 'color:#ef4444' : 'color:#10b981' }}">
            ৳{{ number_format((float)$dealer->current_due, 2) }}
        </div>
        <div class="stat-label">Current Due</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $jarDepositQty }}</div>
        <div class="stat-label">Jars with You</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $pendingOrders }}</div>
        <div class="stat-label">Active Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:1rem;">৳{{ number_format((float)$monthPayments, 2) }}</div>
        <div class="stat-label">This Month Paid</div>
    </div>
</div>

@if($dealer->zone)
<div class="card" style="padding:.75rem 1rem;">
    <span style="font-size:.82rem;color:#64748b;">Zone: </span>
    <span style="font-weight:600;">{{ $dealer->zone->name }}</span>
</div>
@endif

@if($lastOrder)
<div class="card">
    <div class="card-title">Last Order</div>
    <div class="list-item"><span class="list-label">Order No</span><span class="list-value" style="font-family:monospace;font-size:.82rem;">{{ $lastOrder->order_no }}</span></div>
    <div class="list-item"><span class="list-label">Date</span><span class="list-value">{{ $lastOrder->order_date?->format('d M Y') }}</span></div>
    <div class="list-item"><span class="list-label">Total</span><span class="list-value">৳{{ number_format((float)$lastOrder->total_amount, 2) }}</span></div>
    <div class="list-item"><span class="list-label">Status</span><span class="badge badge-{{ $lastOrder->order_status }}">{{ ucfirst($lastOrder->order_status) }}</span></div>
    @if($lastDelivery)
    <div class="list-item"><span class="list-label">Delivery</span><span class="badge badge-{{ $lastDelivery->delivery_status }}">{{ ucwords(str_replace('_',' ',$lastDelivery->delivery_status)) }}</span></div>
    @endif
    <div style="margin-top:.75rem;"><a href="{{ route('dealer.orders.show', $lastOrder) }}" class="btn btn-outline btn-sm">View Details</a></div>
</div>
@endif

@if($lastInvoice)
<div class="card">
    <div class="card-title">Latest Invoice</div>
    <div class="list-item"><span class="list-label">Invoice No</span><span class="list-value" style="font-family:monospace;font-size:.82rem;">{{ $lastInvoice->invoice_no }}</span></div>
    <div class="list-item"><span class="list-label">Total</span><span class="list-value">৳{{ number_format((float)$lastInvoice->total_amount, 2) }}</span></div>
    <div class="list-item"><span class="list-label">Due</span><span class="list-value" style="{{ (float)$lastInvoice->due_amount > 0 ? 'color:#ef4444' : '' }}">৳{{ number_format((float)$lastInvoice->due_amount, 2) }}</span></div>
    <div class="list-item"><span class="list-label">Status</span><span class="badge badge-{{ $lastInvoice->invoice_status }}">{{ ucfirst($lastInvoice->invoice_status) }}</span></div>
    <div style="margin-top:.75rem;"><a href="{{ route('dealer.invoices.show', $lastInvoice) }}" class="btn btn-outline btn-sm">View Invoice</a></div>
</div>
@endif
@endsection
