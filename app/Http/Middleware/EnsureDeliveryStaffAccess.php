<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeliveryStaffAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('delivery.login');
        }

        $user = auth()->user();

        if ($user->role !== 'delivery_staff') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('delivery.login')
                ->withErrors(['login' => 'Access denied. This panel is for delivery staff only.']);
        }

        return $next($request);
    }
}
