@extends('customer.layouts.app')
@section('title', 'Set Recurring Delivery')

@section('content')
<p class="page-title">🔄 Set Recurring Delivery</p>

@if($errors->any())
<div class="alert alert-danger">
    <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('customer.subscription.store') }}" id="sub-form">
        @csrf

        <div class="form-group">
            <label class="form-label">Product *</label>
            <select name="product_id" class="form-control" required>
                <option value="">— Select Product —</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        [{{ $p->sku }}] {{ $p->name }} — ৳{{ number_format((float)$p->getPriceForCustomer($customer->id, today()->toDateString()), 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Quantity *</label>
            <input type="number" name="quantity" class="form-control"
                value="{{ old('quantity', 1) }}" min="1" required>
        </div>

        <div class="form-group">
            <label class="form-label">Frequency *</label>
            <select name="frequency" id="frequency" class="form-control" required onchange="toggleDays(this.value)">
                @foreach(\App\Models\CustomerSubscription::frequencyLabels() as $key => $label)
                    <option value="{{ $key }}" {{ old('frequency', 'daily') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="days-group" style="display:none;">
            <label class="form-label">Delivery Days *</label>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.4rem;">
                @foreach(\App\Models\CustomerSubscription::deliveryDayOptions() as $key => $label)
                    <label style="display:flex;align-items:center;gap:.4rem;font-size:.88rem;">
                        <input type="checkbox" name="delivery_days[]" value="{{ $key }}"
                            {{ in_array($key, old('delivery_days', [])) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Delivery Slot *</label>
            <select name="preferred_delivery_slot" id="slot" class="form-control" required onchange="toggleTime(this.value)">
                @foreach(\App\Models\CustomerSubscription::slotLabels() as $key => $label)
                    <option value="{{ $key }}" {{ old('preferred_delivery_slot', 'morning') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="time-group" style="display:none;">
            <label class="form-label">Delivery Time *</label>
            <input type="time" name="preferred_delivery_time" class="form-control"
                value="{{ old('preferred_delivery_time') }}">
        </div>

        <div class="form-group">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control"
                value="{{ old('start_date', today()->toDateString()) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="2" maxlength="500">{{ old('remarks') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create Subscription</button>
    </form>
</div>

<script>
function toggleDays(val) {
    document.getElementById('days-group').style.display = ['weekly','custom_days'].includes(val) ? 'block' : 'none';
}
function toggleTime(val) {
    document.getElementById('time-group').style.display = val === 'custom' ? 'block' : 'none';
}
window.addEventListener('DOMContentLoaded', () => {
    toggleDays(document.getElementById('frequency').value);
    toggleTime(document.getElementById('slot').value);
});
</script>
@endsection
