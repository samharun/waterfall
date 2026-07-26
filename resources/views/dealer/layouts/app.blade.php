@php
    $portalName = 'Dealer Portal';
    $homeUrl = route('dealer.dashboard');
    $logoutUrl = route('dealer.logout');
    $portalNav = [
        ['label' => 'Dashboard', 'url' => route('dealer.dashboard'), 'active' => 'dealer.dashboard'],
        ['label' => 'Product Prices', 'url' => route('dealer.products.index'), 'active' => 'dealer.products.*'],
        ['label' => 'Orders', 'url' => route('dealer.orders.index'), 'active' => 'dealer.orders.*'],
        ['label' => 'Bills', 'url' => route('dealer.invoices.index'), 'active' => 'dealer.invoices.*'],
        ['label' => 'Payments', 'url' => route('dealer.payments.index'), 'active' => 'dealer.payments.*'],
        ['label' => 'Jar Ledger', 'url' => route('dealer.jar-deposits.index'), 'active' => 'dealer.jar-deposits.*'],
        ['label' => 'Profile', 'url' => route('dealer.profile'), 'active' => 'dealer.profile'],
    ];
@endphp
@include('layouts.portal-shell')
