@php
    $portalName = 'Delivery Portal';
    $homeUrl = route('delivery.today');
    $logoutUrl = route('delivery.logout');
    $portalNav = [
        ['label' => "Today's Deliveries", 'url' => route('delivery.today'), 'active' => 'delivery.today'],
        ['label' => 'All Deliveries', 'url' => route('delivery.deliveries.index'), 'active' => 'delivery.deliveries.*'],
        ['label' => 'Performance', 'url' => route('delivery.dashboard'), 'active' => 'delivery.dashboard'],
    ];
@endphp
@include('layouts.portal-shell')
