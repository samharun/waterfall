<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOtp extends Model
{
    protected $fillable = [
        'mobile',
        'otp_hash',
        'purpose',
        'payload',
        'expires_at',
        'verified_at',
        'attempts',
        'resend_count',
        'last_sent_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'payload'     => 'array',
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'last_sent_at'=> 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= 5;
    }

    public function canResend(): bool
    {
        if ($this->resend_count >= 3) {
            return false;
        }

        if ($this->last_sent_at && $this->last_sent_at->diffInSeconds(now()) < 60) {
            return false;
        }

        return true;
    }
}
