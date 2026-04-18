<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Waterfall') — {{ app()->getLocale() === 'bn' ? 'গ্রাহক পোর্টাল' : 'Customer Portal' }}</title>
    {{-- Bangla font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: {{ app()->getLocale() === 'bn' ? "'Hind Siliguri', " : "" }}-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4f8; color: #1a202c; min-height: 100vh;
        }

        /* Nav */
        .nav { background: #0ea5e9; color: #fff; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 56px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.15); }
        .nav-brand { font-weight: 700; font-size: 1.1rem; letter-spacing: .5px; text-decoration: none; color: #fff; }
        .nav-menu { display: flex; gap: .25rem; align-items: center; }
        .nav-link { color: rgba(255,255,255,.9); text-decoration: none; padding: .4rem .6rem; border-radius: 6px; font-size: .85rem; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,.2); color: #fff; }
        .nav-logout { background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3); color: #fff; padding: .35rem .75rem; border-radius: 6px; font-size: .85rem; cursor: pointer; }
        .nav-logout:hover { background: rgba(255,255,255,.25); }

        /* Language toggle */
        .lang-toggle { display: flex; gap: .2rem; align-items: center; }
        .lang-btn { padding: .25rem .55rem; border-radius: 5px; font-size: .78rem; font-weight: 700; text-decoration: none; border: 1.5px solid rgba(255,255,255,.5); color: rgba(255,255,255,.85); transition: all .15s; }
        .lang-btn.active-lang { background: #fff; color: #0ea5e9; border-color: #fff; }
        .lang-btn:hover:not(.active-lang) { background: rgba(255,255,255,.2); }

        /* Logo */
        .nav-logo { display: flex; align-items: center; text-decoration: none; color: #fff; }
        .nav-logo-img { width: 42px; height: auto; }

        /* Main */
        .main { max-width: 680px; margin: 0 auto; padding: 1.25rem 1rem 4rem; }

        /* Cards */
        .card { background: #fff; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-title { font-size: 1rem; font-weight: 600; margin-bottom: .75rem; color: #0ea5e9; }

        /* Stat grid */
        .stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-bottom: 1rem; }
        .stat-card { background: #fff; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #0ea5e9; }
        .stat-label { font-size: .78rem; color: #64748b; margin-top: .2rem; }

        /* Badges */
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-pending  { background: #fef3c7; color: #92400e; }
        .badge-confirmed, .badge-assigned { background: #dbeafe; color: #1e40af; }
        .badge-delivered, .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-cancelled, .badge-failed, .badge-unpaid { background: #fee2e2; color: #991b1b; }
        .badge-partial  { background: #fef3c7; color: #92400e; }
        .badge-issued   { background: #e0f2fe; color: #0369a1; }
        .badge-draft    { background: #f1f5f9; color: #475569; }

        /* Buttons */
        .btn { display: inline-block; padding: .6rem 1.25rem; border-radius: 8px; font-size: .9rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: opacity .15s; }
        .btn-primary { background: #0ea5e9; color: #fff; }
        .btn-primary:hover { opacity: .9; }
        .btn-outline { background: transparent; border: 1.5px solid #0ea5e9; color: #0ea5e9; }
        .btn-sm { padding: .35rem .75rem; font-size: .8rem; }
        .btn-block { display: block; width: 100%; text-align: center; }
        .btn-order { background: #0ea5e9; color: #fff; font-size: 1.1rem; padding: .9rem; border-radius: 12px; display: block; text-align: center; font-weight: 700; text-decoration: none; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(14,165,233,.3); }
        .btn-order:hover { opacity: .9; }

        /* Forms */
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .form-control { width: 100%; padding: .65rem .85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem; transition: border-color .15s; font-family: inherit; }
        .form-control:focus { outline: none; border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.15); }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; padding-right: 2rem; }

        /* Alerts */
        .alert { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }

        /* Table-like list */
        .list-item { display: flex; justify-content: space-between; align-items: center; padding: .75rem 0; border-bottom: 1px solid #f1f5f9; font-size: .9rem; }
        .list-item:last-child { border-bottom: none; }
        .list-label { color: #64748b; font-size: .82rem; }
        .list-value { font-weight: 600; }

        /* Order card */
        .order-card { background: #fff; border-radius: 12px; padding: 1rem; margin-bottom: .75rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
        .order-no { font-family: monospace; font-size: .85rem; color: #64748b; }
        .order-total { font-size: 1.1rem; font-weight: 700; color: #0ea5e9; }

        /* Page title */
        .page-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; }

        /* Pagination */
        .pagination { display: flex; gap: .5rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .4rem .75rem; border-radius: 6px; font-size: .85rem; text-decoration: none; background: #fff; border: 1px solid #e2e8f0; color: #374151; }
        .pagination .active span { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }

        @media (max-width: 480px) {
            .nav-link span { display: none; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

@php $locale = app()->getLocale(); @endphp

<nav class="nav">
    <a href="{{ route('customer.dashboard') }}" class="nav-logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="nav-logo-img">
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

        {{-- Language toggle --}}
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
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>
