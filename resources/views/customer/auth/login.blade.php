<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-img { width: 80px; height: auto; margin: 0 auto; }
        .logo-title { font-size: 1.4rem; font-weight: 700; color: #0ea5e9; margin-top: .5rem; display: none; }
        .logo-sub { font-size: .85rem; color: #64748b; margin-top: .2rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .form-control { width: 100%; padding: .7rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: border-color .15s; }
        .form-control:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #ef4444; font-size: .82rem; margin-top: .3rem; }
        .btn-login { width: 100%; padding: .8rem; background: #0ea5e9; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: .5rem; transition: opacity .15s; }
        .btn-login:hover { opacity: .9; }
        .hint { text-align: center; font-size: .8rem; color: #94a3b8; margin-top: 1.25rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="logo-img">
            <div class="logo-sub">Customer Portal</div>
        </div>

        @if($errors->any())
            <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('customer.login.submit') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="mobile">Mobile Number</label>
                <input
                    type="tel"
                    id="mobile"
                    name="mobile"
                    class="form-control {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                    value="{{ old('mobile') }}"
                    placeholder="01XXXXXXXXX"
                    maxlength="11"
                    autofocus
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <p class="hint">Having trouble? Contact Waterfall support.</p>
    </div>
</body>
</html>
