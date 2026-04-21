<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CustomerOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OtpService
{
    // ── Config helpers ─────────────────────────────────────────────

    protected function otpLength(): int
    {
        return (int) AppSetting::getValue('otp', 'length', 6);
    }

    protected function expiryMinutes(): int
    {
        return (int) AppSetting::getValue('otp', 'expiry_minutes', 5);
    }

    protected function maxAttempts(): int
    {
        return (int) AppSetting::getValue('otp', 'max_attempts', 5);
    }

    protected function maxResendCount(): int
    {
        return (int) AppSetting::getValue('otp', 'max_resend_count', 3);
    }

    protected function resendCooldown(): int
    {
        return (int) AppSetting::getValue('otp', 'resend_cooldown_seconds', 60);
    }

    protected function messageTemplate(): string
    {
        return AppSetting::getValue(
            'otp',
            'message_template',
            'Your Waterfall verification code is {otp}. It will expire in {minutes} minutes.'
        );
    }

    // ── OTP generation ─────────────────────────────────────────────

    public function generateOtp(): string
    {
        $length = $this->otpLength();
        $min    = (int) str_pad('1', $length, '0');
        $max    = (int) str_pad('9', $length, '9');
        return str_pad((string) random_int($min, $max), $length, '0', STR_PAD_LEFT);
    }

    protected function buildMessage(string $otp): string
    {
        $template = $this->messageTemplate();
        $appName  = config('app.name', 'Waterfall');

        return str_replace(
            ['{otp}', '{minutes}', '{app_name}'],
            [$otp, $this->expiryMinutes(), $appName],
            $template
        );
    }

    // ── Create registration OTP ────────────────────────────────────

    public function createRegistrationOtp(array $data, Request $request): CustomerOtp
    {
        $otp = $this->generateOtp();

        CustomerOtp::where('mobile', $data['mobile'])
            ->where('purpose', 'registration')
            ->whereNull('verified_at')
            ->delete();

        $record = CustomerOtp::create([
            'mobile'       => $data['mobile'],
            'otp_hash'     => Hash::make($otp),
            'purpose'      => 'registration',
            'payload'      => [
                'name'          => $data['name'],
                'mobile'        => $data['mobile'],
                'email'         => $data['email'] ?? null,
                'address'       => $data['address'] ?? null,
                'zone_id'       => $data['zone_id'] ?? null,
                'customer_type' => $data['customer_type'] ?? 'residential',
                'password_hash' => isset($data['password']) ? Hash::make($data['password']) : null,
            ],
            'expires_at'   => now()->addMinutes($this->expiryMinutes()),
            'attempts'     => 0,
            'resend_count' => 0,
            'last_sent_at' => now(),
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);

        $this->sendOtp($data['mobile'], $otp);

        return $record;
    }

    // ── Verify OTP ─────────────────────────────────────────────────

    public function verifyOtp(string $mobile, string $otp, string &$error): ?CustomerOtp
    {
        $record = CustomerOtp::where('mobile', $mobile)
            ->where('purpose', 'registration')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record) {
            $error = 'No pending OTP found. Please register again.';
            return null;
        }

        if ($record->isExpired()) {
            $error = 'OTP has expired. Please request a new one.';
            return null;
        }

        if ($record->attempts >= $this->maxAttempts()) {
            $error = 'Too many failed attempts. Please request a new OTP.';
            return null;
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            $record->increment('attempts');
            $remaining = $this->maxAttempts() - $record->fresh()->attempts;
            $error = "Invalid OTP. {$remaining} attempt(s) remaining.";
            return null;
        }

        $record->update(['verified_at' => now()]);

        return $record;
    }

    // ── Resend OTP ─────────────────────────────────────────────────

    public function resendOtp(string $mobile, Request $request, string &$error): ?CustomerOtp
    {
        $record = CustomerOtp::where('mobile', $mobile)
            ->where('purpose', 'registration')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $record) {
            $error = 'No pending registration found for this mobile.';
            return null;
        }

        if ($record->isVerified()) {
            $error = 'OTP already verified.';
            return null;
        }

        if ($record->resend_count >= $this->maxResendCount()) {
            $error = 'Maximum resend limit reached. Please start registration again.';
            return null;
        }

        $cooldown = $this->resendCooldown();
        if ($record->last_sent_at && $record->last_sent_at->diffInSeconds(now()) < $cooldown) {
            $wait  = $cooldown - $record->last_sent_at->diffInSeconds(now());
            $error = "Please wait {$wait} seconds before requesting a new OTP.";
            return null;
        }

        $otp = $this->generateOtp();

        $record->update([
            'otp_hash'     => Hash::make($otp),
            'expires_at'   => now()->addMinutes($this->expiryMinutes()),
            'attempts'     => 0,
            'resend_count' => $record->resend_count + 1,
            'last_sent_at' => now(),
        ]);

        $this->sendOtp($mobile, $otp);

        return $record;
    }

    // ── Send OTP ───────────────────────────────────────────────────

    protected function sendOtp(string $mobile, string $otp): void
    {
        $message = $this->buildMessage($otp);

        // Only log OTP in local/dev environment
        if (app()->environment('local', 'testing')) {
            Log::info("OTP for {$mobile}: {$otp}");
        }

        app(SmsService::class)->send($mobile, $message);
    }
}
