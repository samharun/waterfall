<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Login — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-icon { font-size: 2.5rem; }
        .logo-title { font-size: 1.4rem; font-weight: 700; color: #1e3a5f; margin-top: .25rem; }
        .logo-sub { font-size: .82rem; color: #64748b; margin-top: .2rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .form-control { width: 100%; padding: .7rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #1e3a5f; box-shadow: 0 0 0 3px rgba(30,58,95,.12); }
        .btn-login { width: 100%; padding: .8rem; background: #1e3a5f; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: .5rem; }
        .btn-login:hover { background: #2d4f7c; }
        .error-box { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .hint { text-align: center; font-size: .78rem; color: #94a3b8; margin-top: 1.25rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <div class="logo-icon">🏪</div>
            <div class="logo-title">Waterfall</div>
            <div class="logo-sub">Dealer / Distributor Portal</div>
        </div>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('dealer.login.submit') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile"
                    class="form-control"
                    value="{{ old('mobile') }}"
                    placeholder="01XXXXXXXXX"
                    maxlength="11"
                    autofocus required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password"
                    class="form-control"
                    placeholder="Enter your password"
                    required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <p class="hint">Dealer accounts only. Contact Waterfall admin for access.</p>
        <p style="text-align:center;font-size:.8rem;margin-top:.75rem;color:#64748b;">
            New dealer? <a href="{{ route('dealer.register') }}" style="color:#1e3a5f;font-weight:600;">Register here</a>
        </p>
    </div>
</body>
</html>
