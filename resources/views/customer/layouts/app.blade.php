@php
    $portalName = 'Customer Portal';
    $homeUrl = route('customer.dashboard');
    $logoutUrl = route('customer.logout');
    $portalNav = [
        ['label' => __('customer.nav_home'), 'url' => route('customer.dashboard'), 'active' => 'customer.dashboard'],
        ['label' => __('customer.nav_orders'), 'url' => route('customer.orders.index'), 'active' => 'customer.orders.*'],
        ['label' => __('customer.nav_subscription'), 'url' => route('customer.subscription.show'), 'active' => 'customer.subscription.*'],
        ['label' => __('customer.nav_bills'), 'url' => route('customer.invoices.index'), 'active' => 'customer.invoices.*'],
        ['label' => __('customer.nav_payments'), 'url' => route('customer.payments.index'), 'active' => 'customer.payments.*'],
        ['label' => __('customer.jars'), 'url' => route('customer.jar-deposits.index'), 'active' => 'customer.jar-deposits.*'],
        ['label' => __('customer.nav_profile'), 'url' => route('customer.profile'), 'active' => 'customer.profile*'],
    ];
@endphp
@include('layouts.portal-shell')
