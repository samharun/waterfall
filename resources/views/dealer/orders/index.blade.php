@extends('dealer.layouts.app')
@section('title', 'My Orders')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p class="page-title" style="margin:0;">My Orders</p>
    <a href="{{ route('dealer.orders.create') }}" class="btn btn-primary btn-sm">+ New Order</a>
</div>

@forelse($orders as $order)
<div class="order-card">
    <div style="flex:1;">
        <div class="order-no">{{ $order->order_no }}</div>
        <div style="font-size:.8rem;color:#64748b;margin:.2rem 0;">{{ $order->order_date?->format('d M Y') }} &bull; {{ ucfirst($order->preferred_delivery_slot) }}</div>
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;">
            <span class="badge badge-{{ $order->order_status }}">{{ ucfirst($order->order_status) }}</span>
            <span class="badge badge-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span>
            @if($order->delivery)
                <span class="badge badge-{{ $order->delivery->delivery_status }}">{{ ucwords(str_replace('_',' ',$order->delivery->delivery_status)) }}</span>
            @endif
        </div>
    </div>
    <div style="text-align:right;">
        <div class="order-total">৳{{ number_format((float)$order->total_amount, 2) }}</div>
        <div style="font-size:.78rem;color:#64748b;margin-top:.15rem;">
            {{ $order->totalQuantity() }} jar(s)
        </div>
        <a href="{{ route('dealer.orders.show', $order) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;">View</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">
    No orders yet. <a href="{{ route('dealer.orders.create') }}" style="color:#1e3a5f;">Place your first order</a>.
</div>
@endforelse
<div class="pagination">{{ $orders->links() }}</div>
@endsection
