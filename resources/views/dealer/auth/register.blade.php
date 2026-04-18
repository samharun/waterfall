<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Registration — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); min-height: 100vh; display: flex; align-items: flex-start; justify-content: center; padding: 1.5rem 1rem; }
        .box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-icon { font-size: 2rem; }
        .logo-title { font-size: 1.3rem; font-weight: 700; color: #1e3a5f; }
        .logo-sub { font-size: .8rem; color: #64748b; }
        .form-group { margin-bottom: .9rem; }
        .form-label { display: block; font-size: .83rem; font-weight: 600; margin-bottom: .3rem; color: #374151; }
        .form-control { width: 100%; padding: .65rem .85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem; }
        .form-control:focus { outline: none; border-color: #1e3a5f; }
        .form-hint { font-size: .75rem; color: #94a3b8; margin-top: .2rem; }
        .invalid-feedback { color: #ef4444; font-size: .78rem; margin-top: .25rem; }
        .btn-submit { width: 100%; padding: .8rem; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: .5rem; }
        .btn-submit:hover { opacity: .9; }
        .error-box { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .login-link { text-align: center; font-size: .82rem; color: #64748b; margin-top: 1rem; }
        .login-link a { color: #1e3a5f; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
<div class="box">
    <div class="logo">
        <div class="logo-icon">🏪</div>
        <div class="logo-title">Waterfall</div>
        <div class="logo-sub">Dealer / Distributor Registration</div>
    </div>

    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('dealer.register.submit') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">Business / Full Name *</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required maxlength="255">
        </div>

        <div class="form-group">
            <label class="form-label">Mobile Number *</label>
            <input type="tel" name="mobile" class="form-control" value="{{ old('mobile') }}"
                placeholder="01XXXXXXXXX" maxlength="11" required>
            <div class="form-hint">Bangladesh mobile format: 017XXXXXXXX</div>
        </div>

        <div class="form-group">
            <label class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="your@email.com">
        </div>

        <div class="form-group">
            <label class="form-label">Business Address *</label>
            <textarea name="address" class="form-control" rows="2" required maxlength="500">{{ old('address') }}</textarea>
        </div>

        @if($zones->count())
        <div class="form-group">
            <label class="form-label">Zone / Area</label>
            <select name="zone_id" class="form-control">
                <option value="">— Select Zone (optional) —</option>
                @foreach($zones as $zone)
                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>{{ $zone->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-group">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
        </div>

        <div class="form-group">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat your password" required>
        </div>

        <button type="submit" class="btn-submit">Send OTP & Continue</button>
    </form>

    <div class="login-link">Already registered? <a href="{{ route('dealer.login') }}">Login here</a></div>
</div>
</body>
</html>
