@extends('customer.layouts.app')
@section('title', 'My Subscription')

@section('content')
<p class="page-title">🔄 My Subscription</p>

@if(! $subscription)
<div class="card" style="text-align:center;padding:2rem;">
    <div style="font-size:2rem;margin-bottom:.75rem;">📦</div>
    <div style="font-weight:600;margin-bottom:.5rem;">No Active Subscription</div>
    <div style="font-size:.85rem;color:#64748b;margin-bottom:1.25rem;">Set up recurring delivery to get water jars automatically.</div>
    <a href="{{ route('customer.subscription.create') }}" class="btn btn-primary">Set Recurring Delivery</a>
</div>
@else

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
        <div class="card-title" style="margin:0;font-family:monospace;">{{ $subscription->subscription_no }}</div>
        <span class="badge badge-{{ $subscription->status }}">{{ ucfirst($subscription->status) }}</span>
    </div>

    @foreach([
        ['Product',        $subscription->product?->name . ' [' . $subscription->product?->sku . ']'],
        ['Quantity',       $subscription->quantity . ' jar(s)'],
        ['Frequency',      ucwords(str_replace('_', ' ', $subscription->frequency))],
        ['Delivery Days',  $subscription->delivery_days ? implode(', ', array_map('ucfirst', $subscription->delivery_days)) : '—'],
        ['Delivery Slot',  ucfirst($subscription->preferred_delivery_slot)],
        ['Delivery Time',  $subscription->preferred_delivery_time ?? '—'],
        ['Start Date',     $subscription->start_date?->format('d M Y')],
        ['Next Delivery',  $subscription->next_delivery_date?->format('d M Y') ?? '—'],
    ] as [$label, $value])
    <div class="list-item">
        <span class="list-label">{{ $label }}</span>
        <span class="list-value">{{ $value }}</span>
    </div>
    @endforeach

    @if($subscription->isPaused())
    <div style="background:#fef3c7;border-radius:8px;padding:.75rem;margin-top:.75rem;font-size:.85rem;">
        <strong>⏸ Paused</strong>
        @if($subscription->paused_from) from {{ $subscription->paused_from->format('d M Y') }} @endif
        @if($subscription->paused_to) to {{ $subscription->paused_to->format('d M Y') }} @endif
        @if($subscription->pause_reason)<br>Reason: {{ $subscription->pause_reason }}@endif
    </div>
    @endif
</div>

<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;">
    @if($subscription->isActive())
        <a href="{{ route('customer.subscription.edit') }}" class="btn btn-outline btn-sm">Edit</a>
    @endif

    @if($subscription->isActive())
        <button type="button" class="btn btn-warning btn-sm"
            onclick="document.getElementById('pause-section').style.display='block';this.style.display='none'">
            ⏸ Pause
        </button>
    @endif

    @if($subscription->isPaused())
        <form method="POST" action="{{ route('customer.subscription.resume') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Resume subscription?')">▶ Resume</button>
        </form>
    @endif

    @if(in_array($subscription->status, ['active','paused']))
        <form method="POST" action="{{ route('customer.subscription.cancel') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Cancel subscription? This cannot be undone.')">✗ Cancel</button>
        </form>
    @endif
</div>

{{-- Pause form --}}
<div id="pause-section" style="display:none;">
    <div class="card">
        <div class="card-title">Pause Subscription</div>
        <form method="POST" action="{{ route('customer.subscription.pause') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Pause From *</label>
                <input type="date" name="paused_from" class="form-control"
                    value="{{ today()->toDateString() }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Resume On (optional)</label>
                <input type="date" name="paused_to" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Reason (optional)</label>
                <textarea name="pause_reason" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <button type="submit" class="btn btn-warning btn-block">Confirm Pause</button>
        </form>
    </div>
</div>

@endif
@endsection
