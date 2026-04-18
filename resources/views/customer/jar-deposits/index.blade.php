@extends('customer.layouts.app')
@section('title', __('customer.jar_deposits'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<p class="page-title">{{ __('customer.jar_deposits') }}</p>

<div class="stat-grid" style="grid-template-columns:1fr;">
    <div class="stat-card">
        <div class="stat-value">{{ Bn::n(auth()->user()->customer->jar_deposit_qty) }}</div>
        <div class="stat-label">{{ __('customer.jars_with_you_label') }}</div>
    </div>
</div>

@forelse($deposits as $dep)
<div class="card" style="padding:.9rem 1rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-family:monospace;font-size:.85rem;font-weight:600;">{{ $dep->deposit_no }}</div>
            <div style="font-size:.8rem;color:#64748b;margin-top:.2rem;">
                {{ Bn::d($dep->transaction_date) }}
            </div>
            <div style="font-size:.82rem;margin-top:.2rem;">{{ $dep->product?->name }}</div>
            @php
                $typeColors = ['deposit_received'=>'badge-confirmed','jar_issued'=>'badge-confirmed','jar_returned'=>'badge-pending','adjustment'=>'badge-draft'];
                $typeKey    = 'dep_'.$dep->transaction_type;
                $dirKey     = 'dep_'.($dep->adjustment_direction ?? '');
            @endphp
            <span class="badge {{ $typeColors[$dep->transaction_type] ?? 'badge-draft' }}" style="margin-top:.3rem;">
                {{ __('customer.'.$typeKey) }}
                @if($dep->adjustment_direction) ({{ __('customer.'.$dirKey) }}) @endif
            </span>
            @if($dep->remarks)
                <div style="font-size:.78rem;color:#94a3b8;margin-top:.2rem;">{{ $dep->remarks }}</div>
            @endif
        </div>
        <div style="text-align:right;">
            <div style="font-size:1.1rem;font-weight:700;color:#0ea5e9;">{{ Bn::n($dep->quantity) }} {{ __('customer.jars') }}</div>
            @if((float)$dep->deposit_amount > 0)
                <div style="font-size:.82rem;color:#64748b;">৳{{ Bn::m($dep->deposit_amount) }}</div>
            @endif
        </div>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">{{ __('customer.no_deposits') }}</div>
@endforelse

<div class="pagination">{{ $deposits->links() }}</div>
@endsection
