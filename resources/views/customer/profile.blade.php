@extends('customer.layouts.app')
@section('title', __('customer.my_profile'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p class="page-title" style="margin:0;">{{ __('customer.my_profile') }}</p>
    <a href="{{ route('customer.profile.edit') }}" class="btn btn-outline btn-sm">{{ __('customer.edit') }}</a>
</div>

<div class="card">
    @php
        $typeLabels     = __('customer.type_labels');
        $approvalLabels = __('customer.approval_labels');
        $slotLabels     = __('customer.slot_labels');
        $displayName    = Bn::localized($customer->name_bn, $customer->name);
        $displayAddress = Bn::localized($customer->address_bn, $customer->address);
    @endphp

    <div class="list-item">
        <span class="list-label">{{ __('customer.customer_id') }}</span>
        <span class="list-value" style="font-family:monospace;font-size:.85rem;">{{ $customer->customer_id }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.name') }}</span>
        <span class="list-value">{{ $displayName }}</span>
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
        <span class="list-label">{{ __('customer.zone') }}</span>
        <span class="list-value">{{ $customer->zone?->name ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.customer_type') }}</span>
        <span class="list-value">{{ $typeLabels[$customer->customer_type] ?? ucfirst($customer->customer_type) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.approval_status') }}</span>
        <span class="list-value">{{ $approvalLabels[$customer->approval_status] ?? ucfirst($customer->approval_status) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.default_slot') }}</span>
        <span class="list-value">{{ $slotLabels[$customer->default_delivery_slot ?? ''] ?? '—' }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.address') }}</span>
        <span class="list-value">{{ $displayAddress }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.current_due') }}</span>
        <span class="list-value">৳ {{ Bn::m($customer->current_due) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.jar_deposit_qty') }}</span>
        <span class="list-value">{{ Bn::n($customer->jar_deposit_qty) }} {{ __('customer.jars') }}</span>
    </div>
</div>
@endsection
