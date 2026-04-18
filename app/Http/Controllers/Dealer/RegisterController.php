<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\CustomerOtp;
use App\Models\User;
use App\Models\Zone;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisterController extends Controller
{
    public function __construct(private OtpService $otpService) {}

    public function showRegister()
    {
        if (Auth::check() && Auth::user()->role === 'dealer'
            && Auth::user()->dealer?->approval_status === 'approved') {
            return redirect()->route('dealer.dashboard');
        }

        $zones = Zone::active()->orderBy('name')->get();

        return view('dealer.auth.register', compact('zones'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'mobile'   => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'    => ['nullable', 'email', 'max:255'],
            'address'  => ['required', 'string', 'max:500'],
            'zone_id'  => ['nullable', 'exists:zones,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'mobile.regex' => 'Please enter a valid Bangladesh mobile number (01XXXXXXXXX).',
        ]);

        $existing = Dealer::where('mobile', $validated['mobile'])->first();

        if ($existing) {
            $msg = match ($existing->approval_status) {
                'pending'  => 'Your dealer registration is already pending admin approval.',
                'approved' => 'This mobile number is already registered. Please login.',
                default    => 'This mobile number cannot register at this time. Please contact Waterfall support.',
            };
            return back()->withErrors(['mobile' => $msg])->withInput();
        }

        // Create OTP with dealer_registration purpose
        $otp = $this->otpService->generateOtp();

        CustomerOtp::where('mobile', $validated['mobile'])
            ->where('purpose', 'dealer_registration')
            ->whereNull('verified_at')
            ->delete();

        CustomerOtp::create([
            'mobile'       => $validated['mobile'],
            'otp_hash'     => Hash::make($otp),
            'purpose'      => 'dealer_registration',
            'payload'      => [
                'name'          => $validated['name'],
                'mobile'        => $validated['mobile'],
                'email'         => $validated['email'] ?? null,
                'address'       => $validated['address'],
                'zone_id'       => $validated['zone_id'] ?? null,
                'password_hash' => Hash::make($validated['password']),
            ],
            'expires_at'   => now()->addMinutes(5),
            'attempts'     => 0,
            'resend_count' => 0,
            'last_sent_at' => now(),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        $message = "Your Waterfall Dealer verification code is {$otp}. It will expire in 5 minutes.";
        Log::info("Dealer OTP for {$validated['mobile']}: {$otp}");
        app(\App\Services\SmsService::class)->send($validated['mobile'], $message);

        session(['dealer_otp_mobile' => $validated['mobile']]);

        return redirect()->route('dealer.otp.verify')
            ->with('info', 'An OTP has been sent to your mobile number.');
    }

    public function showVerifyOtp()
    {
        $mobile = session('dealer_otp_mobile');

        if (! $mobile) {
            return redirect()->route('dealer.register')
                ->withErrors(['mobile' => 'Session expired. Please register again.']);
        }

        return view('dealer.auth.verify-otp', compact('mobile'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'otp'    => ['required', 'digits:6'],
        ]);

        $record = CustomerOtp::where('mobile', $request->mobile)
            ->where('purpose', 'dealer_registration')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'No pending OTP found. Please register again.']);
        }

        if ($record->isExpired()) {
            return back()->withErrors(['otp' => 'OTP has expired. Please request a new one.']);
        }

        if ($record->hasExceededAttempts()) {
            return back()->withErrors(['otp' => 'Too many failed attempts. Please request a new OTP.']);
        }

        if (! Hash::check($request->otp, $record->otp_hash)) {
            $record->increment('attempts');
            $remaining = 5 - $record->fresh()->attempts;
            return back()->withErrors(['otp' => "Invalid OTP. {$remaining} attempt(s) remaining."]);
        }

        $record->update(['verified_at' => now()]);

        try {
            DB::transaction(function () use ($record) {
                $payload = $record->payload;

                if (Dealer::where('mobile', $payload['mobile'])->exists()) {
                    return;
                }

                $user = User::create([
                    'name'     => $payload['name'],
                    'email'    => $payload['email'] ?? null,
                    'password' => $payload['password_hash'],
                    'role'     => 'dealer',
                ]);

                Dealer::create([
                    'user_id'         => $user->id,
                    'name'            => $payload['name'],
                    'mobile'          => $payload['mobile'],
                    'email'           => $payload['email'] ?? null,
                    'address'         => $payload['address'],
                    'zone_id'         => $payload['zone_id'] ?? null,
                    'approval_status' => 'pending',
                    'opening_balance' => 0,
                    'current_due'     => 0,
                ]);

                Log::info("New dealer registration pending approval: {$payload['mobile']}");

                User::whereIn('role', ['super_admin', 'admin'])->each(function (User $admin) use ($payload) {
                    $admin->notify(new \App\Notifications\NewRegistrationPendingNotification('dealer', $payload['name'], $payload['mobile']));
                });
            });
        } catch (\Throwable $e) {
            Log::error("Dealer registration failed: " . $e->getMessage());
            return back()->withErrors(['otp' => 'Registration failed. Please try again.']);
        }

        session()->forget('dealer_otp_mobile');

        return redirect()->route('dealer.registration.pending');
    }

    public function resendOtp(Request $request)
    {
        $request->validate(['mobile' => ['required', 'regex:/^01[3-9][0-9]{8}$/']]);

        $record = CustomerOtp::where('mobile', $request->mobile)
            ->where('purpose', 'dealer_registration')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record) {
            return back()->withErrors(['otp' => 'No pending registration found.']);
        }

        if ($record->resend_count >= 3) {
            return back()->withErrors(['otp' => 'Maximum resend limit reached.']);
        }

        if ($record->last_sent_at && $record->last_sent_at->diffInSeconds(now()) < 60) {
            $wait = 60 - $record->last_sent_at->diffInSeconds(now());
            return back()->withErrors(['otp' => "Please wait {$wait} seconds before resending."]);
        }

        $otp = $this->otpService->generateOtp();
        $record->update([
            'otp_hash'     => Hash::make($otp),
            'expires_at'   => now()->addMinutes(5),
            'attempts'     => 0,
            'resend_count' => $record->resend_count + 1,
            'last_sent_at' => now(),
        ]);

        Log::info("Dealer OTP resend for {$request->mobile}: {$otp}");
        app(\App\Services\SmsService::class)->send($request->mobile,
            "Your Waterfall Dealer verification code is {$otp}. It will expire in 5 minutes.");

        return back()->with('success', 'A new OTP has been sent.');
    }

    public function registrationPending()
    {
        return view('dealer.auth.registration-pending');
    }
}
