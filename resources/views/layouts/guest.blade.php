<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- SEO Meta Tags -->
        <title>@yield('title', \App\Models\Setting::get('website_name', 'RoomRental') . ' - ' . config('app.name', 'Laravel'))</title>
        <meta name="description" content="@yield('description', \App\Models\Setting::get('seo_meta_description', 'Find your perfect room in your city.'))">
        <meta name="keywords" content="@yield('keywords', \App\Models\Setting::get('seo_meta_keywords', 'room rental, apartment, house'))">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="@yield('canonical', url()->current())">
        
        <!-- Open Graph Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="@yield('og_title', \App\Models\Setting::get('website_name', 'RoomRental'))">
        <meta property="og:description" content="@yield('og_description', \App\Models\Setting::get('seo_meta_description', 'Find your perfect room.'))">
        <meta property="og:url" content="@yield('og_url', url()->current())">
        @if(\App\Models\Setting::get('website_logo'))
            <meta property="og:image" content="{{ asset('storage/' . \App\Models\Setting::get('website_logo')) }}">
        @endif
        
        <!-- Google Search Console Verification -->
        @if(\App\Models\Setting::get('google_search_console_code'))
            <meta name="google-site-verification" content="{{ \App\Models\Setting::get('google_search_console_code') }}">
        @endif

        <!-- Fonts -->
        <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                --primary: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }};
                --secondary: {{ \App\Models\Setting::get('secondary_color', '#10B981') }};
                --primary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('primary_color', '#4F46E5'), '#'), '%02x%02x%02x')) }};
                --secondary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('secondary_color', '#10B981'), '#'), '%02x%02x%02x')) }};
            }
            .guest-auth-shell {
                background: linear-gradient(135deg, #f8fafc 0%, rgba(var(--primary-rgb), .08) 52%, rgba(var(--secondary-rgb), .08) 100%);
            }
            .guest-auth-logo {
                color: var(--primary);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-50">
        <div class="guest-auth-shell min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="guest-auth-logo w-20 h-20 fill-current" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl border border-slate-200 overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
