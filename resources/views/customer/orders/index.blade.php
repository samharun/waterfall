@extends('customer.layouts.app')
@section('title', __('customer.my_orders'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p class="page-title" style="margin:0;">{{ __('customer.my_orders') }}</p>
    <a href="{{ route('customer.orders.create') }}" class="btn btn-primary btn-sm">{{ __('customer.new_order') }}</a>
</div>

@forelse($orders as $order)
<div class="order-card">
    <div style="flex:1;">
        <div class="order-no">{{ $order->order_no }}</div>
        <div style="font-size:.82rem;color:#64748b;margin:.2rem 0;">
            {{ Bn::d($order->order_date) }} &bull;
            {{ __('customer.slot_labels.'.$order->preferred_delivery_slot) }}
        </div>
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.4rem;">
            <span class="badge badge-{{ $order->order_status }}">{{ __('customer.status_'.$order->order_status) }}</span>
            <span class="badge badge-{{ $order->payment_status }}">{{ __('customer.status_'.$order->payment_status) }}</span>
            @if($order->delivery)
                <span class="badge badge-{{ $order->delivery->delivery_status }}">
                    {{ __('customer.status_'.str_replace('-','_',$order->delivery->delivery_status)) }}
                </span>
            @endif
        </div>
    </div>
    <div style="text-align:right;">
        <div class="order-total">৳{{ Bn::m($order->total_amount) }}</div>
        <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;">{{ __('customer.view_details') }}</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">
    {{ __('customer.no_orders') }}
    <a href="{{ route('customer.orders.create') }}" style="color:#0ea5e9;">{{ __('customer.place_first') }}</a>
</div>
@endforelse

<div class="pagination">{{ $orders->links() }}</div>
@endsection
