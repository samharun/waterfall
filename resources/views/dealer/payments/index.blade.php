@extends('dealer.layouts.app')
@section('title', 'My Payments')
@section('content')
<p class="page-title">My Payments</p>
@forelse($payments as $pay)
<div class="card" style="padding:.9rem 1rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-family:monospace;font-size:.85rem;font-weight:600;">{{ $pay->payment_no }}</div>
            <div style="font-size:.78rem;color:#64748b;margin-top:.2rem;">{{ $pay->payment_date?->format('d M Y') }} &bull; {{ ucfirst($pay->payment_method) }}</div>
            @if($pay->invoice)<div style="font-size:.75rem;color:#94a3b8;">Invoice: {{ $pay->invoice->invoice_no }}</div>@endif
            @if($pay->reference_no)<div style="font-size:.75rem;color:#94a3b8;">Ref: {{ $pay->reference_no }}</div>@endif
            @if($pay->remarks)<div style="font-size:.75rem;color:#94a3b8;">{{ $pay->remarks }}</div>@endif
        </div>
        <div style="font-size:1.1rem;font-weight:700;color:#10b981;">৳{{ number_format((float)$pay->amount, 2) }}</div>
    </div>
    <div style="margin-top:.4rem;text-align:right;">
        <a href="{{ route('dealer.payments.print', $pay) }}" class="btn btn-outline btn-sm" target="_blank">🖨 Print</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">No payments found.</div>
@endforelse
<div class="pagination">{{ $payments->links() }}</div>
@endsection
