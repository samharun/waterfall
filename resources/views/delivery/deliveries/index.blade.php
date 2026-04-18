@extends('delivery.layouts.app')
@section('title', 'All Deliveries')

@section('content')
<p class="page-title">All My Deliveries</p>

<form method="GET" style="display:flex;gap:.5rem;margin-bottom:1rem;">
    <select name="status" class="form-control form-select" style="flex:1;" onchange="this.form.submit()">
        <option value="">All Status</option>
        @foreach(\App\Models\Delivery::statusLabels() as $key => $label)
            <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</form>

@forelse($deliveries as $delivery)
    @php
        $order = $delivery->order;
        $party = $order?->customer ?? $order?->dealer;
    @endphp
    <div class="delivery-card status-{{ $delivery->delivery_status }}">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div style="flex:1;">
                <div class="del-no">{{ $delivery->delivery_no }} &bull; {{ $order?->order_no ?? '—' }}</div>
                <div class="party-name" style="margin-top:.2rem;">{{ $party?->name ?? '—' }}</div>
                <div style="font-size:.78rem;color:#64748b;margin-top:.15rem;">
                    {{ $delivery->zone?->name ?? '—' }}
                    @if($delivery->assigned_at) &bull; Assigned: {{ $delivery->assigned_at->format('d M H:i') }} @endif
                    @if($delivery->delivered_at) &bull; Delivered: {{ $delivery->delivered_at->format('d M H:i') }} @endif
                </div>
            </div>
            <span class="badge badge-{{ $delivery->delivery_status }}">
                {{ ucwords(str_replace('_',' ',$delivery->delivery_status)) }}
            </span>
        </div>
        <div style="margin-top:.6rem;">
            <a href="{{ route('delivery.deliveries.show', $delivery) }}" class="btn btn-outline btn-sm">View</a>
        </div>
    </div>
@empty
    <div class="card" style="text-align:center;color:#94a3b8;padding:2rem;">No deliveries found.</div>
@endforelse

<div class="pagination">{{ $deliveries->links() }}</div>
@endsection
