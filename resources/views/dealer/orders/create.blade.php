@extends('dealer.layouts.app')
@section('title', 'Place Bulk Order')
@section('content')
<p class="page-title">📦 Place Bulk Order</p>

@if($errors->any())
<div class="alert alert-danger">
    <ul style="margin:0;padding-left:1.2rem;">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="card">
    <form method="POST" action="{{ route('dealer.orders.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="product_id">Product</label>
            <select name="product_id" id="product_id" class="form-control form-select" required onchange="updatePrice(this)">
                <option value="">— Select Product —</option>
                @foreach($products as $p)
                    @php $dealerPrice = $p->getPriceForDealer($dealer->id, $today); @endphp
                    <option value="{{ $p->id }}"
                        data-price="{{ $dealerPrice }}"
                        {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        [{{ $p->sku }}] {{ $p->name }} — ৳{{ number_format((float)$dealerPrice, 2) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="quantity">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control"
                value="{{ old('quantity', 1) }}" min="1" max="500" required
                oninput="updateTotal()">
        </div>

        <div style="background:#f0f9ff;border-radius:8px;padding:.75rem;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;">
                <span style="font-size:.85rem;color:#64748b;">Unit Price</span>
                <span id="unit-price-display" style="font-weight:700;color:#1e3a5f;">৳0.00</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:.4rem;">
                <span style="font-size:.85rem;color:#64748b;">Total Amount</span>
                <span id="total-display" style="font-weight:700;font-size:1.1rem;color:#1e3a5f;">৳0.00</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="preferred_delivery_slot">Delivery Slot</label>
            <select name="preferred_delivery_slot" id="preferred_delivery_slot" class="form-control form-select" required
                onchange="toggleCustomTime(this.value)">
                @foreach($slots as $key => $label)
                    <option value="{{ $key }}" {{ old('preferred_delivery_slot', 'now') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="custom-time-group" style="display:none;">
            <label class="form-label" for="preferred_delivery_time">Preferred Delivery Time</label>
            <input type="datetime-local" name="preferred_delivery_time" id="preferred_delivery_time"
                class="form-control" value="{{ old('preferred_delivery_time') }}">
        </div>

        <div class="form-group">
            <label class="form-label" for="remarks">Remarks (optional)</label>
            <textarea name="remarks" id="remarks" class="form-control" rows="2"
                placeholder="Any special instructions..." maxlength="500">{{ old('remarks') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Place Order</button>
    </form>
</div>

<script>
function updatePrice(select) {
    const price = parseFloat(select.options[select.selectedIndex]?.dataset?.price || 0);
    const qty   = parseInt(document.getElementById('quantity').value) || 1;
    document.getElementById('unit-price-display').textContent = '৳' + price.toFixed(2);
    document.getElementById('total-display').textContent = '৳' + (price * qty).toFixed(2);
}
function updateTotal() {
    const select = document.getElementById('product_id');
    const price  = parseFloat(select.options[select.selectedIndex]?.dataset?.price || 0);
    const qty    = parseInt(document.getElementById('quantity').value) || 1;
    document.getElementById('total-display').textContent = '৳' + (price * qty).toFixed(2);
}
function toggleCustomTime(val) {
    document.getElementById('custom-time-group').style.display = val === 'custom' ? 'block' : 'none';
    document.getElementById('preferred_delivery_time').required = val === 'custom';
}
window.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('product_id');
    if (sel.value) updatePrice(sel);
    toggleCustomTime(document.getElementById('preferred_delivery_slot').value);
});
</script>
@endsection
