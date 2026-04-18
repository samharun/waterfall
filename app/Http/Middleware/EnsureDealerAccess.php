<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDealerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('dealer.login');
        }

        $user   = auth()->user();
        $dealer = $user->dealer;

        if ($user->role !== 'dealer' || ! $dealer) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('dealer.login')
                ->withErrors(['login' => 'No dealer profile linked to this account.']);
        }

        if ($dealer->approval_status !== 'approved') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('dealer.login')
                ->withErrors(['login' => 'Your dealer account is not active yet. Please contact Waterfall support.']);
        }

        return $next($request);
    }
}
