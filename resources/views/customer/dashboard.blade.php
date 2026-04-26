@extends('customer.layouts.app')
@section('title', __('customer.nav_home'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<p class="page-title">{{ __('customer.welcome', ['name' => \App\Helpers\BnHelper::localized($customer->name_bn, $customer->name)]) }}</p>
<p style="font-size:.82rem;color:#64748b;margin-bottom:1rem;font-family:monospace;">{{ $customer->customer_id }}</p>

<a href="{{ route('customer.orders.create') }}" class="btn-order">{{ __('customer.order_water') }}</a>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value" style="{{ (float)$customer->current_due > 0 ? 'color:#ef4444' : 'color:#10b981' }}">
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
        <div class="stat-value" style="font-size:1rem;">{{ $customer->zone?->name ?? '—' }}</div>
        <div class="stat-label">{{ __('customer.your_zone') }}</div>
    </div>
</div>

@if($lastOrder)
<div class="card">
    <div class="card-title">{{ __('customer.last_order') }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.order_no') }}</span>
        <span class="list-value" style="font-family:monospace;font-size:.85rem;">{{ $lastOrder->order_no }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.date') }}</span>
        <span class="list-value">{{ Bn::d($lastOrder->order_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.total') }}</span>
        <span class="list-value">৳{{ Bn::m($lastOrder->total_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.jars') }}</span>
        <span class="list-value">{{ Bn::n($lastOrder->totalQuantity()) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.status') }}</span>
        <span class="badge badge-{{ $lastOrder->order_status }}">{{ __('customer.status_'.$lastOrder->order_status) }}</span>
    </div>
    @if($lastDelivery)
    <div class="list-item">
        <span class="list-label">{{ __('customer.delivery') }}</span>
        <span class="badge badge-{{ $lastDelivery->delivery_status }}">{{ __('customer.status_'.str_replace('-','_',$lastDelivery->delivery_status)) }}</span>
    </div>
    @endif
    <div style="margin-top:.75rem;">
        <a href="{{ route('customer.orders.show', $lastOrder) }}" class="btn btn-outline btn-sm">{{ __('customer.view_details') }}</a>
    </div>
</div>
@endif

@if($subscription)
<div class="card">
    <div class="card-title">{{ __('customer.recurring') }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.product') }}</span>
        <span class="list-value">{{ $subscription->product?->name }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.frequency') }}</span>
        <span class="list-value">{{ __('customer.freq_'.str_replace('-','_',$subscription->frequency)) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.next_delivery') }}</span>
        <span class="list-value">{{ Bn::d($subscription->next_delivery_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.status') }}</span>
        <span class="badge badge-{{ $subscription->status }}">{{ __('customer.status_'.$subscription->status) }}</span>
    </div>
    <div style="margin-top:.75rem;">
        <a href="{{ route('customer.subscription.show') }}" class="btn btn-outline btn-sm">{{ __('customer.manage') }}</a>
    </div>
</div>
@else
<div class="card" style="text-align:center;padding:1.25rem;">
    <div style="font-size:.88rem;color:#64748b;margin-bottom:.75rem;">{{ __('customer.no_recurring') }}</div>
    <a href="{{ route('customer.subscription.create') }}" class="btn btn-outline btn-sm">{{ __('customer.set_recurring') }}</a>
</div>
@endif

@if($lastInvoice)
<div class="card">
    <div class="card-title">{{ __('customer.latest_invoice') }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.order_no') }}</span>
        <span class="list-value" style="font-family:monospace;font-size:.85rem;">{{ $lastInvoice->invoice_no }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.total') }}</span>
        <span class="list-value">৳{{ Bn::m($lastInvoice->total_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.due') }}</span>
        <span class="list-value" style="{{ (float)$lastInvoice->due_amount > 0 ? 'color:#ef4444' : '' }}">৳{{ Bn::m($lastInvoice->due_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.status') }}</span>
        <span class="badge badge-{{ $lastInvoice->invoice_status }}">{{ __('customer.status_'.$lastInvoice->invoice_status) }}</span>
    </div>
    <div style="margin-top:.75rem;">
        <a href="{{ route('customer.invoices.show', $lastInvoice) }}" class="btn btn-outline btn-sm">{{ __('customer.view_invoice') }}</a>
    </div>
</div>
@endif

{{-- Zone Line Manager Contact --}}
@php $manager = $customer->zone?->deliveryManager; @endphp
<div class="card" style="margin-top:1rem;">
    <div class="card-title">{{ __('customer.zone_contact') }}</div>
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
        <span class="list-value">
            <a href="mailto:{{ $manager->email }}" style="color:#0d6efd;text-decoration:none;">{{ $manager->email }}</a>
        </span>
    </div>
    @endif
    @else
    <div style="font-size:.85rem;color:#94a3b8;text-align:center;padding:.5rem 0;">
        {{ __('customer.no_manager') }}
    </div>
    @endif
</div>
@endsection
