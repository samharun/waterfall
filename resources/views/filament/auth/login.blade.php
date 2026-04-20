<x-filament-panels::page.simple>
    <style>
        .wf-login-shell {
            min-height: calc(100vh - 2rem);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .wf-login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 18px;
            background: #ffffff;
            padding: 2rem 1.75rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .wf-login-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .wf-login-brand img {
            width: 88px;
            height: auto;
            margin: 0 auto;
        }

        .wf-login-brand-title {
            margin-top: 0.5rem;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            letter-spacing: -0.02em;
        }

        .wf-login-brand-subtitle {
            margin-top: 0.35rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .wf-login-card .fi-fo-field-wrp-label span,
        .wf-login-card .fi-fo-checkbox-list-option-label,
        .wf-login-card .fi-checkbox-label,
        .wf-login-card .fi-input-wrp input,
        .wf-login-card input {
            color: #111827 !important;
            -webkit-text-fill-color: #111827;
        }

        .wf-login-card .fi-input-wrp,
        .wf-login-card .fi-input,
        .wf-login-card input {
            background: #ffffff !important;
        }

        .wf-login-card input::placeholder {
            color: #9ca3af !important;
            opacity: 1;
        }

        .wf-login-card .fi-input-wrp input:-webkit-autofill,
        .wf-login-card .fi-input-wrp input:-webkit-autofill:hover,
        .wf-login-card .fi-input-wrp input:-webkit-autofill:focus {
            -webkit-text-fill-color: #111827;
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
            box-shadow: 0 0 0 1000px #ffffff inset;
        }

        .wf-login-card .fi-btn {
            min-height: 3rem;
        }

        .wf-login-hint {
            margin-top: 1.25rem;
            text-align: center;
            color: #94a3b8;
            font-size: 0.875rem;
        }
    </style>

    <div class="wf-login-shell">
        <div class="wf-login-card">
            <div class="wf-login-brand">
                <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo">
                <div class="wf-login-brand-title">Admin Sign in</div>
                <div class="wf-login-brand-subtitle">Use your back office account to continue.</div>
            </div>

            {{ $this->content }}

            <div class="wf-login-hint">
                Admin access only. Contact system administrator for credentials.
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
