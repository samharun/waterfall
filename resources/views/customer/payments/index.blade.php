@extends('customer.layouts.app')
@section('title', __('customer.my_payments'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<p class="page-title">{{ __('customer.my_payments') }}</p>

@forelse($payments as $pay)
<div class="card" style="padding:.9rem 1rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-family:monospace;font-size:.85rem;font-weight:600;">{{ $pay->payment_no }}</div>
            <div style="font-size:.8rem;color:#64748b;margin-top:.2rem;">
                {{ Bn::d($pay->payment_date) }} &bull; {{ ucfirst($pay->payment_method) }}
            </div>
            @if($pay->invoice)
                <div style="font-size:.78rem;color:#94a3b8;margin-top:.15rem;">{{ __('customer.invoice_ref') }}: {{ $pay->invoice->invoice_no }}</div>
            @endif
            @if($pay->reference_no)
                <div style="font-size:.78rem;color:#94a3b8;">{{ __('customer.ref') }}: {{ $pay->reference_no }}</div>
            @endif
            @if($pay->remarks)
                <div style="font-size:.78rem;color:#94a3b8;">{{ $pay->remarks }}</div>
            @endif
        </div>
        <div style="font-size:1.15rem;font-weight:700;color:#10b981;">৳{{ Bn::m($pay->amount) }}</div>
    </div>
    <div style="margin-top:.5rem;text-align:right;">
        <a href="{{ route('customer.payments.print', $pay) }}" class="btn btn-outline btn-sm" target="_blank">{{ __('customer.print_receipt') }}</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">{{ __('customer.no_payments') }}</div>
@endforelse

<div class="pagination">{{ $payments->links() }}</div>
@endsection
