<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(\App\Services\DataMaintenanceService::class)->run(false))
    ->dailyAt('02:30')
    ->name('cleanup-stale-platform-data')
    ->withoutOverlapping();
