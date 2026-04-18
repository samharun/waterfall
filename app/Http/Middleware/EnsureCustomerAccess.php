<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login');
        }

        $user     = auth()->user();
        $customer = $user->customer;

        if (! $customer) {
            auth()->logout();
            return redirect()->route('customer.login')
                ->withErrors(['mobile' => 'No customer profile linked to this account.']);
        }

        if ($customer->approval_status !== 'approved') {
            auth()->logout();
            return redirect()->route('customer.login')
                ->withErrors(['mobile' => 'Your account is not active yet. Please contact Waterfall support.']);
        }

        return $next($request);
    }
}
