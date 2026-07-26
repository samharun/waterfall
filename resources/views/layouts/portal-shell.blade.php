<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $portalName) | Waterfall</title>
    <style>{!! file_get_contents(resource_path('css/portal.css')) !!}</style>
    @livewireStyles
</head>
<body>
<div class="portal-shell">
    <aside class="portal-sidebar" id="portal-sidebar">
        <a href="{{ $homeUrl }}" class="portal-brand">
            <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall">
            <span><strong>Waterfall</strong><small>{{ $portalName }}</small></span>
        </a>
        <div class="portal-nav-label">Navigation</div>
        <nav class="nav-menu">
            @foreach($portalNav as $item)
                <a href="{{ $item['url'] }}" wire:navigate class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>
    </aside>
    <button type="button" class="sidebar-backdrop" aria-label="Close navigation" data-portal-close></button>

    <div class="portal-main">
        <header class="portal-header">
            <div class="portal-header-left">
                <button type="button" class="portal-toggle" aria-label="Open navigation" aria-controls="portal-sidebar" data-portal-toggle>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <div class="portal-heading"><small>Waterfall</small><strong>{{ $portalName }}</strong></div>
            </div>
            <div class="portal-user">
                <span class="portal-avatar">{{ strtoupper(substr(auth()->user()?->name ?? $portalName, 0, 1)) }}</span>
                <span class="portal-user-name">{{ auth()->user()?->name ?? $portalName }}</span>
                <form method="POST" action="{{ $logoutUrl }}">
                    @csrf
                    <button type="submit" class="nav-logout">Logout</button>
                </form>
            </div>
        </header>
        <main class="main">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
@livewireScripts
<script>
    (() => {
        const body = document.body;
        const close = () => body.classList.remove('portal-menu-open');
        document.querySelector('[data-portal-toggle]')?.addEventListener('click', () => body.classList.toggle('portal-menu-open'));
        document.querySelector('[data-portal-close]')?.addEventListener('click', close);
        document.querySelectorAll('.portal-sidebar a').forEach(link => link.addEventListener('click', close));
    })();
</script>
</body>
</html>
