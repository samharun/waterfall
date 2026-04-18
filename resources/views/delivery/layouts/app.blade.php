<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Delivery') — Waterfall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; color: #1a202c; min-height: 100vh; }

        .nav { background: #0f172a; color: #fff; padding: 0 1rem; display: flex; align-items: center; justify-content: space-between; height: 56px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
        .nav-brand { font-weight: 700; font-size: 1rem; color: #38bdf8; text-decoration: none; }
        .nav-menu { display: flex; gap: .25rem; align-items: center; }
        .nav-link { color: rgba(255,255,255,.8); text-decoration: none; padding: .4rem .6rem; border-radius: 6px; font-size: .82rem; transition: background .15s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        .nav-logout { background: rgba(239,68,68,.2); border: 1px solid rgba(239,68,68,.4); color: #fca5a5; padding: .35rem .75rem; border-radius: 6px; font-size: .82rem; cursor: pointer; }
        .nav-logout:hover { background: rgba(239,68,68,.35); }

        /* Logo */
        .nav-logo { display: flex; align-items: center; text-decoration: none; color: #38bdf8; }
        .nav-logo-img { width: 40px; height: auto; }

        .main { max-width: 680px; margin: 0 auto; padding: 1.25rem 1rem 4rem; }

        .card { background: #fff; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .card-title { font-size: .95rem; font-weight: 700; margin-bottom: .75rem; color: #0f172a; }

        .stat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-bottom: 1rem; }
        .stat-card { background: #fff; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .stat-value { font-size: 1.6rem; font-weight: 700; color: #0ea5e9; }
        .stat-label { font-size: .75rem; color: #64748b; margin-top: .2rem; }

        .badge { display: inline-block; padding: .2rem .55rem; border-radius: 20px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
        .badge-pending    { background: #fef3c7; color: #92400e; }
        .badge-assigned   { background: #dbeafe; color: #1e40af; }
        .badge-in_progress{ background: #e0f2fe; color: #0369a1; }
        .badge-delivered  { background: #d1fae5; color: #065f46; }
        .badge-failed     { background: #fee2e2; color: #991b1b; }
        .badge-cancelled  { background: #f1f5f9; color: #475569; }
        .badge-unpaid     { background: #fee2e2; color: #991b1b; }
        .badge-paid       { background: #d1fae5; color: #065f46; }
        .badge-partial    { background: #fef3c7; color: #92400e; }

        .btn { display: inline-block; padding: .55rem 1rem; border-radius: 8px; font-size: .85rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: opacity .15s; }
        .btn-primary  { background: #0ea5e9; color: #fff; }
        .btn-success  { background: #10b981; color: #fff; }
        .btn-warning  { background: #f59e0b; color: #fff; }
        .btn-danger   { background: #ef4444; color: #fff; }
        .btn-outline  { background: transparent; border: 1.5px solid #0ea5e9; color: #0ea5e9; }
        .btn-sm       { padding: .3rem .65rem; font-size: .78rem; }
        .btn-block    { display: block; width: 100%; text-align: center; padding: .75rem; font-size: .95rem; }
        .btn:hover    { opacity: .88; }

        .delivery-card { background: #fff; border-radius: 12px; padding: 1rem; margin-bottom: .75rem; box-shadow: 0 1px 4px rgba(0,0,0,.08); border-left: 4px solid #0ea5e9; }
        .delivery-card.status-delivered { border-left-color: #10b981; opacity: .75; }
        .delivery-card.status-failed    { border-left-color: #ef4444; }
        .delivery-card.status-in_progress { border-left-color: #f59e0b; }

        .party-name   { font-size: 1rem; font-weight: 700; color: #0f172a; }
        .party-mobile { font-size: .85rem; color: #0ea5e9; font-weight: 600; }
        .party-addr   { font-size: .82rem; color: #64748b; margin-top: .2rem; }
        .del-no       { font-family: monospace; font-size: .78rem; color: #94a3b8; }

        .action-row { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .75rem; }

        .form-control { width: 100%; padding: .65rem .85rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: .95rem; }
        .form-control:focus { outline: none; border-color: #0ea5e9; }
        .form-group { margin-bottom: 1rem; }
        .form-label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; color: #374151; }

        .alert { padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .list-item { display: flex; justify-content: space-between; align-items: flex-start; padding: .6rem 0; border-bottom: 1px solid #f1f5f9; font-size: .88rem; gap: .5rem; }
        .list-item:last-child { border-bottom: none; }
        .list-label { color: #64748b; font-size: .8rem; flex-shrink: 0; }
        .list-value { font-weight: 600; text-align: right; }

        .page-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; color: #0f172a; }

        .checkbox-wrap { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
        .checkbox-wrap input[type=checkbox] { width: 18px; height: 18px; cursor: pointer; }

        .pagination { display: flex; gap: .5rem; justify-content: center; margin-top: 1rem; flex-wrap: wrap; }
        .pagination a, .pagination span { padding: .4rem .75rem; border-radius: 6px; font-size: .82rem; text-decoration: none; background: #fff; border: 1px solid #e2e8f0; color: #374151; }
        .pagination .active span { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
    </style>
</head>
<body>

<nav class="nav">
    <a href="{{ route('delivery.today') }}" class="nav-logo">
        <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall Logo" class="nav-logo-img">
    </a>
    <div class="nav-menu">
        <a href="{{ route('delivery.today') }}" class="nav-link {{ request()->routeIs('delivery.today') ? 'active' : '' }}">Today</a>
        <a href="{{ route('delivery.deliveries.index') }}" class="nav-link {{ request()->routeIs('delivery.deliveries.*') ? 'active' : '' }}">All</a>
        <a href="{{ route('delivery.dashboard') }}" class="nav-link {{ request()->routeIs('delivery.dashboard') ? 'active' : '' }}">Stats</a>
        <form method="POST" action="{{ route('delivery.logout') }}" style="display:inline">
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
