@extends('dealer.layouts.app')
@section('title', 'My Bills')
@section('content')
<p class="page-title">My Bills</p>
@forelse($invoices as $inv)
<div class="order-card">
    <div style="flex:1;">
        <div class="order-no">{{ $inv->invoice_no }}</div>
        <div style="font-size:.8rem;color:#64748b;margin:.2rem 0;">
            @if($inv->billing_month && $inv->billing_year)\Carbon\Carbon::create($inv->billing_year,$inv->billing_month)->format('F Y') &bull; @endif
            {{ $inv->invoice_date?->format('d M Y') }}
        </div>
        <span class="badge badge-{{ $inv->invoice_status }}">{{ ucfirst($inv->invoice_status) }}</span>
    </div>
    <div style="text-align:right;">
        <div class="order-total">৳{{ number_format((float)$inv->total_amount, 2) }}</div>
        @if((float)$inv->due_amount > 0)<div style="font-size:.78rem;color:#ef4444;">Due: ৳{{ number_format((float)$inv->due_amount, 2) }}</div>@endif
        <a href="{{ route('dealer.invoices.show', $inv) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;">View</a>
        <a href="{{ route('dealer.invoices.print', $inv) }}" class="btn btn-outline btn-sm" style="margin-top:.5rem;" target="_blank">🖨 Print</a>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">No invoices found.</div>
@endforelse
<div class="pagination">{{ $invoices->links() }}</div>
@endsection
