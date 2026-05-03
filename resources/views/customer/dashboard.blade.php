@extends('customer.layouts.app')
@section('title', __('customer.nav_home'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')

{{-- Welcome header --}}
<div style="background:linear-gradient(135deg,#0077B6,#00B4D8);border-radius:16px;padding:20px;margin-bottom:16px;color:#fff">
    <div style="font-size:18px;font-weight:800;letter-spacing:-.02em">
        {{ __('customer.welcome', ['name' => \App\Helpers\BnHelper::localized($customer->name_bn, $customer->name)]) }}
    </div>
    <div style="font-size:12px;opacity:.8;margin-top:3px;font-family:monospace">{{ $customer->customer_id }}</div>
    @if($customer->zone)
        <div style="font-size:12px;opacity:.75;margin-top:2px">📍 {{ $customer->zone->name }}</div>
    @endif
</div>

{{-- Order CTA --}}
<a href="{{ route('customer.orders.create') }}" class="btn-order">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
    </svg>
    {{ __('customer.order_water') }}
</a>

{{-- Stats --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value" style="{{ (float)$customer->current_due > 0 ? 'color:#dc2626' : 'color:#16a34a' }}">
            ৳{{ Bn::m($customer->current_due) }}
        </div>
        <div class="stat-label">{{ __('customer.current_due') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ Bn::n($customer->jar_deposit_qty) }}</div>
        <div class="stat-label">{{ __('customer.jars_with_you') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ Bn::n($pendingOrders) }}</div>
        <div class="stat-label">{{ __('customer.active_orders') }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="font-size:16px;padding-top:4px">{{ $customer->zone?->name ?? '—' }}</div>
        <div class="stat-label">{{ __('customer.your_zone') }}</div>
    </div>
</div>

{{-- Last Order --}}
@if($lastOrder)
<div class="section-label">{{ __('customer.last_order') }}</div>
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title" style="font-family:monospace">{{ $lastOrder->order_no }}</div>
            <div class="card-subtitle">{{ Bn::d($lastOrder->order_date) }}</div>
        </div>
        <div style="text-align:right">
            <div style="font-size:20px;font-weight:800;color:#0077B6">৳{{ Bn::m($lastOrder->total_amount) }}</div>
            <div style="font-size:11px;color:#64748b">{{ Bn::n($lastOrder->totalQuantity()) }} {{ __('customer.jars') }}</div>
        </div>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
        <span class="badge badge-{{ $lastOrder->order_status }}">{{ __('customer.status_'.$lastOrder->order_status) }}</span>
        <span class="badge badge-{{ $lastOrder->payment_status }}">{{ __('customer.status_'.$lastOrder->payment_status) }}</span>
        @if($lastDelivery)
            <span class="badge badge-{{ $lastDelivery->delivery_status }}">{{ __('customer.status_'.str_replace('-','_',$lastDelivery->delivery_status)) }}</span>
        @endif
    </div>
    <a href="{{ route('customer.orders.show', $lastOrder) }}" class="btn btn-outline btn-sm">{{ __('customer.view_details') }}</a>
</div>
@endif

{{-- Subscription --}}
@if($subscription)
<div class="section-label">{{ __('customer.recurring') }}</div>
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">{{ $subscription->product?->name }}</div>
            <div class="card-subtitle">{{ __('customer.freq_'.str_replace('-','_',$subscription->frequency)) }}</div>
        </div>
        <span class="badge badge-{{ $subscription->status }}">{{ __('customer.status_'.$subscription->status) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.next_delivery') }}</span>
        <span class="list-value">{{ Bn::d($subscription->next_delivery_date) }}</span>
    </div>
    <div style="margin-top:10px">
        <a href="{{ route('customer.subscription.show') }}" class="btn btn-outline btn-sm">{{ __('customer.manage') }}</a>
    </div>
</div>
@else
<div class="section-label">{{ __('customer.recurring') }}</div>
<div class="card" style="text-align:center;padding:20px">
    <div style="font-size:13px;color:#64748b;margin-bottom:10px">{{ __('customer.no_recurring') }}</div>
    <a href="{{ route('customer.subscription.create') }}" class="btn btn-outline btn-sm">{{ __('customer.set_recurring') }}</a>
</div>
@endif

{{-- Latest Invoice --}}
@if($lastInvoice)
<div class="section-label">{{ __('customer.latest_invoice') }}</div>
<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title" style="font-family:monospace">{{ $lastInvoice->invoice_no }}</div>
            <div class="card-subtitle">{{ Bn::d($lastInvoice->invoice_date) }}</div>
        </div>
        <span class="badge badge-{{ $lastInvoice->invoice_status }}">{{ __('customer.status_'.$lastInvoice->invoice_status) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.total') }}</span>
        <span class="list-value">৳{{ Bn::m($lastInvoice->total_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.due') }}</span>
        <span class="list-value" style="{{ (float)$lastInvoice->due_amount > 0 ? 'color:#dc2626' : 'color:#16a34a' }}">৳{{ Bn::m($lastInvoice->due_amount) }}</span>
    </div>
    <div style="margin-top:10px">
        <a href="{{ route('customer.invoices.show', $lastInvoice) }}" class="btn btn-outline btn-sm">{{ __('customer.view_invoice') }}</a>
    </div>
</div>
@endif

{{-- Zone Contact --}}
@php $manager = $customer->zone?->deliveryManager; @endphp
<div class="section-label">{{ __('customer.zone_contact') }}</div>
<div class="card">
    @if($manager)
        <div class="list-item">
            <span class="list-label">{{ __('customer.zone') }}</span>
            <span class="list-value">{{ $customer->zone->name }}</span>
        </div>
        <div class="list-item">
            <span class="list-label">{{ __('customer.line_manager') }}</span>
            <span class="list-value">{{ $manager->name }}</span>
        </div>
        @if($manager->email)
        <div class="list-item">
            <span class="list-label">{{ __('customer.email') }}</span>
            <span class="list-value"><a href="mailto:{{ $manager->email }}" style="color:#0077B6;text-decoration:none">{{ $manager->email }}</a></span>
        </div>
        @endif
    @else
        <div class="empty-state" style="padding:16px">
            <div class="empty-state-text">{{ __('customer.no_manager') }}</div>
        </div>
    @endif
</div>

@endsection
