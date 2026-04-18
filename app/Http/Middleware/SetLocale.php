<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Default locale is Bangla
        $locale = session('locale', 'bn');

        if (! in_array($locale, ['bn', 'en'])) {
            $locale = 'bn';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
