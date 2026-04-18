@extends('customer.layouts.app')
@section('title', __('customer.order_details'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('customer.orders.index') }}" style="color:#0ea5e9;text-decoration:none;">{{ __('customer.back_orders') }}</a>
    <p class="page-title" style="margin:0;">{{ __('customer.order_details') }}</p>
</div>

<div class="card">
    <div class="card-title" style="font-family:monospace;">{{ $order->order_no }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.order_date') }}</span>
        <span class="list-value">{{ Bn::d($order->order_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_slot') }}</span>
        <span class="list-value">{{ __('customer.slot_labels.'.$order->preferred_delivery_slot) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_time') }}</span>
        <span class="list-value">{{ Bn::dt($order->preferred_delivery_time) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.zone') }}</span>
        <span class="list-value">{{ $order->zone?->name ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.remarks') }}</span>
        <span class="list-value">{{ $order->remarks ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.order_status') }}</span>
        <span class="badge badge-{{ $order->order_status }}">{{ __('customer.status_'.$order->order_status) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.payment_status') }}</span>
        <span class="badge badge-{{ $order->payment_status }}">{{ __('customer.status_'.$order->payment_status) }}</span>
    </div>
</div>

<div class="card">
    <div class="card-title">{{ __('customer.items') }}</div>
    @foreach($order->items as $item)
    <div style="padding:.6rem 0;border-bottom:1px solid #f1f5f9;">
        <div style="font-weight:600;">{{ $item->product?->name }}</div>
        <div style="font-size:.82rem;color:#64748b;font-family:monospace;">{{ $item->product?->sku }}</div>
        <div style="display:flex;justify-content:space-between;margin-top:.3rem;font-size:.9rem;">
            <span>{{ Bn::n($item->quantity) }} × ৳{{ Bn::m($item->unit_price) }}</span>
            <span style="font-weight:700;">৳{{ Bn::m($item->line_total) }}</span>
        </div>
    </div>
    @endforeach

    <div style="margin-top:.75rem;">
        <div class="list-item">
            <span class="list-label">{{ __('customer.subtotal') }}</span>
            <span>৳{{ Bn::m($order->subtotal) }}</span>
        </div>
        <div class="list-item">
            <span class="list-label">{{ __('customer.discount') }}</span>
            <span>৳{{ Bn::m($order->discount) }}</span>
        </div>
        <div class="list-item">
            <span class="list-label">{{ __('customer.delivery_charge') }}</span>
            <span>৳{{ Bn::m($order->delivery_charge) }}</span>
        </div>
        <div class="list-item" style="border-top:2px solid #e2e8f0;margin-top:.25rem;padding-top:.75rem;">
            <span style="font-weight:700;">{{ __('customer.total_amount') }}</span>
            <span style="font-weight:700;font-size:1.1rem;color:#0ea5e9;">৳{{ Bn::m($order->total_amount) }}</span>
        </div>
    </div>
</div>

@if($order->delivery)
<div class="card">
    <div class="card-title">{{ __('customer.delivery') }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_no') }}</span>
        <span class="list-value" style="font-family:monospace;font-size:.85rem;">{{ $order->delivery->delivery_no }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.status') }}</span>
        <span class="badge badge-{{ $order->delivery->delivery_status }}">
            {{ __('customer.status_'.str_replace('-','_',$order->delivery->delivery_status)) }}
        </span>
    </div>
    @if($order->delivery->delivery_note)
    <div class="list-item">
        <span class="list-label">{{ __('customer.note') }}</span>
        <span class="list-value">{{ $order->delivery->delivery_note }}</span>
    </div>
    @endif
    @if($order->delivery->delivered_at)
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivered_at') }}</span>
        <span class="list-value">{{ Bn::dt($order->delivery->delivered_at) }}</span>
    </div>
    @endif
</div>
@endif
@endsection
