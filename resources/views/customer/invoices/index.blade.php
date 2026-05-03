@extends('customer.layouts.app')
@section('title', __('customer.my_bills'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div class="page-header">
    <h1 class="page-title">{{ __('customer.my_bills') }}</h1>
</div>

@forelse($invoices as $inv)
<div class="order-card">
    <div style="flex:1;min-width:0">
        <div class="order-no">{{ $inv->invoice_no }}</div>
        <div style="font-size:12px;color:#64748b;margin:3px 0">
            @if($inv->billing_month && $inv->billing_year)
                {{ Bn::my($inv->billing_month, $inv->billing_year) }} &bull;
            @endif
            {{ Bn::d($inv->invoice_date) }}
        </div>
        <div style="margin-top:6px">
            <span class="badge badge-{{ $inv->invoice_status }}">{{ __('customer.status_'.$inv->invoice_status) }}</span>
        </div>
    </div>
    <div style="text-align:right;flex-shrink:0">
        <div class="order-total">৳{{ Bn::m($inv->total_amount) }}</div>
        @if((float)$inv->due_amount > 0)
            <div style="font-size:12px;color:#dc2626;font-weight:600;margin-top:2px">{{ __('customer.due') }}: ৳{{ Bn::m($inv->due_amount) }}</div>
        @else
            <div style="font-size:12px;color:#16a34a;font-weight:600;margin-top:2px">✓ Cleared</div>
        @endif
        <div style="display:flex;gap:5px;justify-content:flex-end;margin-top:8px">
            <a href="{{ route('customer.invoices.show', $inv) }}" class="btn btn-outline btn-sm">{{ __('customer.view_details') }}</a>
            <a href="{{ route('customer.invoices.print', $inv) }}" class="btn btn-sm" style="background:#f3f4f6;color:#374151" target="_blank">{{ __('customer.print') }}</a>
        </div>
    </div>
</div>
@empty
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">🧾</div>
        <div class="empty-state-title">{{ __('customer.no_invoices') }}</div>
    </div>
</div>
@endforelse

<div class="pagination">{{ $invoices->links() }}</div>
@endsection
