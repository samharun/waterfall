<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\Customer;
use App\Models\CustomerOtp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    // ── Register ───────────────────────────────────────────────────

    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name'       => 'required|string|max:255',
                'name_bn'    => 'nullable|string|max:255',
                'mobile'     => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'address'    => 'required|string|max:500',
                'address_bn' => 'nullable|string|max:500',
                'email'      => 'nullable|email|max:100',
                'zone_id'    => 'required|exists:zones,id',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (Customer::where('mobile', $data['mobile'])->exists()) {
            return $this->error('Mobile number already registered.', 422);
        }

        $customer = Customer::create([
            'name'            => $data['name'],
            'name_bn'         => $data['name_bn'] ?? null,
            'mobile'          => $data['mobile'],
            'address'         => $data['address'],
            'address_bn'      => $data['address_bn'] ?? null,
            'email'           => $data['email'] ?? null,
            'zone_id'         => $data['zone_id'],
            'approval_status' => 'pending',
        ]);

        $this->generateOtp($data['mobile'], 'registration');

        return $this->success(
            'Registration successful. OTP sent. Your account will be activated after admin approval.',
            ['customer_id' => $customer->customer_id],
            201
        );
    }

    // ── Login ──────────────────────────────────────────────────────

    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile'   => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'password' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $locale   = $this->resolveLocale($request);
        $customer = Customer::with(['user', 'zone'])->where('mobile', $data['mobile'])->first();

        if (! $customer) {
            return $this->error('No account found with this mobile number.', 404);
        }

        // Password login path
        if (! empty($data['password'])) {
            if (! $customer->user_id || ! $customer->user) {
                return $this->error('This account does not have password login enabled.', 422);
            }

            if ($customer->approval_status !== 'approved') {
                return $this->success('Your account is waiting for admin approval.', [
                    'account_status' => $customer->approval_status,
                ]);
            }

            if (! Hash::check($data['password'], $customer->user->password)) {
                return $this->error('Invalid mobile number or password.', 422);
            }

            $token = $customer->createToken('customer-app')->plainTextToken;

            return $this->success('Login successful.', [
                'token'    => $token,
                'customer' => $this->customerProfile($customer, $locale),
            ]);
        }

        // OTP login path
        $this->generateOtp($data['mobile'], 'login');

        return $this->success('OTP sent to your mobile number.');
    }

    // ── Verify OTP ─────────────────────────────────────────────────

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'otp'    => 'required|string',
                'type'   => 'required|in:registration,login',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $locale    = $this->resolveLocale($request);
        $otpRecord = CustomerOtp::where('mobile', $data['mobile'])
            ->where('purpose', $data['type'])
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otpRecord) {
            return $this->error('OTP not found. Please request a new OTP.', 400);
        }

        if ($otpRecord->isExpired()) {
            return $this->error('OTP has expired. Please request a new OTP.', 400);
        }

        if ($otpRecord->hasExceededAttempts()) {
            return $this->error('Too many failed attempts. Please request a new OTP.', 400);
        }

        if (! Hash::check($data['otp'], $otpRecord->otp_hash)) {
            $otpRecord->increment('attempts');
            return $this->error('Invalid OTP.', 400);
        }

        $otpRecord->update(['verified_at' => now()]);

        $customer = Customer::with('zone')->where('mobile', $data['mobile'])->first();

        if (! $customer) {
            return $this->error('Customer account not found.', 404);
        }

        if ($customer->approval_status !== 'approved') {
            return $this->success('OTP verified. Your account is waiting for admin approval.', [
                'account_status' => $customer->approval_status,
            ]);
        }

        $token = $customer->createToken('customer-app')->plainTextToken;

        return $this->success('Login successful.', [
            'token'    => $token,
            'customer' => $this->customerProfile($customer, $locale),
        ]);
    }

    // ── Logout ─────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logged out successfully.');
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function generateOtp(string $mobile, string $purpose): void
    {
        CustomerOtp::where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $otp = app()->isProduction() ? (string) random_int(100000, 999999) : '123456';

        CustomerOtp::create([
            'mobile'       => $mobile,
            'otp_hash'     => Hash::make($otp),
            'purpose'      => $purpose,
            'expires_at'   => now()->addMinutes(5),
            'last_sent_at' => now(),
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        // TODO: Send OTP via SMS in production
    }

    private function customerProfile(Customer $customer, string $locale = 'bn'): array
    {
        return [
            'id'              => $customer->id,
            'customer_id'     => $customer->customer_id,
            'name'            => $customer->name,
            'name_bn'         => $customer->name_bn,
            'display_name'    => $this->localized($customer->name_bn, $customer->name, $locale),
            'mobile'          => $customer->mobile,
            'email'           => $customer->email,
            'address'         => $customer->address,
            'address_bn'      => $customer->address_bn,
            'display_address' => $this->localized($customer->address_bn, $customer->address, $locale),
            'status'          => $customer->approval_status,
            'zone'            => $customer->zone?->name,
        ];
    }
}
