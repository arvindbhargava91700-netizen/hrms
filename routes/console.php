<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('subscriptions:process')->dailyAt('00:01');
Schedule::command('subscriptions:check-partners')->dailyAt('00:05');
Schedule::command('app:send-payment-reminders')->dailyAt('09:00');

Schedule::command('queue:work --once --queue=otp')
    ->everyMinute();

Schedule::command('queue:work --once --queue=emails')
    ->everyMinute();

Schedule::command('queue:work --once --queue=notifications')
    ->everyMinute();

// Schedule::command('hrms:mark-auto-absent')->everyFiveMinutes();
Schedule::command('hrms:mark-auto-absent')->everyMinute();