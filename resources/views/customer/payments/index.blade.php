@extends('customer.layouts.app')
@section('title', __('customer.my_payments'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ __('customer.my_payments') }}</h1>
</div>

@forelse($payments as $pay)
<div class="order-card">
    <div style="flex:1;min-width:0">
        <div class="order-no">{{ $pay->payment_no }}</div>
        <div style="font-size:12px;color:#64748b;margin:3px 0">
            {{ Bn::d($pay->payment_date) }} &bull;
            <span style="display:inline-flex;align-items:center;gap:3px">
                <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block"></span>
                {{ ucfirst($pay->payment_method) }}
            </span>
        </div>
        @if($pay->invoice)
            <div style="font-size:11px;color:#94a3b8;margin-top:2px">{{ __('customer.invoice_ref') }}: <span style="font-family:monospace">{{ $pay->invoice->invoice_no }}</span></div>
        @endif
        @if($pay->reference_no)
            <div style="font-size:11px;color:#94a3b8">{{ __('customer.ref') }}: {{ $pay->reference_no }}</div>
        @endif
        @if($pay->remarks)
            <div style="font-size:11px;color:#94a3b8">{{ $pay->remarks }}</div>
        @endif
    </div>
    <div style="text-align:right;flex-shrink:0">
        <div style="font-size:20px;font-weight:800;color:#16a34a">৳{{ Bn::m($pay->amount) }}</div>
        <a href="{{ route('customer.payments.print', $pay) }}" class="btn btn-sm" style="background:#f3f4f6;color:#374151;margin-top:8px" target="_blank">
            {{ __('customer.print_receipt') }}
        </a>
    </div>
</div>
@empty
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">💳</div>
        <div class="empty-state-title">{{ __('customer.no_payments') }}</div>
    </div>
</div>
@endforelse

<div class="pagination">{{ $payments->links() }}</div>
@endsection
