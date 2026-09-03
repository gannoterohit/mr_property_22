<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dynamically load mail configuration from database for both Web and Queue
        \App\Models\Setting::setMailConfig();

        if (!\App::runningInConsole() && app()->environment('local')) {
            \Config::set('app.url', \Request::root());
            \Config::set('filesystems.disks.public.url', \Request::root() . '/storage');
        }

        // Custom Rate Limiting for Security
        \Illuminate\Support\Facades\RateLimiter::for('strict_otp', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip())->response(function() {
                return response()->json(['status' => 'error', 'message' => 'Too many OTP requests. Please wait 1 minute.'], 429);
            });
        });

        \Illuminate\Support\Facades\RateLimiter::for('strict_login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinutes(5, 5)->by($request->ip())->response(function() {
                return response()->json(['status' => 'error', 'message' => 'Too many login attempts. Please wait 5 minutes.'], 429);
            });
        });

        \Illuminate\Support\Facades\RateLimiter::for('public_form', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json(['status' => 'error', 'message' => 'Too many requests. Please try again later.'], 429);
            });
        });
    }
}
