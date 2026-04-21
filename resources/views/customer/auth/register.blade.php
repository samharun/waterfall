<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('customer.register_page_title') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(125, 211, 252, 0.28), transparent 28%),
                linear-gradient(135deg, #0b5ed7 0%, #0ea5e9 52%, #0f766e 100%);
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1.5rem 1rem;
            color: #0f172a;
        }
        .box {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 24px;
            padding: 2rem 1.75rem;
            width: 100%;
            max-width: 470px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .2);
        }
        .logo { text-align: center; margin-bottom: 1.25rem; }
        .logo-img { width: 76px; height: auto; margin: 0 auto .6rem; display: block; }
        .logo-title { font-size: 1.5rem; font-weight: 800; color: #0b5ed7; }
        .logo-sub { font-size: .95rem; color: #64748b; margin-top: .35rem; line-height: 1.6; }
        .info-card {
            margin-bottom: 1.25rem;
            padding: .95rem 1rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #eff6ff 0%, #ecfeff 100%);
            border: 1px solid #bfdbfe;
        }
        .info-card strong { display: block; font-size: .92rem; color: #0f172a; margin-bottom: .3rem; }
        .info-card p { font-size: .83rem; color: #475569; line-height: 1.6; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .84rem; font-weight: 700; margin-bottom: .38rem; color: #334155; }
        .form-control {
            width: 100%;
            padding: .78rem .9rem;
            border: 1.5px solid #dbe4f0;
            border-radius: 12px;
            font-size: .96rem;
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
        }
        .form-control.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #dc2626; font-size: .78rem; margin-top: .3rem; }
        .form-hint { font-size: .77rem; color: #64748b; margin-top: .28rem; line-height: 1.5; }
        .password-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .9rem;
        }
        .btn-submit {
            width: 100%;
            padding: .92rem;
            background: linear-gradient(135deg, #0ea5e9 0%, #0b5ed7 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: .35rem;
            box-shadow: 0 16px 30px rgba(14, 165, 233, .22);
        }
        .btn-submit:hover { opacity: .96; }
        .error-box {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: .8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: .88rem;
        }
        .success-box {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            padding: .8rem 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: .88rem;
        }
        .login-link {
            text-align: center;
            font-size: .84rem;
            color: #64748b;
            margin-top: 1rem;
        }
        .login-link a {
            color: #0b5ed7;
            text-decoration: none;
            font-weight: 700;
        }
        @media (max-width: 575px) {
            .box { padding: 1.5rem 1rem; border-radius: 20px; }
            .password-grid { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
</head>
<body>
<div class="box">
    <div class="logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="logo-img">
        <div class="logo-title">{{ __('customer.register_heading') }}</div>
        <div class="logo-sub">{{ __('customer.register_subheading') }}</div>
    </div>


    @if(session('success'))
        <div class="success-box">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('customer.register.submit') }}">
        @csrf

        <div class="form-group">
            <label class="form-label">{{ __('customer.register_name_label') }}</label>
            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                value="{{ old('name') }}" placeholder="{{ __('customer.register_name_placeholder') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('customer.register_mobile_label') }}</label>
            <input type="tel" name="mobile" class="form-control {{ $errors->has('mobile') ? 'is-invalid' : '' }}"
                value="{{ old('mobile') }}" placeholder="01XXXXXXXXX" maxlength="11" required>
            <div class="form-hint">{{ __('customer.register_mobile_hint') }}</div>
            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="password-grid">
            <div class="form-group">
                <label class="form-label">{{ __('customer.register_password_label') }}</label>
                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="{{ __('customer.register_password_placeholder') }}" required minlength="6">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('customer.register_password_confirmation_label') }}</label>
                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="{{ __('customer.register_password_confirmation_placeholder') }}" required minlength="6">
            </div>
        </div>

        <button type="submit" class="btn-submit">{{ __('customer.register_submit') }}</button>
    </form>

    <div class="login-link">
        {{ __('customer.register_login_prompt') }} <a href="{{ route('customer.login') }}">{{ __('customer.register_login_link') }}</a>
    </div>
</div>
</body>
</html>
