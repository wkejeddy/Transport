<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule 30-minute unpaid booking release & advance reservation 8h reminder / 6h cancellation every minute
Schedule::command('transport:release-unpaid-bookings')->everyMinute();

// Schedule dispute 48-hour auto-escalation check every hour
Schedule::command('transport:escalate-disputes')->hourly();

// Recalculate scorecards every 6 hours
Schedule::command('transport:recalculate-scorecards')->everySixHours();
