<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerOtp;
use App\Models\User;
use App\Models\Zone;
use App\Notifications\NewRegistrationPendingNotification;
use App\Support\SafeNotifier;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    // ── Show registration form ─────────────────────────────────────

    public function showRegister()
    {
        if (Auth::check() && Auth::user()->customer?->approval_status === 'approved') {
            return redirect()->route('customer.dashboard');
        }

        $zones = Zone::active()->orderBy('name')->get();

        return view('customer.auth.register', compact('zones'));
    }

    // ── Handle registration form submit ────────────────────────────

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'mobile'        => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'         => ['nullable', 'email', 'max:255'],
            'address'       => ['required', 'string', 'max:500'],
            'zone_id'       => ['nullable', 'exists:zones,id'],
            'customer_type' => ['required', 'in:residential,corporate'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'mobile.regex' => 'Please enter a valid Bangladesh mobile number (01XXXXXXXXX).',
        ]);

        // Check if mobile already exists
        $existing = Customer::where('mobile', $validated['mobile'])->first();

        if ($existing) {
            $msg = match ($existing->approval_status) {
                'pending'  => 'Your registration is already pending admin approval.',
                'approved' => 'This mobile number is already registered. Please login.',
                default    => 'This mobile number cannot register at this time. Please contact Waterfall support.',
            };
            return back()->withErrors(['mobile' => $msg])->withInput();
        }

        // Create OTP and store registration payload
        $this->otpService->createRegistrationOtp($validated, $request);

        // Store mobile in session for OTP page
        session(['otp_mobile' => $validated['mobile']]);

        return redirect()->route('customer.otp.verify')
            ->with('info', 'An OTP has been sent to your mobile number.');
    }

    // ── Show OTP verification form ─────────────────────────────────

    public function showVerifyOtp(Request $request)
    {
        $mobile = session('otp_mobile');

        if (! $mobile) {
            return redirect()->route('customer.register')
                ->withErrors(['mobile' => 'Session expired. Please register again.']);
        }

        return view('customer.auth.verify-otp', compact('mobile'));
    }

    // ── Handle OTP verification ────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'otp'    => ['required', 'digits:6'],
        ]);

        $error  = '';
        $record = $this->otpService->verifyOtp($request->mobile, $request->otp, $error);

        if (! $record) {
            return back()->withErrors(['otp' => $error])->withInput();
        }

        // Create user and customer in a transaction
        try {
            DB::transaction(function () use ($record) {
                $payload = $record->payload;

                // Prevent duplicate if OTP submitted twice
                if (Customer::where('mobile', $payload['mobile'])->exists()) {
                    return;
                }

                $user = User::create([
                    'name'     => $payload['name'],
                    'email'    => $payload['email'] ?? null,
                    'password' => $payload['password_hash'],
                    'role'     => 'customer',
                ]);

                Customer::create([
                    'user_id'         => $user->id,
                    'name'            => $payload['name'],
                    'mobile'          => $payload['mobile'],
                    'email'           => $payload['email'] ?? null,
                    'address'         => $payload['address'],
                    'zone_id'         => $payload['zone_id'] ?? null,
                    'customer_type'   => $payload['customer_type'],
                    'approval_status' => 'pending',
                    'opening_balance' => 0,
                    'current_due'     => 0,
                    'jar_deposit_qty' => 0,
                ]);

                Log::info("New customer registration pending approval: {$payload['mobile']}");

                // Notify all admin users by email
                User::whereIn('role', ['super_admin', 'admin'])->each(function (User $admin) use ($payload) {
                    SafeNotifier::send(
                        $admin,
                        new NewRegistrationPendingNotification('customer', $payload['name'], $payload['mobile']),
                        [
                            'context' => 'customer_registration_pending',
                            'mobile' => $payload['mobile'],
                            'admin_user_id' => $admin->id,
                        ]
                    );
                });
            });
        } catch (\Throwable $e) {
            Log::error("Customer registration failed: " . $e->getMessage());
            return back()->withErrors(['otp' => 'Registration failed. Please try again.']);
        }

        // Clear session
        session()->forget('otp_mobile');

        return redirect()->route('customer.registration.pending');
    }

    // ── Resend OTP ─────────────────────────────────────────────────

    public function resendOtp(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
        ]);

        $error  = '';
        $result = $this->otpService->resendOtp($request->mobile, $request, $error);

        if (! $result) {
            return back()->withErrors(['otp' => $error]);
        }

        return back()->with('success', 'A new OTP has been sent to your mobile number.');
    }

    // ── Registration pending page ──────────────────────────────────

    public function registrationPending()
    {
        return view('customer.auth.registration-pending');
    }
}
