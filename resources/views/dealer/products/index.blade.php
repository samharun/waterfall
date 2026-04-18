@extends('dealer.layouts.app')
@section('title', 'My Price List')
@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <p class="page-title" style="margin:0;">My Price List</p>
    <a href="{{ route('dealer.orders.create') }}" class="btn btn-primary btn-sm">+ Order</a>
</div>

@foreach($priceList as $item)
@php $p = $item['product']; @endphp
<div class="card" style="padding:.9rem 1rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-weight:700;">{{ $p->name }}</div>
            <div style="font-family:monospace;font-size:.78rem;color:#94a3b8;">{{ $p->sku }}</div>
            <div style="font-size:.8rem;color:#64748b;margin-top:.2rem;">{{ ucfirst($p->product_type) }}</div>
        </div>
        <div style="text-align:right;">
            @if($item['has_custom'])
                <div class="price-custom" style="font-size:1.1rem;">৳{{ number_format($item['dealer_price'], 2) }}</div>
                <div style="font-size:.75rem;text-decoration:line-through;color:#94a3b8;">৳{{ number_format($item['default_price'], 2) }}</div>
                <div style="font-size:.7rem;color:#059669;font-weight:600;">Custom Price</div>
            @else
                <div class="price-default" style="font-size:1.05rem;font-weight:700;">৳{{ number_format($item['dealer_price'], 2) }}</div>
                <div style="font-size:.7rem;color:#94a3b8;">Default Price</div>
            @endif
            @if((float)$p->deposit_amount > 0)
                <div style="font-size:.75rem;color:#64748b;margin-top:.2rem;">Deposit: ৳{{ number_format((float)$p->deposit_amount, 2) }}</div>
            @endif
        </div>
    </div>
</div>
@endforeach

@if($priceList->isEmpty())
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">No active products found.</div>
@endif
@endsection
