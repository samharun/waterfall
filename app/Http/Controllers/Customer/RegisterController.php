<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function showRegister()
    {
        if (Auth::check() && Auth::user()->customer?->approval_status === 'approved') {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'mobile.regex' => 'Please enter a valid Bangladesh mobile number (01XXXXXXXXX).',
        ]);

        if (Customer::where('mobile', $validated['mobile'])->exists()) {
            return back()->withErrors(['mobile' => 'Mobile number already registered.'])->withInput();
        }

        try {
            DB::transaction(function () use ($validated) {
                $email = sprintf('%s-customer@waterfall.local', $validated['mobile']);

                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => $validated['password'],
                    'role' => 'customer',
                ]);

                Customer::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'mobile' => $validated['mobile'],
                    'email' => $email,
                    'address' => null,
                    'zone_id' => null,
                    'customer_type' => 'residential',
                    'approval_status' => 'pending',
                    'opening_balance' => 0,
                    'current_due' => 0,
                    'jar_deposit_qty' => 0,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Customer registration submit failed: ' . $e->getMessage(), [
                'mobile' => $validated['mobile'],
            ]);

            return back()->withErrors(['mobile' => 'Registration failed. Please try again.'])->withInput();
        }

        return redirect()->route('customer.registration.pending')
            ->with('success', 'Registration submitted successfully. Your account is now waiting for admin approval.');
    }

    public function showVerifyOtp(Request $request)
    {
        return redirect()->route('customer.register')
            ->withErrors(['mobile' => 'OTP verification is currently disabled. Please sign up directly and wait for admin approval.']);
    }

    public function verifyOtp(Request $request)
    {
        return redirect()->route('customer.register')
            ->withErrors(['mobile' => 'OTP verification is currently disabled. Please sign up directly and wait for admin approval.']);
    }

    public function resendOtp(Request $request)
    {
        return redirect()->route('customer.register')
            ->withErrors(['mobile' => 'OTP resend is currently disabled. Please sign up directly and wait for admin approval.']);
    }

    public function registrationPending()
    {
        return view('customer.auth.registration-pending');
    }
}
