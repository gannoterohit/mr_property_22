<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    if (Schema::hasTable('otps')) {
        DB::table('otps')->where('expires_at', '<', now()->subDay())->delete();
    }

    if (Schema::hasTable('sessions')) {
        DB::table('sessions')
            ->where('last_activity', '<', now()->subDays(7)->timestamp)
            ->delete();
    }

    if (Schema::hasTable('payments')) {
        DB::table('payments')
            ->whereIn('status', ['pending', 'failed'])
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    if (Schema::hasTable('analytics_events')) {
        DB::table('analytics_events')
            ->where('created_at', '<', now()->subMonths(6))
            ->delete();
    }
})->dailyAt('02:30')->name('cleanup-stale-platform-data')->withoutOverlapping();
