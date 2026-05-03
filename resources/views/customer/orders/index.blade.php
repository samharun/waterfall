@extends('customer.layouts.app')
@section('title', __('customer.my_orders'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ __('customer.my_orders') }}</h1>
    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        {{ __('customer.new_order') }}
    </a>
</div>

@forelse($orders as $order)
<div class="order-card">
    <div style="flex:1;min-width:0">
        <div class="order-no">{{ $order->order_no }}</div>
        <div style="font-size:12px;color:#64748b;margin:3px 0">
            {{ Bn::d($order->order_date) }} &bull; {{ __('customer.slot_labels.'.$order->preferred_delivery_slot) }}
        </div>
        <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:6px">
            <span class="badge badge-{{ $order->order_status }}">{{ __('customer.status_'.$order->order_status) }}</span>
            <span class="badge badge-{{ $order->payment_status }}">{{ __('customer.status_'.$order->payment_status) }}</span>
            @if($order->delivery)
                <span class="badge badge-{{ $order->delivery->delivery_status }}">
                    {{ __('customer.status_'.str_replace('-','_',$order->delivery->delivery_status)) }}
                </span>
            @endif
        </div>
    </div>
    <div style="text-align:right;flex-shrink:0">
        <div class="order-total">৳{{ Bn::m($order->total_amount) }}</div>
        <div style="font-size:11px;color:#64748b;margin-top:2px">{{ Bn::n($order->totalQuantity()) }} {{ __('customer.jars') }}</div>
        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline btn-sm" style="margin-top:8px">{{ __('customer.view_details') }}</a>
    </div>
</div>
@empty
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">📦</div>
        <div class="empty-state-title">{{ __('customer.no_orders') }}</div>
        <div class="empty-state-text" style="margin-bottom:14px">{{ __('customer.place_first') }}</div>
        <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm">{{ __('customer.new_order') }}</a>
    </div>
</div>
@endforelse

<div class="pagination">{{ $orders->links() }}</div>
@endsection
