@extends('customer.layouts.app')
@section('title', __('customer.my_bills'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<p class="page-title">{{ __('customer.my_bills') }}</p>

@forelse($invoices as $inv)
<div class="order-card">
    <div style="flex:1;">
        <div class="order-no">{{ $inv->invoice_no }}</div>
        <div style="font-size:.82rem;color:#64748b;margin:.2rem 0;">
            @if($inv->billing_month && $inv->billing_year)
                {{ Bn::my($inv->billing_month, $inv->billing_year) }} &bull;
            @endif
            {{ Bn::d($inv->invoice_date) }}
        </div>
        <div style="margin-top:.4rem;">
            <span class="badge badge-{{ $inv->invoice_status }}">{{ __('customer.status_'.$inv->invoice_status) }}</span>
        </div>
    </div>
    <div style="text-align:right;">
        <div class="order-total">৳{{ Bn::m($inv->total_amount) }}</div>
        @if((float)$inv->due_amount > 0)
            <div style="font-size:.8rem;color:#ef4444;margin-top:.2rem;">{{ __('customer.due') }}: ৳{{ Bn::m($inv->due_amount) }}</div>
        @endif
        <a href="{{ route('customer.invoices.show', $inv) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;">{{ __('customer.view_details') }}</a>
        <a href="{{ route('customer.invoices.print', $inv) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;" target="_blank">{{ __('customer.print') }}</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">{{ __('customer.no_invoices') }}</div>
@endforelse

<div class="pagination">{{ $invoices->links() }}</div>
@endsection
