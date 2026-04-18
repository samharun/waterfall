<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 1.5rem 1rem; }
        .box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-img { width: 70px; height: auto; margin: 0 auto; }
        .logo-title { font-size: 1.3rem; font-weight: 700; color: #0ea5e9; margin-top: .3rem; display: none; }
        .logo-sub { font-size: .8rem; color: #64748b; }
        .form-group { margin-bottom: .9rem; }
        .form-label { display: block; font-size: .83rem; font-weight: 600; margin-bottom: .3rem; color: #374151; }
        .form-control { width: 100%; padding: .65rem .85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem; }
        .form-control:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.12); }
        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #ef4444; font-size: .78rem; margin-top: .25rem; }
        .form-hint { font-size: .75rem; color: #94a3b8; margin-top: .2rem; }
        .btn-submit { width: 100%; padding: .8rem; background: #0ea5e9; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: .5rem; }
        .btn-submit:hover { opacity: .9; }
        .error-box { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .login-link { text-align: center; font-size: .82rem; color: #64748b; margin-top: 1rem; }
        .login-link a { color: #0ea5e9; text-decoration: none; font-weight: 600; }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; padding-right: 2rem; }
    </style>
</head>
<body>
<div class="box">
    <div class="logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="logo-img">
        <div class="logo-sub">Create Customer Account</div>
    </div>

    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('customer.register.submit') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                value="{{ old('name') }}" placeholder="Your full name" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Mobile Number *</label>
            <input type="tel" name="mobile" class="form-control {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            <div class="form-hint">Bangladesh mobile format: 017XXXXXXXX (11 digits)</div>
            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                value="{{ old('email') }}" placeholder="your@email.com">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Address *</label>
            <textarea name="address" class="form-control {{ $errors->has('address') ? 'is-invalid' : '' }}"
                rows="2" placeholder="Your delivery address" required>{{ old('address') }}</textarea>
            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        @if($zones->count())
        <div class="form-group">
            <label class="form-label">Zone / Line</label>
            <select name="zone_id" class="form-control form-select">
                <option value="">— Select Zone (optional) —</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                        {{ $zone->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Customer Type *</label>
            <select name="customer_type" class="form-control form-select" required>
                <option value="residential" {{ old('customer_type', 'residential') === 'residential' ? 'selected' : '' }}>Residential</option>
                <option value="corporate"   {{ old('customer_type') === 'corporate' ? 'selected' : '' }}>Corporate</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                placeholder="Minimum 8 characters" required minlength="8">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirmation" class="form-control"
                placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn-submit">Send OTP & Continue</button>
    </form>

    <div class="login-link">Already have an account? <a href="{{ route('customer.login') }}">Login here</a></div>
</div>
</body>
</html>
