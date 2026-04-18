@extends('customer.layouts.app')
@section('title', __('customer.order_water'))
@php use App\Helpers\BnHelper as Bn; @endphp

@section('content')
<p class="page-title">{{ __('customer.order_water') }}</p>

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-left:1.2rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('customer.orders.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="product_id">{{ __('customer.product') }}</label>
            <select name="product_id" id="product_id" class="form-control form-select" required onchange="updatePrice(this)">
                <option value="">{{ __('customer.select_product') }}</option>
                @foreach($products as $p)
                    @php $price = $p->getPriceForCustomer(auth()->user()->customer->id, today()->toDateString()); @endphp
                    <option value="{{ $p->id }}"
                        data-price="{{ $price }}"
                        {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        [{{ $p->sku }}] {{ $p->name }} — ৳{{ Bn::m($price) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="quantity">{{ __('customer.quantity') }}</label>
            <input type="number" name="quantity" id="quantity" class="form-control"
                value="{{ old('quantity', 1) }}" min="1" max="100" required
                oninput="updateTotal()">
        </div>

        <div class="form-group" style="background:#f0f9ff;border-radius:8px;padding:.75rem;margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.85rem;color:#64748b;">{{ __('customer.unit_price') }}</span>
                <span id="unit-price-display" style="font-weight:700;color:#0ea5e9;">৳০.০০</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.4rem;">
                <span style="font-size:.85rem;color:#64748b;">{{ __('customer.total_amount') }}</span>
                <span id="total-display" style="font-weight:700;font-size:1.1rem;color:#0ea5e9;">৳০.০০</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="preferred_delivery_slot">{{ __('customer.delivery_slot') }}</label>
            <select name="preferred_delivery_slot" id="preferred_delivery_slot" class="form-control form-select" required
                onchange="toggleCustomTime(this.value)">
                @foreach(__('customer.slot_labels') as $key => $label)
                    <option value="{{ $key }}" {{ old('preferred_delivery_slot', 'now') === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" id="custom-time-group" style="display:none;">
            <label class="form-label" for="preferred_delivery_time">{{ __('customer.preferred_delivery_time') }}</label>
            <input type="datetime-local" name="preferred_delivery_time" id="preferred_delivery_time"
                class="form-control" value="{{ old('preferred_delivery_time') }}">
        </div>

        <div class="form-group">
            <label class="form-label" for="remarks">{{ __('customer.remarks_optional') }}</label>
            <textarea name="remarks" id="remarks" class="form-control" rows="2"
                placeholder="{{ __('customer.special_instructions') }}" maxlength="500">{{ old('remarks') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="padding:.8rem;font-size:1rem;">
            {{ __('customer.place_order') }}
        </button>
    </form>
</div>

<script>
const isBn = {{ app()->getLocale() === 'bn' ? 'true' : 'false' }};
const bnDigits = {'0':'০','1':'১','2':'২','3':'৩','4':'৪','5':'৫','6':'৬','7':'৭','8':'৮','9':'৯'};

function toBn(str) {
    if (!isBn) return str;
    return String(str).replace(/[0-9]/g, d => bnDigits[d]);
}

function updatePrice(select) {
    const price = parseFloat(select.options[select.selectedIndex]?.dataset?.price || 0);
    const qty   = parseInt(document.getElementById('quantity').value) || 1;
    document.getElementById('unit-price-display').textContent = '৳' + toBn(price.toFixed(2));
    document.getElementById('total-display').textContent = '৳' + toBn((price * qty).toFixed(2));
}
function updateTotal() {
    const select = document.getElementById('product_id');
    const price  = parseFloat(select.options[select.selectedIndex]?.dataset?.price || 0);
    const qty    = parseInt(document.getElementById('quantity').value) || 1;
    document.getElementById('total-display').textContent = '৳' + toBn((price * qty).toFixed(2));
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
