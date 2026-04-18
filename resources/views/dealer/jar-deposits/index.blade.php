@extends('dealer.layouts.app')
@section('title', 'Jar Deposits')
@section('content')
<p class="page-title">Jar Deposits</p>
<div class="stat-card" style="margin-bottom:1rem;">
    <div class="stat-value">{{ $jarDepositQty }}</div>
    <div class="stat-label">Jars Currently With You</div>
</div>
@forelse($deposits as $dep)
<div class="card" style="padding:.9rem 1rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-family:monospace;font-size:.82rem;font-weight:600;">{{ $dep->deposit_no }}</div>
            <div style="font-size:.78rem;color:#64748b;margin-top:.15rem;">{{ $dep->transaction_date?->format('d M Y') }}</div>
            <div style="font-size:.82rem;margin-top:.2rem;">{{ $dep->product?->name }}</div>
            @php $typeColors=['deposit_received'=>'badge-confirmed','jar_issued'=>'badge-confirmed','jar_returned'=>'badge-pending','adjustment'=>'badge-draft']; @endphp
            <span class="badge {{ $typeColors[$dep->transaction_type] ?? 'badge-draft' }}" style="margin-top:.3rem;">
                {{ ucwords(str_replace('_',' ',$dep->transaction_type)) }}
                @if($dep->adjustment_direction) ({{ $dep->adjustment_direction }}) @endif
            </span>
            @if($dep->remarks)<div style="font-size:.75rem;color:#94a3b8;margin-top:.2rem;">{{ $dep->remarks }}</div>@endif
        </div>
        <div style="text-align:right;">
            <div style="font-size:1.05rem;font-weight:700;color:#1e3a5f;">{{ $dep->quantity }} jar(s)</div>
            @if((float)$dep->deposit_amount > 0)<div style="font-size:.8rem;color:#64748b;">৳{{ number_format((float)$dep->deposit_amount, 2) }}</div>@endif
        </div>
    </div>
</div>
@empty
<div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">No jar deposit records found.</div>
@endforelse
<div class="pagination">{{ $deposits->links() }}</div>
@endsection
