@extends('customer.layouts.app')
@section('title', __('customer.edit_profile'))

@section('content')
<div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem;">
    <a href="{{ route('customer.profile') }}" style="color:#0ea5e9;text-decoration:none;">← {{ __('customer.my_profile') }}</a>
    <p class="page-title" style="margin:0;">{{ __('customer.edit_profile') }}</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-left:1.2rem;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form method="POST" action="{{ route('customer.profile.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">{{ __('customer.full_name') }}</label>
            <input type="text" name="name" class="form-control"
                value="{{ old('name', $customer->name) }}" required maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.full_name_bn') }}</label>
            <input type="text" name="name_bn" class="form-control"
                value="{{ old('name_bn', $customer->name_bn) }}" maxlength="255"
                placeholder="বাংলায় নাম লিখুন" style="font-family:'Hind Siliguri',sans-serif;">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.mobile') }}</label>
            <input type="text" class="form-control" value="{{ $customer->mobile }}" disabled
                style="background:#f8fafc;color:#94a3b8;">
            <div style="font-size:.75rem;color:#94a3b8;margin-top:.2rem;">{{ __('customer.mobile_hint') }}</div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.email') }}</label>
            <input type="email" name="email" class="form-control"
                value="{{ old('email', $customer->email) }}" placeholder="your@email.com">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.address_label') }}</label>
            <textarea name="address" class="form-control" rows="3" required maxlength="500">{{ old('address', $customer->address) }}</textarea>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.address_bn_label') }}</label>
            <textarea name="address_bn" class="form-control" rows="3" maxlength="500"
                placeholder="বাংলায় ঠিকানা লিখুন"
                style="font-family:'Hind Siliguri',sans-serif;">{{ old('address_bn', $customer->address_bn) }}</textarea>
        </div>

        @if($zones->count())
        <div class="form-group">
            <label class="form-label">{{ __('customer.zone_line') }}</label>
            <select name="zone_id" class="form-control" style="appearance:none;">
                <option value="">{{ __('customer.select_zone') }}</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id', $customer->zone_id) == $zone->id ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">{{ __('customer.customer_type') }} *</label>
            <select name="customer_type" class="form-control" required>
                <option value="residential" {{ old('customer_type', $customer->customer_type) === 'residential' ? 'selected' : '' }}>
                    {{ __('customer.type_residential') }}
                </option>
                <option value="corporate" {{ old('customer_type', $customer->customer_type) === 'corporate' ? 'selected' : '' }}>
                    {{ __('customer.type_corporate') }}
                </option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.default_delivery_slot') }}</label>
            <select name="default_delivery_slot" class="form-control">
                <option value="">{{ __('customer.none') }}</option>
                @foreach(__('customer.slot_labels') as $key => $label)
                    <option value="{{ $key }}" {{ old('default_delivery_slot', $customer->default_delivery_slot) === $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-block">{{ __('customer.save_changes') }}</button>
    </form>
</div>
@endsection
