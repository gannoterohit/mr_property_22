<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(\App\Services\DataMaintenanceService::class)->run(true))
    ->dailyAt('02:30')
    ->name('cleanup-stale-platform-data')
    ->withoutOverlapping();
// * * * * * cd /path-to-apnanest && php artisan schedule:run >> /dev/null 2>&1