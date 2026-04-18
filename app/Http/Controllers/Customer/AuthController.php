<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->customer?->approval_status === 'approved') {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'password' => ['required'],
        ], [
            'mobile.regex' => 'Please enter a valid Bangladesh mobile number (01XXXXXXXXX).',
        ]);

        // Find customer by mobile
        $customer = Customer::where('mobile', $request->mobile)->first();

        if (! $customer || ! $customer->user_id) {
            return back()->withErrors(['mobile' => 'Invalid mobile number or password.'])->withInput();
        }

        if ($customer->approval_status !== 'approved') {
            $msg = match ($customer->approval_status) {
                'pending'  => 'Your account is pending admin approval. You will be notified once approved.',
                'rejected' => 'Your registration was not approved. Please contact Waterfall support.',
                'inactive' => 'Your account is inactive. Please contact Waterfall support.',
                default    => 'Your account is not active yet. Please contact Waterfall support.',
            };
            return back()->withErrors(['mobile' => $msg])->withInput();
        }

        // Attempt login with the linked user account
        if (! Auth::attempt(['id' => $customer->user_id, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['mobile' => 'Invalid mobile number or password.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
