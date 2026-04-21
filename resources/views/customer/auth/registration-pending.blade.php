<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('customer.registration_pending_page_title') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .box { background: #fff; border-radius: 16px; padding: 2.5rem 2rem; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.2); text-align: center; }
        .logo-img { width: 70px; height: auto; margin: 0 auto 1.5rem; }
        .icon { font-size: 3.5rem; margin-bottom: 1rem; }
        .title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .75rem; }
        .message { font-size: .92rem; color: #475569; line-height: 1.6; margin-bottom: 1.5rem; }
        .highlight { background: #f0f9ff; border-radius: 8px; padding: .75rem 1rem; font-size: .88rem; color: #0369a1; margin-bottom: 1.5rem; border: 1px solid #bae6fd; }
        .btn { display: inline-block; padding: .7rem 1.5rem; background: #0ea5e9; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: .95rem; }
        .btn:hover { opacity: .9; }
        .support-note { font-size: .78rem; color: #94a3b8; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="box">
    <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="logo-img">
    <div class="icon">&#9203;</div>
    <div class="title">{{ __('customer.registration_pending_title') }}</div>
    <div class="message">
        {{ __('customer.registration_pending_message') }}
    </div>
    <div class="highlight">
        {{ __('customer.registration_pending_highlight') }}
    </div>
    <a href="{{ route('customer.login') }}" class="btn">{{ __('customer.registration_pending_login') }}</a>
    <div class="support-note">{{ __('customer.registration_pending_support') }}</div>
</div>
</body>
</html>
