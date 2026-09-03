<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GenerateSitemap::class,
        \App\Console\Commands\PurgeRoomsAndRelatedData::class,
        \App\Console\Commands\SendCityAlerts::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Example: regenerate sitemap daily at 02:00
        $schedule->command('sitemap:generate')->dailyAt('02:00');

        $schedule->command('alerts:send-city --hours=24')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        // Load commands from Console/Commands
        $this->load(__DIR__ . '/Commands');
    }
}
