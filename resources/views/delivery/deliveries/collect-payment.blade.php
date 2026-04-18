@extends('delivery.layouts.app')
@section('title', 'Collect Payment')

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('delivery.deliveries.show', $delivery) }}" style="color:#38bdf8;text-decoration:none;">← Delivery</a>
    <p class="page-title" style="margin:0;">Collect Payment</p>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Summary --}}
<div class="card" style="background:#0f172a;color:#fff;border-radius:12px;padding:1rem;margin-bottom:1rem;">
    <div style="font-family:monospace;font-size:.8rem;color:#94a3b8;">{{ $delivery->delivery_no }}</div>
    <div style="font-size:1rem;font-weight:700;margin:.3rem 0;">{{ $party?->name ?? '—' }}</div>
    <div style="font-size:.85rem;color:#94a3b8;">📞 {{ $party?->mobile ?? '—' }}</div>
    <div style="display:flex;gap:1.5rem;margin-top:.75rem;font-size:.85rem;">
        <div>
            <div style="color:#94a3b8;font-size:.75rem;">Order Total</div>
            <div style="font-weight:700;color:#38bdf8;">৳{{ number_format((float)($order?->total_amount ?? 0), 2) }}</div>
        </div>
        <div>
            <div style="color:#94a3b8;font-size:.75rem;">Current Due</div>
            <div style="font-weight:700;color:#f87171;">৳{{ number_format((float)($party?->current_due ?? 0), 2) }}</div>
        </div>
        @if($totalCollected > 0)
        <div>
            <div style="color:#94a3b8;font-size:.75rem;">Already Collected</div>
            <div style="font-weight:700;color:#34d399;">৳{{ number_format($totalCollected, 2) }}</div>
        </div>
        @endif
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('delivery.deliveries.collect-payment.store', $delivery) }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Amount (৳) *</label>
            <input type="number" name="amount" class="form-control"
                value="{{ old('amount', $suggestedAmount > 0 ? number_format($suggestedAmount, 2, '.', '') : '') }}"
                min="0.01" step="0.01" required
                style="font-size:1.3rem;font-weight:700;text-align:center;">
        </div>

        <div class="form-group">
            <label class="form-label">Payment Method *</label>
            <select name="payment_method" class="form-control" required>
                @foreach(\App\Models\Payment::methodLabels() as $key => $label)
                    <option value="{{ $key }}" {{ old('payment_method', 'cash') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        @if($invoices->count())
        <div class="form-group">
            <label class="form-label">Apply to Invoice (optional)</label>
            <select name="invoice_id" class="form-control">
                <option value="">— No Invoice (Unallocated) —</option>
                @foreach($invoices as $inv)
                    <option value="{{ $inv->id }}"
                        {{ old('invoice_id', $suggestedInvoice?->id) == $inv->id ? 'selected' : '' }}>
                        {{ $inv->invoice_no }} — Due: ৳{{ number_format((float)$inv->due_amount, 2) }}
                    </option>
                @endforeach
            </select>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.2rem;">
                Amount must not exceed selected invoice due amount.
            </div>
        </div>
        @else
        <input type="hidden" name="invoice_id" value="">
        <div style="background:#1e293b;border-radius:8px;padding:.6rem .9rem;margin-bottom:1rem;font-size:.8rem;color:#94a3b8;">
            No unpaid invoices found. Payment will be recorded as unallocated.
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Reference No (optional)</label>
            <input type="text" name="reference_no" class="form-control"
                value="{{ old('reference_no') }}" placeholder="bKash/Nagad transaction ID" maxlength="100">
        </div>

        <div class="form-group">
            <label class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="2" maxlength="500">{{ old('remarks') }}</textarea>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="font-size:1rem;padding:.85rem;">
            💰 Save Collection
        </button>
    </form>
</div>
@endsection
