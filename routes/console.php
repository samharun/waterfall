<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Waterfall Scheduled Tasks ──────────────────────────────────────
Schedule::command('waterfall:generate-recurring-orders')->dailyAt('06:00');
Schedule::command('waterfall:clean-otps')->daily();
