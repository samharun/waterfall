<x-filament-panels::page.simple>
    <style>
        body { background: #0077B6 !important; }

        .wf-login-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: linear-gradient(135deg, #0077B6 0%, #005f92 50%, #003d5c 100%);
        }

        .wf-login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 20px;
            background: #ffffff;
            padding: 36px 32px;
            box-shadow: 0 24px 64px rgba(0,0,0,.25);
        }

        .wf-login-brand {
            text-align: center;
            margin-bottom: 28px;
        }

        .wf-login-logo {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, #0077B6, #00B4D8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            box-shadow: 0 4px 16px rgba(0,119,182,.35);
        }

        .wf-login-logo svg { color: #fff; }

        .wf-login-brand-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.02em;
            margin-bottom: 4px;
        }

        .wf-login-brand-subtitle {
            color: #64748b;
            font-size: 13px;
        }

        /* Input fields */
        .wf-login-card .fi-fo-field-wrp-label span { color: #374151 !important; font-size: 12px !important; font-weight: 600 !important; }
        .wf-login-card .fi-input-wrp input,
        .wf-login-card input {
            color: #111827 !important;
            background: #f9fafb !important;
            border-color: #d1d5db !important;
            border-radius: 8px !important;
            font-size: 13px !important;
        }
        .wf-login-card input:focus {
            border-color: #0077B6 !important;
            box-shadow: 0 0 0 3px rgba(0,119,182,.12) !important;
            background: #fff !important;
        }
        .wf-login-card input::placeholder { color: #9ca3af !important; }
        .wf-login-card input:-webkit-autofill,
        .wf-login-card input:-webkit-autofill:focus {
            -webkit-text-fill-color: #111827;
            -webkit-box-shadow: 0 0 0 1000px #f9fafb inset;
        }

        /* Login button */
        .wf-login-card .fi-btn {
            min-height: 44px !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            background: #0077B6 !important;
            border-color: #0077B6 !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(0,119,182,.3) !important;
            transition: all .15s !important;
        }
        .wf-login-card .fi-btn:hover {
            background: #005f92 !important;
            border-color: #005f92 !important;
            box-shadow: 0 4px 12px rgba(0,119,182,.4) !important;
        }

        .wf-login-hint {
            margin-top: 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
        }

        /* Divider */
        .fi-simple-page { padding: 0 !important; background: transparent !important; }
        .fi-simple-main { padding: 0 !important; background: transparent !important; }
    </style>

    <div class="wf-login-shell">
        <div class="wf-login-card">
            <div class="wf-login-brand">
                <div class="wf-login-logo">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9Zm0 0c0 4.97-4.03 9-9 9m9-9c0 4.97 4.03 9 9 9"/>
                    </svg>
                </div>
                <div class="wf-login-brand-title">Waterfall Admin</div>
                <div class="wf-login-brand-subtitle">Sign in to your back office account</div>
            </div>

            {{ $this->content }}

            <div class="wf-login-hint">
                Admin access only &nbsp;·&nbsp; Contact system administrator for credentials.
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
