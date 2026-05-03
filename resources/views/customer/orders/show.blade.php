@extends('customer.layouts.app')
@section('title', __('customer.order_details'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div class="page-header">
    <a href="{{ route('customer.orders.index') }}" style="display:inline-flex;align-items:center;gap:5px;color:#0077B6;text-decoration:none;font-size:13px;font-weight:500">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        {{ __('customer.back_orders') }}
    </a>
    <h1 class="page-title" style="font-family:monospace;font-size:16px">{{ $order->order_no }}</h1>
</div>

{{-- Status badges --}}
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px">
    <span class="badge badge-{{ $order->order_status }}">{{ __('customer.status_'.$order->order_status) }}</span>
    <span class="badge badge-{{ $order->payment_status }}">{{ __('customer.status_'.$order->payment_status) }}</span>
</div>

{{-- Order info --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">{{ __('customer.order_details') }}</div>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.order_date') }}</span>
        <span class="list-value">{{ Bn::d($order->order_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_slot') }}</span>
        <span class="list-value">{{ __('customer.slot_labels.'.$order->preferred_delivery_slot) }}</span>
    </div>
    @if($order->preferred_delivery_time)
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_time') }}</span>
        <span class="list-value">{{ Bn::dt($order->preferred_delivery_time) }}</span>
    </div>
    @endif
    <div class="list-item">
        <span class="list-label">{{ __('customer.zone') }}</span>
        <span class="list-value">{{ $order->zone?->name ?? '—' }}</span>
    </div>
    @if($order->remarks)
    <div class="list-item">
        <span class="list-label">{{ __('customer.remarks') }}</span>
        <span class="list-value">{{ $order->remarks }}</span>
    </div>
    @endif
</div>

{{-- Items --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">{{ __('customer.items') }}</div>
    </div>
    @foreach($order->items as $item)
    <div style="padding:10px 0;border-bottom:1px solid #f8fafc">
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div style="font-weight:600;font-size:13px">{{ $item->product?->name }}</div>
                <div style="font-size:11px;color:#64748b;font-family:monospace;margin-top:2px">{{ $item->product?->sku }}</div>
            </div>
            <div style="text-align:right">
                <div style="font-weight:700;font-size:14px">৳{{ Bn::m($item->line_total) }}</div>
                <div style="font-size:11px;color:#64748b">{{ Bn::n($item->quantity) }} × ৳{{ Bn::m($item->unit_price) }}</div>
            </div>
        </div>
    </div>
    @endforeach

    <div style="margin-top:10px">
        <div class="list-item">
            <span class="list-label">{{ __('customer.subtotal') }}</span>
            <span>৳{{ Bn::m($order->subtotal) }}</span>
        </div>
        @if((float)$order->discount > 0)
        <div class="list-item">
            <span class="list-label">{{ __('customer.discount') }}</span>
            <span style="color:#16a34a">-৳{{ Bn::m($order->discount) }}</span>
        </div>
        @endif
        @if((float)$order->delivery_charge > 0)
        <div class="list-item">
            <span class="list-label">{{ __('customer.delivery_charge') }}</span>
            <span>৳{{ Bn::m($order->delivery_charge) }}</span>
        </div>
        @endif
        <div class="list-item" style="border-top:2px solid #e2e8f0;margin-top:6px;padding-top:10px">
            <span style="font-weight:700;font-size:14px">{{ __('customer.total_amount') }}</span>
            <span style="font-weight:800;font-size:18px;color:#0077B6">৳{{ Bn::m($order->total_amount) }}</span>
        </div>
    </div>
</div>

{{-- Delivery --}}
@if($order->delivery)
<div class="card">
    <div class="card-header">
        <div class="card-title">{{ __('customer.delivery') }}</div>
        <span class="badge badge-{{ $order->delivery->delivery_status }}">
            {{ __('customer.status_'.str_replace('-','_',$order->delivery->delivery_status)) }}
        </span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery_no') }}</span>
        <span class="list-value" style="font-family:monospace;font-size:12px">{{ $order->delivery->delivery_no }}</span>
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
        <span class="list-value" style="color:#16a34a">{{ Bn::dt($order->delivery->delivered_at) }}</span>
    </div>
    @endif
</div>
@endif
@endsection
