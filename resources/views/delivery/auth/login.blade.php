<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Staff Login — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .login-box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-icon { font-size: 2.5rem; }
        .logo-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-top: .25rem; }
        .logo-sub { font-size: .82rem; color: #64748b; margin-top: .2rem; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .form-control { width: 100%; padding: .7rem .9rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
        .btn-login { width: 100%; padding: .8rem; background: #0f172a; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: .5rem; }
        .btn-login:hover { background: #1e293b; }
        .error-box { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .hint { text-align: center; font-size: .78rem; color: #94a3b8; margin-top: 1.25rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">
            <div class="logo-icon">🚚</div>
            <div class="logo-title">Waterfall</div>
            <div class="logo-sub">Delivery Staff Portal</div>
        </div>

        @if($errors->any())
            <div class="error-box">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('delivery.login.submit') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="login">Email Address</label>
                <input type="text" id="login" name="login"
                    class="form-control"
                    value="{{ old('login') }}"
                    placeholder="your@email.com"
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

        <p class="hint">Delivery staff only. Contact admin for access.</p>
    </div>
</body>
</html>
