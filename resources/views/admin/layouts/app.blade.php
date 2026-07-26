<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin' }} | Waterfall</title>
    <script>
        if (localStorage.getItem('waterfall-theme') === 'dark' || (!localStorage.getItem('waterfall-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>{!! file_get_contents(resource_path('css/admin.css')) !!}</style>
    @livewireStyles
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar" id="admin-sidebar" aria-label="Admin navigation">
            <a href="{{ route('admin.livewire.zones.index') }}" class="brand">
                <img src="{{ asset('images/waterfall-logo.png') }}" alt="Waterfall">
                <span class="brand-copy">
                    <span class="brand-name">Waterfall</span>
                    <span class="brand-caption">Admin console</span>
                </span>
            </a>

            <nav>
                <div class="nav-group-label">Customer management</div>
                <a href="{{ route('admin.livewire.zones.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.zones.*') ? 'active' : '' }}">Zones / Lines</a>
                <a href="{{ route('admin.livewire.customers.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.customers.*') ? 'active' : '' }}">Customers</a>
                <a href="{{ route('admin.livewire.customer-prices.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.customer-prices.*') ? 'active' : '' }}">Customer Pricing</a>
                <a href="{{ route('admin.livewire.customer-subscriptions.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.customer-subscriptions.*') ? 'active' : '' }}">Subscriptions</a>

                <div class="nav-group-label">Dealer management</div>
                <a href="{{ route('admin.livewire.dealers.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.dealers.*') ? 'active' : '' }}">Dealers</a>
                <a href="{{ route('admin.livewire.dealer-prices.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.dealer-prices.*') ? 'active' : '' }}">Dealer Pricing</a>

                <div class="nav-group-label">Operations</div>
                <a href="{{ route('admin.livewire.deliveries.index') }}" class="nav-link {{ request()->routeIs('admin.livewire.deliveries.*') ? 'active' : '' }}">Delivery Assignments</a>

                <div class="nav-group-label">Administration</div>
                <a href="{{ url('/admin') }}" class="nav-link">Filament Panel</a>
            </nav>
        </aside>

        <button type="button" class="sidebar-backdrop" aria-label="Close navigation" data-sidebar-close></button>

        <div class="app-main">
            <header class="topbar">
                <div class="topbar-left">
                    <button type="button" class="sidebar-toggle" aria-controls="admin-sidebar" aria-expanded="false" data-sidebar-toggle>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                    <div>
                        <div class="topbar-eyebrow">Waterfall Management</div>
                        <div class="topbar-title">{{ $title ?? 'Admin' }}</div>
                    </div>
                </div>

                <div class="topbar-actions">
                    <button type="button" class="theme-toggle" aria-label="Toggle dark mode" data-theme-toggle>
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.5 8.5 0 1 0 20.5 14.2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <div class="user-chip">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</span>
                        <span class="user-name">{{ auth()->user()?->name ?? 'Administrator' }}</span>
                    </div>
                    <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                        @csrf
                        <button type="submit" class="logout">Logout</button>
                    </form>
                </div>
            </header>

            <main class="content">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
        (() => {
            const body = document.body;
            const toggle = document.querySelector('[data-sidebar-toggle]');
            const close = document.querySelector('[data-sidebar-close]');
            const setSidebar = (open) => {
                body.classList.toggle('sidebar-open', open);
                toggle?.setAttribute('aria-expanded', String(open));
            };
            toggle?.addEventListener('click', () => setSidebar(!body.classList.contains('sidebar-open')));
            close?.addEventListener('click', () => setSidebar(false));
            document.querySelectorAll('.sidebar a').forEach(link => link.addEventListener('click', () => setSidebar(false)));
            document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('waterfall-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            });
        })();
    </script>
</body>
</html>
