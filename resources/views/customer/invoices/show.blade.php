@extends('customer.layouts.app')
@section('title', __('customer.invoice_details'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('customer.invoices.index') }}" style="color:#0ea5e9;text-decoration:none;">{{ __('customer.back_bills') }}</a>
    <p class="page-title" style="margin:0;">{{ __('customer.invoice_details') }}</p>
    <a href="{{ route('customer.invoices.print', $invoice) }}" class="btn btn-outline btn-sm" target="_blank" style="margin-left:auto;">{{ __('customer.print') }}</a>
</div>

<div class="card">
    <div class="card-title" style="font-family:monospace;">{{ $invoice->invoice_no }}</div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.billing_period') }}</span>
        <span class="list-value">
            {{ $invoice->billing_month && $invoice->billing_year ? Bn::my($invoice->billing_month, $invoice->billing_year) : '—' }}
        </span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.invoice_date') }}</span>
        <span class="list-value">{{ Bn::d($invoice->invoice_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.due_date') }}</span>
        <span class="list-value">{{ Bn::d($invoice->due_date) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.subtotal') }}</span>
        <span class="list-value">৳ {{ Bn::m($invoice->subtotal) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.previous_due') }}</span>
        <span class="list-value">৳ {{ Bn::m($invoice->previous_due) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.total_amount') }}</span>
        <span class="list-value">৳ {{ Bn::m($invoice->total_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.paid_amount') }}</span>
        <span class="list-value">৳ {{ Bn::m($invoice->paid_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.due_amount') }}</span>
        <span class="list-value" style="{{ (float)$invoice->due_amount > 0 ? 'color:#ef4444' : '' }}">৳ {{ Bn::m($invoice->due_amount) }}</span>
    </div>
    <div class="list-item">
        <span class="list-label">{{ __('customer.status') }}</span>
        <span class="badge badge-{{ $invoice->invoice_status }}">{{ __('customer.status_'.$invoice->invoice_status) }}</span>
    </div>
</div>

@if($invoice->payments->count())
<div class="card">
    <div class="card-title">{{ __('customer.payments_label') }}</div>
    @foreach($invoice->payments as $pay)
    <div class="list-item">
        <div>
            <div style="font-family:monospace;font-size:.82rem;">{{ $pay->payment_no }}</div>
            <div style="font-size:.78rem;color:#64748b;">{{ Bn::d($pay->payment_date) }} &bull; {{ ucfirst($pay->payment_method) }}</div>
        </div>
        <span style="font-weight:700;color:#10b981;">৳{{ Bn::m($pay->amount) }}</span>
    </div>
    @endforeach
</div>
@endif
@endsection
