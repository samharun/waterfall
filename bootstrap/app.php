<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Smart guest redirect based on URL prefix
        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('delivery/*') || $request->is('delivery')) {
                return route('delivery.login');
            }
            if ($request->is('dealer/*') || $request->is('dealer')) {
                return route('dealer.login');
            }
            return route('customer.login');
        });

        // Smart authenticated redirect based on URL prefix
        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('delivery/*') || $request->is('delivery')) {
                return route('delivery.today');
            }
            if ($request->is('dealer/*') || $request->is('dealer')) {
                return route('dealer.dashboard');
            }
            return route('customer.dashboard');
        });

        $middleware->alias([
            'customer.access' => \App\Http\Middleware\EnsureCustomerAccess::class,
            'delivery.access' => \App\Http\Middleware\EnsureDeliveryStaffAccess::class,
            'dealer.access'   => \App\Http\Middleware\EnsureDealerAccess::class,
            'back.office'     => \App\Http\Middleware\EnsureBackOfficeAccess::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
