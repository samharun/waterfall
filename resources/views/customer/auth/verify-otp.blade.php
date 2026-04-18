<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .box { background: #fff; border-radius: 16px; padding: 2rem 1.75rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
        .logo { text-align: center; margin-bottom: 1.5rem; }
        .logo-img { width: 70px; height: auto; margin: 0 auto; }
        .logo-title { font-size: 1.3rem; font-weight: 700; color: #0ea5e9; margin-top: .3rem; display: none; }
        .mobile-display { text-align: center; background: #f0f9ff; border-radius: 8px; padding: .75rem; margin-bottom: 1.25rem; }
        .mobile-display .label { font-size: .78rem; color: #64748b; }
        .mobile-display .number { font-size: 1.1rem; font-weight: 700; color: #0ea5e9; letter-spacing: 1px; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .otp-input { width: 100%; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 1.8rem; font-weight: 700; text-align: center; letter-spacing: .5rem; }
        .otp-input:focus { outline: none; border-color: #0ea5e9; }
        .btn-verify { width: 100%; padding: .8rem; background: #0ea5e9; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; }
        .btn-verify:hover { opacity: .9; }
        .error-box { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .success-box { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .88rem; }
        .resend-section { text-align: center; margin-top: 1.25rem; }
        .resend-btn { background: none; border: none; color: #0ea5e9; font-size: .85rem; cursor: pointer; text-decoration: underline; }
        .expiry-note { font-size: .75rem; color: #94a3b8; text-align: center; margin-top: .5rem; }
        .back-link { text-align: center; font-size: .8rem; color: #94a3b8; margin-top: 1rem; }
        .back-link a { color: #0ea5e9; text-decoration: none; }
        @if(app()->environment('local'))
        .dev-hint { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: .6rem .9rem; margin-bottom: 1rem; font-size: .8rem; color: #92400e; }
        @endif
    </style>
</head>
<body>
<div class="box">
    <div class="logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="logo-img">
    </div>

    @if(app()->environment('local'))
        <div style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;padding:.6rem .9rem;margin-bottom:1rem;font-size:.8rem;color:#92400e;">
            🔧 <strong>Dev mode:</strong> Check <code>storage/logs/laravel.log</code> for OTP.
        </div>
    @endif

    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="success-box">{{ session('success') }}</div>
    @endif

    <div class="mobile-display">
        <div class="label">OTP sent to</div>
        <div class="number">
            @php
                $masked = substr($mobile, 0, 4) . '***' . substr($mobile, -4);
            @endphp
            {{ $masked }}
        </div>
    </div>

    <form method="POST" action="{{ route('customer.otp.verify.submit') }}">
        @csrf
        <input type="hidden" name="mobile" value="{{ $mobile }}">

        <div class="form-group">
            <label class="form-label">Enter 6-Digit OTP</label>
            <input type="text" name="otp" class="otp-input"
                placeholder="000000"
                maxlength="6"
                inputmode="numeric"
                pattern="[0-9]{6}"
                autofocus
                required>
        </div>

        <button type="submit" class="btn-verify">Verify OTP</button>
    </form>

    <div class="resend-section">
        <form method="POST" action="{{ route('customer.otp.resend') }}" style="display:inline;">
            @csrf
            <input type="hidden" name="mobile" value="{{ $mobile }}">
            <button type="submit" class="resend-btn">Resend OTP</button>
        </form>
        <div class="expiry-note">OTP expires in 5 minutes. Max 3 resends.</div>
    </div>

    <div class="back-link"><a href="{{ route('customer.register') }}">← Back to Registration</a></div>
</div>
</body>
</html>
