<?php

namespace App\Console\Commands;

use App\Models\CustomerOtp;
use Illuminate\Console\Command;

class CleanExpiredOtps extends Command
{
    protected $signature = 'waterfall:clean-otps';
    protected $description = 'Delete expired and verified OTP records older than 24 hours.';

    public function handle(): int
    {
        $deleted = CustomerOtp::where(function ($q) {
            $q->where('expires_at', '<', now()->subHours(24))
              ->orWhere(function ($q2) {
                  $q2->whereNotNull('verified_at')
                     ->where('verified_at', '<', now()->subHours(24));
              });
        })->delete();

        $this->info("Cleaned {$deleted} expired OTP record(s).");

        return self::SUCCESS;
    }
}
