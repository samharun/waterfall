<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Pending — Waterfall Dealer</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .box { background: #fff; border-radius: 16px; padding: 2.5rem 2rem; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.3); text-align: center; }
        .icon { font-size: 3.5rem; margin-bottom: 1rem; }
        .title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .75rem; }
        .message { font-size: .92rem; color: #475569; line-height: 1.6; margin-bottom: 1.5rem; }
        .highlight { background: #f0f4f8; border-radius: 8px; padding: .75rem 1rem; font-size: .88rem; color: #1e3a5f; margin-bottom: 1.5rem; border: 1px solid #cbd5e1; }
        .btn { display: inline-block; padding: .7rem 1.5rem; background: #1e3a5f; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: .95rem; }
        .support-note { font-size: .78rem; color: #94a3b8; margin-top: 1rem; }
    </style>
</head>
<body>
<div class="box">
    <div class="icon">⏳</div>
    <div class="title">Dealer Registration Submitted!</div>
    <div class="message">Your mobile number has been verified. Your dealer account is now <strong>pending admin approval</strong>.</div>
    <div class="highlight">You will be able to login after Waterfall admin reviews and approves your account.</div>
    <a href="{{ route('dealer.login') }}" class="btn">Go to Login</a>
    <div class="support-note">Need help? Contact Waterfall support.</div>
</div>
</body>
</html>
