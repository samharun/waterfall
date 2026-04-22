<?php

namespace App\Providers;

use App\Events\OrderStatusChanged;
use App\Listeners\SendOrderStatusPushNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderStatusChanged::class => [
            SendOrderStatusPushNotification::class,
        ],
    ];
}
