<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dealer Portal') — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; color: #1a202c; min-height: 100vh; }
        .nav { background: #1e3a5f; color: #fff; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 56px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.2); overflow-x: auto; }
        .nav-brand { font-weight: 700; font-size: 1rem; color: #7dd3fc; text-decoration: none; white-space: nowrap; margin-right: .5rem; }
        .nav-menu { display: flex; gap: .2rem; align-items: center; flex-shrink: 0; }
        .nav-link { color: rgba(255,255,255,.85); text-decoration: none; padding: .35rem .5rem; border-radius: 6px; font-size: .78rem; white-space: nowrap; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        .nav-logout { background: rgba(239,68,68,.2); border: 1px solid rgba(239,68,68,.4); color: #fca5a5; padding: .3rem .6rem; border-radius: 6px; font-size: .78rem; cursor: pointer; white-space: nowrap; }
        .nav-logout:hover { background: rgba(239,68,68,.35); }

        /* Logo */
        .nav-logo { display: flex; align-items: center; text-decoration: none; color: #7dd3fc; }
        .nav-logo-img { width: 40px; height: auto; }

        .main { max-width: 680px; margin: 0 auto; padding: 1.25rem 1rem 4rem; }
        .card { background: #fff; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-title { font-size: .95rem; font-weight: 700; margin-bottom: .75rem; color: #1e3a5f; }
        .stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-bottom: 1rem; }
        .stat-card { background: #fff; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .stat-value { font-size: 1.5rem; font-weight: 700; color: #1e3a5f; }
        .stat-label { font-size: .75rem; color: #64748b; margin-top: .2rem; }
        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-confirmed, .badge-assigned { background: #dbeafe; color: #1e40af; }
        .badge-delivered, .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-cancelled, .badge-failed, .badge-unpaid { background: #fee2e2; color: #991b1b; }
        .badge-partial   { background: #fef3c7; color: #92400e; }
        .badge-issued    { background: #e0f2fe; color: #0369a1; }
        .badge-draft     { background: #f1f5f9; color: #475569; }
        .btn { display: inline-block; padding: .55rem 1rem; border-radius: 8px; font-size: .88rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: opacity .15s; }
        .btn-primary { background: #1e3a5f; color: #fff; }
        .btn-primary:hover { opacity: .88; }
        .btn-outline { background: transparent; border: 1.5px solid #1e3a5f; color: #1e3a5f; }
        .btn-sm { padding: .3rem .65rem; font-size: .78rem; }
        .btn-block { display: block; width: 100%; text-align: center; padding: .8rem; font-size: .95rem; }
        .btn-order { background: #1e3a5f; color: #fff; font-size: 1.05rem; padding: .9rem; border-radius: 12px; display: block; text-align: center; font-weight: 700; text-decoration: none; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(30,58,95,.25); }
        .btn-order:hover { opacity: .9; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }
        .form-control { width: 100%; padding: .65rem .85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem; }
        .form-control:focus { outline: none; border-color: #1e3a5f; }
        .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .75rem center; padding-right: 2rem; }
        .alert { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .list-item { display: flex; justify-content: space-between; align-items: flex-start; padding: .65rem 0; border-bottom: 1px solid #f1f5f9; font-size: .88rem; gap: .5rem; }
        .list-item:last-child { border-bottom: none; }
        .list-label { color: #64748b; font-size: .8rem; flex-shrink: 0; }
        .list-value { font-weight: 600; text-align: right; }
        .order-card { background: #fff; border-radius: 12px; padding: 1rem; margin-bottom: .75rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
        .order-no { font-family: monospace; font-size: .82rem; color: #64748b; }
        .order-total { font-size: 1.05rem; font-weight: 700; color: #1e3a5f; }
        .page-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; }
        .pagination { display: flex; gap: .5rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .4rem .75rem; border-radius: 6px; font-size: .82rem; text-decoration: none; background: #fff; border: 1px solid #e2e8f0; color: #374151; }
        .pagination .active span { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
        .price-custom { color: #059669; font-weight: 700; }
        .price-default { color: #64748b; }
    </style>
</head>
<body>
<nav class="nav">
    <a href="{{ route('dealer.dashboard') }}" class="nav-logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="nav-logo-img">
    </a>
    <div class="nav-menu">
        <a href="{{ route('dealer.dashboard') }}" class="nav-link {{ request()->routeIs('dealer.dashboard') ? 'active' : '' }}">Home</a>
        <a href="{{ route('dealer.products.index') }}" class="nav-link {{ request()->routeIs('dealer.products.*') ? 'active' : '' }}">Prices</a>
        <a href="{{ route('dealer.orders.index') }}" class="nav-link {{ request()->routeIs('dealer.orders.*') ? 'active' : '' }}">Orders</a>
        <a href="{{ route('dealer.invoices.index') }}" class="nav-link {{ request()->routeIs('dealer.invoices.*') ? 'active' : '' }}">Bills</a>
        <a href="{{ route('dealer.payments.index') }}" class="nav-link {{ request()->routeIs('dealer.payments.*') ? 'active' : '' }}">Payments</a>
        <a href="{{ route('dealer.profile') }}" class="nav-link {{ request()->routeIs('dealer.profile') ? 'active' : '' }}">Profile</a>
        <form method="POST" action="{{ route('dealer.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="nav-logout">Logout</button>
        </form>
    </div>
</nav>
<main class="main">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
