@extends('customer.layouts.app')
@section('title', __('customer.my_profile'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ __('customer.my_profile') }}</h1>
    <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline btn-sm">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
        {{ __('customer.edit') }}
    </a>
</div>

@php
    $typeLabels     = __('customer.type_labels');
    $approvalLabels = __('customer.approval_labels');
    $slotLabels     = __('customer.slot_labels');
    $displayName    = Bn::localized($customer->name_bn, $customer->name);
    $displayAddress = Bn::localized($customer->address_bn, $customer->address);
@endphp

{{-- Avatar + name header --}}
<div style="background:linear-gradient(135deg,#0077B6,#00B4D8);border-radius:16px;padding:20px;margin-bottom:14px;display:flex;align-items:center;gap:14px">
    <div style="width:52px;height:52px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;color:#fff;flex-shrink:0">
        {{ strtoupper(substr($customer->name, 0, 1)) }}
    </div>
    <div>
        <div style="font-size:17px;font-weight:800;color:#fff">{{ $displayName }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.75);font-family:monospace;margin-top:2px">{{ $customer->customer_id }}</div>
        <div style="margin-top:5px">
            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;font-size:11px">
                {{ $approvalLabels[$customer->approval_status] ?? ucfirst($customer->approval_status) }}
            </span>
        </div>
    </div>
</div>

{{-- Account info --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Account Information</div>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.mobile') }}</span>
        <span class="list-value">{{ Bn::n($customer->mobile) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.email') }}</span>
        <span class="list-value">{{ $customer->email ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.customer_type') }}</span>
        <span class="list-value">{{ $typeLabels[$customer->customer_type] ?? ucfirst($customer->customer_type) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.zone') }}</span>
        <span class="list-value">{{ $customer->zone?->name ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.default_slot') }}</span>
        <span class="list-value">{{ $slotLabels[$customer->default_delivery_slot ?? ''] ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.address') }}</span>
        <span class="list-value" style="max-width:60%;text-align:right">{{ $displayAddress ?? '—' }}</span>
    </div>
</div>

{{-- Financial summary --}}
<div class="card">
    <div class="card-header">
        <div class="card-title">Financial Summary</div>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.current_due') }}</span>
        <span class="list-value" style="{{ (float)$customer->current_due > 0 ? 'color:#dc2626' : 'color:#16a34a' }}">
            ৳ {{ Bn::m($customer->current_due) }}
        </span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.jar_deposit_qty') }}</span>
        <span class="list-value">{{ Bn::n($customer->jar_deposit_qty) }} {{ __('customer.jars') }}</span>
    </div>
</div>
@endsection
