@extends('delivery.layouts.app')
@section('title', 'Dashboard')

@section('content')
<p class="page-title">📊 My Stats</p>
<p style="font-size:.82rem;color:#64748b;margin-bottom:1rem;">{{ auth()->user()->name }}</p>

<p style="font-size:.85rem;font-weight:600;color:#64748b;margin-bottom:.5rem;">TODAY</p>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $todayTotal }}</div>
        <div class="stat-label">Total Assigned</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#10b981;">{{ $todayDelivered }}</div>
        <div class="stat-label">Delivered</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#f59e0b;">{{ $todayActive }}</div>
        <div class="stat-label">Pending / Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-value" style="color:#ef4444;">{{ $todayFailed }}</div>
        <div class="stat-label">Failed</div>
    </div>
</div>

<div class="card" style="text-align:center;">
    <div style="font-size:.82rem;color:#64748b;">This Month Total</div>
    <div style="font-size:2rem;font-weight:700;color:#0ea5e9;margin:.25rem 0;">{{ $monthTotal }}</div>
    <div style="font-size:.78rem;color:#94a3b8;">deliveries assigned</div>
</div>

<a href="{{ route('delivery.today') }}" class="btn btn-primary btn-block">View Today's Deliveries</a>
@endsection
