<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Waterfall') — {{ app()->getLocale() === 'bn' ? 'গ্রাহক পোর্টাল' : 'Customer Portal' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #0077B6;
            --primary-dark: #005f92;
            --primary-light: #e0f2fe;
            --secondary: #00B4D8;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --bg: #f0f7ff;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --text-light: #94a3b8;
        }

        body {
            font-family: {{ app()->getLocale() === 'bn' ? "'Hind Siliguri'," : "" }} 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ── Top Navigation ─────────────────────────────────────── */
        .nav {
            background: var(--primary);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,119,182,.3);
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }
        .nav-brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .nav-brand-name {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .nav-menu {
            display: flex;
            gap: 2px;
            align-items: center;
        }

        .nav-link {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .nav-link:hover { background: rgba(255,255,255,.15); color: #fff; }
        .nav-link.active { background: rgba(255,255,255,.2); color: #fff; font-weight: 600; }

        .lang-toggle { display: flex; gap: 3px; margin: 0 6px; }
        .lang-btn {
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            border: 1.5px solid rgba(255,255,255,.4);
            color: rgba(255,255,255,.8);
            transition: all .15s;
        }
        .lang-btn.active-lang { background: #fff; color: var(--primary); border-color: #fff; }
        .lang-btn:hover:not(.active-lang) { background: rgba(255,255,255,.15); }

        .nav-logout {
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }
        .nav-logout:hover { background: rgba(255,255,255,.25); }

        /* ── Main layout ─────────────────────────────────────────── */
        .main {
            max-width: 720px;
            margin: 0 auto;
            padding: 24px 16px 60px;
        }

        /* ── Page header ─────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .page-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.02em;
        }

        /* ── Cards ───────────────────────────────────────────────── */
        .card {
            background: var(--card);
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04);
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }
        .card-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ── Stat grid ───────────────────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }
        .stat-card {
            background: var(--card);
            border-radius: 14px;
            padding: 16px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04);
        }
        .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -.02em;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Badges ──────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .01em;
        }
        .badge-pending              { background: #fef9c3; color: #92400e; }
        .badge-confirmed            { background: #dbeafe; color: #1d4ed8; }
        .badge-assigned             { background: #e0e7ff; color: #3730a3; }
        .badge-delivered            { background: #dcfce7; color: #15803d; }
        .badge-paid                 { background: #dcfce7; color: #15803d; }
        .badge-cancelled            { background: #f3f4f6; color: #6b7280; }
        .badge-failed               { background: #fee2e2; color: #b91c1c; }
        .badge-unpaid               { background: #fee2e2; color: #b91c1c; }
        .badge-partial              { background: #fef9c3; color: #92400e; }
        .badge-issued               { background: #dbeafe; color: #1d4ed8; }
        .badge-draft                { background: #f3f4f6; color: #6b7280; }
        .badge-active               { background: #dcfce7; color: #15803d; }
        .badge-paused               { background: #fef9c3; color: #92400e; }
        .badge-in_progress          { background: #fef9c3; color: #92400e; }
        .badge-not_delivered        { background: #fee2e2; color: #b91c1c; }
        .badge-customer_unavailable { background: #fff7ed; color: #9a3412; }
        .badge-partial_delivered    { background: #d1fae5; color: #065f46; }

        /* ── Buttons ─────────────────────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all .15s;
            white-space: nowrap;
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 2px 6px rgba(0,119,182,.25); }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: transparent; border: 1.5px solid var(--primary); color: var(--primary); }
        .btn-outline:hover { background: var(--primary-light); }
        .btn-sm { padding: 5px 12px; font-size: 12px; border-radius: 7px; }
        .btn-block { display: flex; width: 100%; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-success { background: var(--success); color: #fff; }

        /* ── Order CTA button ────────────────────────────────────── */
        .btn-order {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            padding: 14px;
            border-radius: 14px;
            text-decoration: none;
            margin-bottom: 16px;
            box-shadow: 0 4px 16px rgba(0,119,182,.3);
            transition: opacity .15s, transform .1s;
        }
        .btn-order:hover { opacity: .95; transform: translateY(-1px); }

        /* ── List items ──────────────────────────────────────────── */
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 13px;
            gap: 12px;
        }
        .list-item:last-child { border-bottom: none; }
        .list-label { color: var(--text-muted); font-size: 12px; font-weight: 500; flex-shrink: 0; }
        .list-value { font-weight: 600; color: var(--text); text-align: right; }

        /* ── Order / Invoice cards ───────────────────────────────── */
        .order-card {
            background: var(--card);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 0 0 1px rgba(0,0,0,.04);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            transition: box-shadow .15s;
        }
        .order-card:hover { box-shadow: 0 4px 12px rgba(0,119,182,.1), 0 0 0 1px rgba(0,119,182,.1); }
        .order-no { font-family: monospace; font-size: 12px; color: var(--text-muted); font-weight: 600; }
        .order-total { font-size: 18px; font-weight: 800; color: var(--primary); }

        /* ── Forms ───────────────────────────────────────────────── */
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #374151; }
        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: 13px;
            font-family: inherit;
            background: #f9fafb;
            color: var(--text);
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,119,182,.12);
            background: #fff;
        }
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 28px;
        }
        .form-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ── Alerts ──────────────────────────────────────────────── */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .alert-danger  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-info    { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        /* ── Empty state ─────────────────────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }
        .empty-state-icon { font-size: 40px; margin-bottom: 12px; opacity: .5; }
        .empty-state-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .empty-state-text { font-size: 13px; color: var(--text-muted); }

        /* ── Pagination ──────────────────────────────────────────── */
        .pagination { display: flex; gap: 4px; justify-content: center; margin-top: 16px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text);
            transition: all .12s;
        }
        .pagination a:hover { background: var(--primary-light); border-color: var(--primary); color: var(--primary); }
        .pagination .active span { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* ── Section divider ─────────────────────────────────────── */
        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 20px 0 10px;
        }

        /* ── Scrollbar ───────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }

        @media (max-width: 480px) {
            .nav-link span { display: none; }
            .main { padding: 16px 12px 60px; }
        }
    </style>
</head>
<body>

@php $locale = app()->getLocale(); @endphp

<nav class="nav">
    <a href="{{ route('customer.dashboard') }}" class="nav-brand">
        <div class="nav-brand-logo">
            <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9-4.03-9-9-9Zm0 0c0 4.97-4.03 9-9 9m9-9c0 4.97 4.03 9 9 9"/>
            </svg>
        </div>
        <span class="nav-brand-name">Waterfall</span>
    </a>

    <div class="nav-menu">
        <a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
            <span>{{ __('customer.nav_home') }}</span>
        </a>
        <a href="{{ route('customer.orders.index') }}" class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}">
            <span>{{ __('customer.nav_orders') }}</span>
        </a>
        <a href="{{ route('customer.invoices.index') }}" class="nav-link {{ request()->routeIs('customer.invoices.*') ? 'active' : '' }}">
            <span>{{ __('customer.nav_bills') }}</span>
        </a>
        <a href="{{ route('customer.payments.index') }}" class="nav-link {{ request()->routeIs('customer.payments.*') ? 'active' : '' }}">
            <span>{{ __('customer.nav_payments') }}</span>
        </a>
        <a href="{{ route('customer.subscription.show') }}" class="nav-link {{ request()->routeIs('customer.subscription.*') ? 'active' : '' }}">
            <span>{{ __('customer.nav_subscription') }}</span>
        </a>
        <a href="{{ route('customer.profile') }}" class="nav-link {{ request()->routeIs('customer.profile*') ? 'active' : '' }}">
            <span>{{ __('customer.nav_profile') }}</span>
        </a>

        <div class="lang-toggle">
            <a href="{{ route('locale.switch', 'bn') }}" class="lang-btn {{ $locale === 'bn' ? 'active-lang' : '' }}">বাং</a>
            <a href="{{ route('locale.switch', 'en') }}" class="lang-btn {{ $locale === 'en' ? 'active-lang' : '' }}">EN</a>
        </div>

        <form method="POST" action="{{ route('customer.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="nav-logout">{{ __('customer.nav_logout') }}</button>
        </form>
    </div>
</nav>

<main class="main">
    @if(session('success'))
        <div class="alert alert-success">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
