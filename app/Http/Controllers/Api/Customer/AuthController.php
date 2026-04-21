<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerOtp;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'language_code' => 'nullable|string|max:10',
                'preferred_language' => 'nullable|string|max:10',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if (Customer::where('mobile', $data['mobile'])->exists()) {
            return $this->error('Mobile number already registered.', 422);
        }

        $email = sprintf('%s-customer@waterfall.local', $data['mobile']);

        $customer = DB::transaction(function () use ($data, $email) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => $data['password'],
                'role' => 'customer',
            ]);

            return Customer::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'mobile' => $data['mobile'],
                'email' => $email,
                'address' => null,
                'zone_id' => null,
                'customer_type' => 'residential',
                'approval_status' => 'pending',
            ]);
        });

        $locale = $this->resolveLocale($request);

        return $this->success(
            'Registration submitted successfully. Your account is now waiting for admin approval.',
            [
                'customer_id' => $customer->customer_id,
                'account_status' => $customer->approval_status,
                'customer' => $this->customerProfile($customer->fresh(['zone']), $locale),
            ],
            201
        );
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'password' => ['nullable', 'string'],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        $locale = $this->resolveLocale($request);
        $customer = Customer::with(['user', 'zone'])->where('mobile', $data['mobile'])->first();

        if (! $customer) {
            return $this->error('No account found with this mobile number.', 404);
        }

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
                'token' => $token,
                'customer' => $this->customerProfile($customer, $locale),
            ]);
        }

        $this->generateOtp($data['mobile'], 'login');

        return $this->success('OTP sent to your mobile number.');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'mobile' => ['required', 'string', 'regex:/^01[3-9][0-9]{8}$/'],
                'otp' => 'required|string',
                'type' => 'required|in:registration,login',
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors());
        }

        if ($data['type'] === 'registration') {
            $customer = Customer::with('zone')->where('mobile', $data['mobile'])->first();

            if (! $customer) {
                return $this->error('Customer account not found.', 404);
            }

            return $this->success('OTP verification is disabled for registration. Your account is waiting for admin approval.', [
                'account_status' => $customer->approval_status,
            ]);
        }

        $locale = $this->resolveLocale($request);
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
            'token' => $token,
            'customer' => $this->customerProfile($customer, $locale),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logged out successfully.');
    }

    private function generateOtp(string $mobile, string $purpose): void
    {
        CustomerOtp::where('mobile', $mobile)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $otp = app()->isProduction() ? (string) random_int(100000, 999999) : '123456';

        CustomerOtp::create([
            'mobile' => $mobile,
            'otp_hash' => Hash::make($otp),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // TODO: Send OTP via SMS in production.
    }

    private function customerProfile(Customer $customer, string $locale = 'bn'): array
    {
        return [
            'id' => $customer->id,
            'customer_id' => $customer->customer_id,
            'name' => $customer->name,
            'name_bn' => $customer->name_bn,
            'display_name' => $this->localized($customer->name_bn, $customer->name, $locale),
            'mobile' => $customer->mobile,
            'email' => $customer->email,
            'address' => $customer->address,
            'address_bn' => $customer->address_bn,
            'display_address' => $this->localized($customer->address_bn, $customer->address, $locale),
            'status' => $customer->approval_status,
            'zone' => $customer->zone?->name,
        ];
    }
}
