@extends('dealer.layouts.app')
@section('title', 'Invoice Details')
@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('dealer.invoices.index') }}" style="color:#1e3a5f;text-decoration:none;">← Bills</a>
    <p class="page-title" style="margin:0;">Invoice Details</p>
</div>
<div class="card">
    <div class="card-title" style="font-family:monospace;">{{ $invoice->invoice_no }}</div>
    @foreach([
        ['Billing Period', $invoice->billing_month && $invoice->billing_year ? \Carbon\Carbon::create($invoice->billing_year,$invoice->billing_month)->format('F Y') : '—'],
        ['Invoice Date',   $invoice->invoice_date?->format('d M Y')],
        ['Due Date',       $invoice->due_date?->format('d M Y') ?? '—'],
        ['Subtotal',       '৳ '.number_format((float)$invoice->subtotal, 2)],
        ['Previous Due',   '৳ '.number_format((float)$invoice->previous_due, 2)],
        ['Total Amount',   '৳ '.number_format((float)$invoice->total_amount, 2)],
        ['Paid Amount',    '৳ '.number_format((float)$invoice->paid_amount, 2)],
        ['Due Amount',     '৳ '.number_format((float)$invoice->due_amount, 2)],
    ] as [$label, $value])
    <div class="list-item"><span class="list-label">{{ $label }}</span><span class="list-value">{{ $value }}</span></div>
    @endforeach
    <div class="list-item"><span class="list-label">Status</span><span class="badge badge-{{ $invoice->invoice_status }}">{{ ucfirst($invoice->invoice_status) }}</span></div>
</div>
@if($invoice->payments->count())
<div class="card">
    <div class="card-title">Payments</div>
    @foreach($invoice->payments as $pay)
    <div class="list-item">
        <div><div style="font-family:monospace;font-size:.8rem;">{{ $pay->payment_no }}</div><div style="font-size:.75rem;color:#64748b;">{{ $pay->payment_date?->format('d M Y') }} &bull; {{ ucfirst($pay->payment_method) }}</div></div>
        <span style="font-weight:700;color:#10b981;">৳{{ number_format((float)$pay->amount, 2) }}</span>
    </div>
    @endforeach
</div>
@endif
@endsection
