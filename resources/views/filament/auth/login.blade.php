<x-filament-panels::page.simple>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

        :root { color-scheme: light; }
        html, body, .fi-simple-layout, .fi-simple-page, .fi-simple-main { min-height: 100% !important; }
        body { margin: 0 !important; background: #f9fafb !important; font-family: Outfit, ui-sans-serif, system-ui, sans-serif !important; }
        .fi-simple-page { display: block !important; padding: 0 !important; background: transparent !important; }
        .fi-simple-main { width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; border: 0 !important; border-radius: 0 !important; background: transparent !important; box-shadow: none !important; }
        .fi-simple-header, .fi-simple-header-heading, .fi-simple-header-subheading { display: none !important; }

        .ta-auth { display: grid; min-height: 100vh; grid-template-columns: minmax(0, 1fr) minmax(460px, 42%); background: #fff; }
        .ta-auth-visual { position: relative; display: flex; overflow: hidden; flex-direction: column; justify-content: space-between; background: #1a2231; padding: 54px 64px; color: #fff; }
        .ta-auth-visual::before { position: absolute; top: -170px; right: -150px; width: 520px; height: 520px; border: 1px solid rgba(117,146,255,.2); border-radius: 50%; content: ''; box-shadow: 0 0 0 80px rgba(70,95,255,.05), 0 0 0 160px rgba(70,95,255,.035); }
        .ta-auth-visual::after { position: absolute; bottom: -180px; left: -150px; width: 430px; height: 430px; border-radius: 50%; background: radial-gradient(circle, rgba(70,95,255,.28), rgba(70,95,255,0) 68%); content: ''; }
        .ta-brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 13px; }
        .ta-brand img { width: 48px; height: 48px; object-fit: contain; }
        .ta-brand strong { display: block; font-size: 21px; line-height: 1.1; }
        .ta-brand span { display: block; margin-top: 4px; color: #98a2b3; font-size: 11px; font-weight: 500; letter-spacing: .1em; text-transform: uppercase; }
        .ta-visual-copy { position: relative; z-index: 1; max-width: 560px; padding: 80px 0; }
        .ta-visual-kicker { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; border: 1px solid rgba(117,146,255,.28); border-radius: 999px; background: rgba(70,95,255,.12); padding: 7px 12px; color: #9cb9ff; font-size: 12px; font-weight: 600; }
        .ta-visual-kicker::before { width: 7px; height: 7px; border-radius: 50%; background: #7592ff; content: ''; box-shadow: 0 0 0 4px rgba(117,146,255,.12); }
        .ta-visual-copy h1 { max-width: 520px; margin: 0; font-size: clamp(36px, 4vw, 58px); font-weight: 600; letter-spacing: -.04em; line-height: 1.08; }
        .ta-visual-copy p { max-width: 500px; margin: 22px 0 0; color: #98a2b3; font-size: 16px; line-height: 1.7; }
        .ta-copyright { position: relative; z-index: 1; color: #667085; font-size: 12px; }

        .ta-auth-form-side { display: flex; align-items: center; justify-content: center; background: #fff; padding: 48px; }
        .ta-login-card { width: 100%; max-width: 410px; }
        .ta-mobile-brand { display: none; margin-bottom: 34px; align-items: center; gap: 10px; color: #101828; }
        .ta-mobile-brand img { width: 42px; height: 42px; object-fit: contain; }
        .ta-mobile-brand strong { font-size: 18px; }
        .ta-login-heading { margin-bottom: 32px; }
        .ta-login-heading h2 { margin: 0; color: #101828; font-size: 30px; font-weight: 600; letter-spacing: -.03em; }
        .ta-login-heading p { margin: 8px 0 0; color: #667085; font-size: 14px; }

        .ta-login-card .fi-fo-field-wrp { gap: 6px !important; }
        .ta-login-card .fi-fo-field-wrp-label span { color: #344054 !important; font-size: 13px !important; font-weight: 500 !important; }
        .ta-login-card .fi-input-wrp { min-height: 46px !important; border: 1px solid #d0d5dd !important; border-radius: 8px !important; background: #fff !important; box-shadow: 0 1px 2px rgba(16,24,40,.05) !important; transition: border-color .15s, box-shadow .15s !important; }
        .ta-login-card .fi-input-wrp:focus-within { border-color: #7592ff !important; box-shadow: 0 0 0 4px rgba(70,95,255,.12) !important; }
        .ta-login-card .fi-input-wrp input, .ta-login-card input { color: #101828 !important; background: transparent !important; font-size: 14px !important; }
        .ta-login-card input::placeholder { color: #98a2b3 !important; }
        .ta-login-card .fi-checkbox-input { color: #465fff !important; }
        .ta-login-card .fi-btn { min-height: 46px !important; border-radius: 8px !important; background: #465fff !important; color: #fff !important; font-size: 14px !important; font-weight: 600 !important; box-shadow: 0 1px 2px rgba(16,24,40,.1) !important; transition: background .15s, transform .05s !important; }
        .ta-login-card .fi-btn:hover { background: #3641f5 !important; }
        .ta-login-card .fi-btn:active { transform: translateY(1px); }
        .ta-login-card .fi-link { color: #465fff !important; }
        .ta-login-card .fi-fo-field-wrp-error-message { color: #b42318 !important; font-size: 12px !important; }
        .ta-login-card .fi-section, .ta-login-card form { gap: 22px !important; }
        .ta-security { display: flex; align-items: flex-start; gap: 10px; margin-top: 28px; border: 1px solid #e4e7ec; border-radius: 10px; background: #f9fafb; padding: 13px 14px; color: #667085; font-size: 12px; line-height: 1.5; }
        .ta-security svg { flex: 0 0 auto; margin-top: 1px; color: #465fff; }

        @media (max-width: 960px) {
            .ta-auth { grid-template-columns: 1fr; }
            .ta-auth-visual { display: none; }
            .ta-auth-form-side { min-height: 100vh; padding: 36px 24px; }
            .ta-mobile-brand { display: flex; }
        }
        @media (max-width: 480px) { .ta-auth-form-side { align-items: flex-start; padding: 28px 18px; } .ta-login-heading h2 { font-size: 26px; } }
    </style>

    <div class="ta-auth">
        <section class="ta-auth-visual" aria-hidden="true">
            <div class="ta-brand">
                <img src="{{ asset('images/waterfall-logo.png') }}" alt="">
                <div><strong>Waterfall</strong><span>Management system</span></div>
            </div>
            <div class="ta-visual-copy">
                <div class="ta-visual-kicker">Secure back-office access</div>
                <h1>Manage every delivery from one clear dashboard.</h1>
                <p>Customers, dealers, orders, inventory, billing, and field operations—all organized in the Waterfall admin console.</p>
            </div>
            <div class="ta-copyright">&copy; {{ date('Y') }} Waterfall. Authorized personnel only.</div>
        </section>

        <section class="ta-auth-form-side">
            <div class="ta-login-card">
                <div class="ta-mobile-brand">
                    <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall">
                    <strong>Waterfall Admin</strong>
                </div>
                <div class="ta-login-heading">
                    <h2>Welcome back</h2>
                    <p>Enter your credentials to access the admin console.</p>
                </div>

                {{ $this->content }}

                <div class="ta-security">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 5 6v5c0 4.55 2.92 8.8 7 10 4.08-1.2 7-5.45 7-10V6l-7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9.5 12 1.6 1.6 3.6-3.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>This area is restricted to authorized administrators. Login attempts may be recorded for security.</span>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
