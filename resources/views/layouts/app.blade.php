<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="format-detection" content="telephone=no">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- SEO Meta Tags -->
    <title>@yield('title', \App\Models\Setting::get('website_name', 'RoomRental') . ' - Find Your Perfect Room')</title>
    <meta name="description" content="@yield('description', \App\Models\Setting::get('seo_meta_description', 'Find your perfect room in your city. Browse verified room listings.'))">
    <meta name="keywords" content="@yield('keywords', \App\Models\Setting::get('seo_meta_keywords', 'room rental, apartment, house, property'))">
    <meta name="author" content="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}">
    <meta name="robots" content="{{ request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*') ? 'noindex, nofollow' : 'index, follow' }}">
    <meta name="theme-color" content="{{ \App\Models\Setting::get('primary_color', '#4F46E5') }}">
    
    
    <!-- Favicon -->
    @php
        $favicon = \App\Models\Setting::get('website_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $favicon) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @endif
    
    @include('partials.seo')
    @if(Route::currentRouteName() === 'home')
        @include('partials.home-ld')
    @endif
    
    <!-- Google Ads & Analytics (Production Only) -->
    @if(app()->environment('production'))
        @php
            $gaEnabled = \App\Models\Setting::get('google_ads_enabled') == '1';
            $ga4Id = \App\Models\Setting::get('ga4_measurement_id');
            $adsId = \App\Models\Setting::get('google_ads_tag_id');
            
            $adsenseEnabled = \App\Models\Setting::get('adsense_enabled') == '1';
            $adsenseId = \App\Models\Setting::get('adsense_client_id');
        @endphp

        {{-- Google Analytics & Ads --}}
        @if($ga4Id || ($gaEnabled && $adsId))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id ?: $adsId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                // GA4 Config (Always load if ID exists)
                @if($ga4Id)
                gtag('config', '{{ $ga4Id }}');
                @endif

                // Google Ads Config (Load only if enabled)
                @if($gaEnabled && $adsId)
                gtag('config', '{{ $adsId }}');
                @endif

                // Helper function for conversion tracking
                window.trackAdsConversion = function(label, value, currency = 'INR') {
                    @if($gaEnabled && $adsId)
                    if (!label) return;
                    gtag('event', 'conversion', {
                        'send_to': '{{ $adsId }}/' + label,
                        'value': value || 1.0,
                        'currency': currency
                     });
                    @endif
                };
            </script>
        @endif

        {{-- Google AdSense --}}
        @if($adsenseEnabled && $adsenseId)
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseId }}" crossorigin="anonymous"></script>
        @endif
    @endif

    @php
        $metaPixelEnabled = \App\Models\Setting::get('meta_pixel_enabled', '0') == '1';
        $metaPixelId = trim((string) \App\Models\Setting::get('meta_pixel_id', ''));
    @endphp
    @if(app()->environment('production') && $metaPixelEnabled && $metaPixelId !== '' && !request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*'))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($metaPixelId));
            fbq('track', 'PageView');
            window.trackMetaPixel = function(eventName, params = {}) {
                if (typeof fbq !== 'function') return;
                fbq('track', eventName, params);
            };
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"></noscript>
    @else
        <script>
            window.trackMetaPixel = function() {};
        </script>
    @endif
    <script>
        window.trackRoomNestAnalytics = function(eventName, params = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token || !eventName) return;

            const payload = {
                event_name: eventName,
                room_id: params.room_id || (Array.isArray(params.content_ids) ? params.content_ids[0] : null),
                payment_id: params.payment_id || null,
                city: params.city || null,
                amount: params.amount || params.value || null,
                currency: params.currency || 'INR',
                url: window.location.href,
                referrer: document.referrer || null,
                payload: params
            };

            fetch(@json(route('analytics.events.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            }).catch(() => {});
        };

        window.trackRoomNestEvent = function(eventName, params = {}) {
            window.trackMetaPixel?.(eventName, params);
            window.trackRoomNestAnalytics?.(eventName, params);
        };

        @unless(request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*'))
            document.addEventListener('DOMContentLoaded', function() {
                window.trackRoomNestAnalytics('PageView', {
                    city: new URLSearchParams(window.location.search).get('city')
                });
            });
        @endunless
    </script>
    
    <!-- Preconnect to external domains - Mobile Optimized -->
    <link rel="preconnect" href="https://images.unsplash.com" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <!-- Resource Hints for Mobile Performance -->
    <meta http-equiv="x-dns-prefetch-control" content="on">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }};
            --primary-dark: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }};
            --accent: #F59E0B;
            --secondary: {{ \App\Models\Setting::get('secondary_color', '#10B981') }};
            --danger: #EF4444;
            --primary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('primary_color', '#4F46E5'), '#'), '%02x%02x%02x')) }};
            --secondary-rgb: {{ implode(',', sscanf(ltrim(\App\Models\Setting::get('secondary_color', '#10B981'), '#'), '%02x%02x%02x')) }};
            --gray-light: #F8FAFC;
            --bg-premium: #F8FAFC;
            --text-main: #1E293B;
            --text-dark: #0F172A;
            --border: #E2E8F0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
        }
    </style>

    <!-- Anti-Inspection Shield (Only for guests/users in production) -->
    @if(app()->environment('production'))
        @auth
            @if(Auth::user()->role !== 'admin' && Auth::user()->role !== 'owner')
                <script>
                    document.addEventListener('contextmenu', event => event.preventDefault());
                    document.onkeydown = function(e) {
                        if(e.keyCode == 123) return false; // F12
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false; 
                        if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false; 
                    };
                    setInterval(function() { console.clear(); }, 1000);
                </script>
            @endif
        @else
            <script>
                document.addEventListener('contextmenu', event => event.preventDefault());
                document.onkeydown = function(e) {
                    if(e.keyCode == 123) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) return false; 
                    if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false; 
                };
                setInterval(function() { console.clear(); }, 1000);
            </script>
        @endauth
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-premium);
            color: var(--text-main);
            overflow-x: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        .font-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
    
    <!-- Critical Inline CSS (Prevents FOUC when Tailwind is deferred) - Mobile Optimized -->
    <style>
        :root { --primary: {{ \App\Models\Setting::get('primary_color', '#4F46E5') }}; --secondary: {{ \App\Models\Setting::get('secondary_color', '#10B981') }}; }
        @media (max-width: 1023px) {
            .hero-mobile { background: var(--primary); min-height: 300px; display: flex; align-items: center; justify-content: center; }
            img[loading="lazy"] { content-visibility: auto; }
        }
        @media (min-width: 1024px) {
            .hero-mobile { display: none !important; }
        }
        .loading-overlay { position: fixed; inset: 0; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center; }
        @font-face { font-family: 'Font Awesome 6 Free'; font-display: swap; }
    </style>
    
    <!-- Defer Heavy Assets (Non-blocking) - Mobile Optimized -->
    <link rel="preload" href="{{ asset('assets/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}"></noscript>
    <script>
        // Load Font Awesome asynchronously for better mobile performance
        (function() {
            var link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = '{{ asset('assets/css/all.min.css') }}';
            link.media = 'print';
            link.onload = function() { this.media = 'all'; };
            document.head.appendChild(link);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}" media="print" onload="this.media='all'">

    <!-- Custom Styles -->
    <style>
        html, body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
        }
        
        footer {
            margin-top: auto;
        }

        #toast-container {
            position: fixed;
            z-index: 99999;
            pointer-events: none;
        }

        #toast-container.toast-top-right {
            top: 1rem;
            right: 1rem;
        }

        #toast-container > .toast {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 3.25rem;
            width: min(22rem, calc(100vw - 2rem));
            margin: 0 0 .875rem;
            padding: .95rem 2.75rem .95rem 3.25rem;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: .5rem;
            background-color: #fff !important;
            background-image: none !important;
            box-shadow: 0 14px 34px rgba(15, 23, 42, .16), 0 3px 8px rgba(15, 23, 42, .08);
            color: #111827;
            font-family: inherit;
            font-size: .875rem;
            line-height: 1.35;
            opacity: 1;
            pointer-events: auto;
        }

        #toast-container > .toast::before {
            position: absolute;
            left: 1rem;
            top: 50%;
            display: grid;
            width: 1.45rem;
            height: 1.45rem;
            place-items: center;
            border-radius: 999px;
            color: #fff;
            font-family: "Font Awesome 6 Free";
            font-size: .8rem;
            font-weight: 900;
            transform: translateY(-50%);
        }

        #toast-container > .toast-success::before { content: "\f00c"; background: #22c55e; }
        #toast-container > .toast-error::before { content: "\f00d"; background: #ef4444; }
        #toast-container > .toast-warning::before { content: "\f071"; background: #eab308; }
        #toast-container > .toast-info::before { content: "\f129"; background: #38bdf8; }

        #toast-container > .toast-success { border-bottom-color: #22c55e; }
        #toast-container > .toast-error { border-bottom-color: #ef4444; }
        #toast-container > .toast-warning { border-bottom-color: #eab308; }
        #toast-container > .toast-info { border-bottom-color: #38bdf8; }

        #toast-container .toast-title {
            margin-right: .25rem;
            color: #111827;
            font-weight: 800;
        }

        #toast-container .toast-message {
            color: #111827;
            font-weight: 500;
        }

        #toast-container .toast-close-button {
            position: absolute;
            top: 50%;
            right: .9rem;
            width: 1.25rem;
            height: 1.25rem;
            margin: 0;
            color: #6b7280;
            font-size: 1.25rem;
            font-weight: 400;
            line-height: 1;
            text-shadow: none;
            transform: translateY(-50%);
            opacity: 1;
        }

        #toast-container .toast-progress {
            height: .2rem;
            opacity: 1;
        }

        #toast-container .toast-success .toast-progress { background-color: #22c55e; }
        #toast-container .toast-error .toast-progress { background-color: #ef4444; }
        #toast-container .toast-warning .toast-progress { background-color: #eab308; }
        #toast-container .toast-info .toast-progress { background-color: #38bdf8; }

        @media (max-width: 640px) {
            #toast-container.toast-top-right {
                top: 1rem;
                right: 1rem;
                left: 1rem;
            }

            #toast-container > .toast {
                width: 100%;
                padding-right: 2.5rem;
            }
        }

        .site-footer {
            background-color: #111827 !important;
            color: #94a3b8;
            border-top-color: #1f2937 !important;
            padding-top: 4.5rem !important;
        }

        .navbar-brand-logo,
        .footer-brand-logo {
            height: 2.5rem;
            width: auto;
            object-fit: contain;
            transform: scale(1.7);
            transform-origin: left center;
        }

        .footer-brand-logo {
            margin-bottom: 0.875rem;
        }

        /* Keep the desktop navbar identical for guests and signed-in users. */
        .desktop-navbar-inner {
            width: 100%;
            padding-inline: 3rem;
        }

        .desktop-navbar-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            height: 4rem;
            overflow: visible;
        }

        .desktop-navbar-logo {
            justify-self: start;
        }

        .desktop-navbar-menu {
            justify-self: center;
        }

        .desktop-navbar-menu a {
            font-size: 0.875rem !important;
            line-height: 1.25rem;
        }

        .desktop-navbar-actions {
            justify-self: end;
            overflow: visible;
        }

        .desktop-navbar-actions a {
            font-size: 0.875rem;
        }

        .theme-brand-mark,
        .theme-primary-button {
            background: var(--primary);
            color: #fff;
        }

        .theme-primary-button:hover {
            background: var(--primary-dark);
            filter: brightness(.94);
        }

        .theme-secondary-button {
            background: rgba(var(--primary-rgb), .08);
            border-color: rgba(var(--primary-rgb), .2);
            color: var(--primary);
        }

        .theme-secondary-button:hover {
            background: rgba(var(--primary-rgb), .14);
        }

        .theme-brand-text,
        .theme-nav-link:hover,
        .theme-nav-link-active,
        .theme-account-link:hover,
        .theme-footer-link:hover,
        .theme-footer-accent,
        .theme-footer-contact:hover {
            color: var(--primary);
        }

        .theme-nav-link-active {
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        }

        .theme-account-link:hover {
            background: rgba(var(--primary-rgb), .08);
        }

        .theme-account-icon {
            color: rgba(var(--primary-rgb), .68);
        }

        .theme-newsletter {
            background: rgba(var(--primary-rgb), .07);
            border-color: rgba(var(--primary-rgb), .14);
        }

        .theme-newsletter-icon,
        .theme-social-link:hover {
            background: var(--primary);
            color: #fff;
        }

        .theme-newsletter-form:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), .14);
        }

        .site-footer > .container > .grid p,
        .site-footer > .container > .grid ul {
            font-size: 0.875rem !important;
            line-height: 1.5rem;
        }

        .site-footer > .container > .grid h4 {
            font-size: 0.9375rem !important;
        }

        .site-footer > .container > .grid ul {
            font-weight: 600;
        }

        .site-footer > .container > .grid .fa-brands,
        .site-footer > .container > .grid .fas,
        .site-footer > .container > .grid .far {
            font-size: 0.8125rem;
        }

        .site-footer > .container > .border-t p,
        .site-footer > .container > .border-t span {
            font-size: 0.8125rem !important;
        }

        .complaint-page-main {
            min-width: 0;
            max-width: 100%;
            overflow-x: hidden;
            min-height: calc(100vh - 4rem);
            padding-bottom: 3rem;
            background: #f8fafc;
        }

        .complaint-page-content {
            width: 100%;
            max-width: 80rem;
            margin-inline: auto;
            padding: 1.5rem;
        }

        .complaint-detail-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 20rem;
            gap: 1.25rem;
            align-items: start;
        }

        .complaint-detail-grid > section,
        .complaint-detail-grid > aside,
        .complaint-detail-grid > section > *,
        .complaint-detail-grid > aside > * {
            min-width: 0;
            max-width: 100%;
        }

        .complaint-detail-grid p,
        .complaint-detail-grid dd,
        .complaint-detail-grid a {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        @media (max-width: 1279px) {
            .complaint-detail-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 639px) {
            .complaint-page-content {
                padding: 1rem;
            }
        }

        @media (max-width: 1279px) {
            .desktop-navbar-inner {
                padding-inline: 1.5rem;
            }
        }
        /* App-like Bottom Navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: none;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.05);
            padding-bottom: env(safe-area-inset-bottom, 15px);
        }
        
        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #334155; /* Improved contrast from #64748b */
            text-decoration: none;
            font-size: 10px;
            font-weight: 600;
            transition: all 0.2s;
            flex: 1;
        }
        
        .bottom-nav-item i {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .bottom-nav-item.active {
            color: var(--secondary);
        }
        
        .bottom-nav-item.active i {
            transform: translateY(-2px);
        }

        /* Mobile Header */
        .mobile-app-header {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #fff;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            display: none;
            align-items: center;
            justify-content: space-between;
        }

        @media (max-width: 1023px) {
            body.mobile-app-view {
                padding-bottom: 70px; /* Space for bottom nav */
            }
        }
         
        @media (max-width: 1023px) {
            body.mobile-app-view main {
                padding-top: 0 !important;
            }
        }
        
        @media (max-width: 1023px) {
            .bottom-nav, .mobile-app-header {
                display: flex;
            }
            footer {
                display: none !important;
            }
        }
        
        /* Enhanced mobile app styles */
        .mobile-app-view .app-card {
            margin: 8px;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .mobile-app-view .app-btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .mobile-app-view .app-btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .cms-content-main {
            flex: 0 0 auto !important;
        }
    </style>


    <style>
        .page-scroll-progress {
            position: fixed;
            z-index: 1100;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary);
            pointer-events: none;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 flex flex-col min-h-screen mobile-app-view {{ request()->routeIs('admin.*') ? 'admin-page' : '' }}">
    <div id="page-scroll-progress" class="page-scroll-progress" role="progressbar" aria-label="Page scroll progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
    @php
        try {
            $publishedCmsSlugs = \App\Models\CmsPage::published()->pluck('slug')->flip();
        } catch (\Throwable $exception) {
            $publishedCmsSlugs = collect();
        }
        $cmsPageLive = fn (string $slug): bool => $publishedCmsSlugs->has($slug);
    @endphp
    @unless(request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*', 'plans', 'login', 'register'))
        @include('partials.offer-banner', ['placement' => 'top_nav'])
    @endunless
    
    <!-- Mobile App Header - Enhanced App Style -->
    <div class="mobile-app-header lg:hidden">
        <div class="header-left">
                    @php $mobileLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo'); @endphp
                    @if($mobileLogo)
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('storage/' . $mobileLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white">
                        </a>
                    @else
                        <div class="app-icon">
                            <i class="fas fa-home text-white text-xl"></i>
                        </div>
                    @endif
                <div class="header-content">
                <h1 class="text-lg font-bold text-gray-900 leading-none">{{ \App\Models\Setting::get('website_name', 'RoomRental') }}</h1>
                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Find your perfect stay</p>
                </div>
        </div>
        <div class="header-right">
            <button id="mobile-menu-toggle-app"
                    class="menu-toggle"
                    aria-label="Open navigation menu">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile App Menu - Include the new app-style menu -->
    @include('partials.mobile-app-menu')
    
    
    <!-- Compact Desktop Navigation -->
    <!-- Desktop Navigation (Redesigned) -->
    <nav class="hidden md:block bg-white border-b border-slate-100 shadow-sm sticky top-0 z-40">
        <div class="desktop-navbar-inner">
            <div class="desktop-navbar-row">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="desktop-navbar-logo flex items-center gap-2 overflow-visible">
                    @php
                        $navbarLogo = \App\Models\Setting::get('navbar_logo');
                    @endphp
                    @if($navbarLogo)
                        <img src="{{ asset('storage/' . $navbarLogo) }}"
                             alt="ApnaNest Logo"
                             class="navbar-brand-logo">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="theme-brand-mark w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-home text-lg"></i>
                            </div>
                            <span class="text-xl font-black text-slate-900 tracking-tight">Apna<span class="theme-brand-text">Nest</span></span>
                        </div>
                    @endif
                </a>
                
                <!-- Center Links -->
                <div class="desktop-navbar-menu hidden lg:flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-xl p-1">
                    <a href="{{ route('home') }}" class="theme-nav-link {{ request()->routeIs('home') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Home</a>
                    <a href="{{ route('rooms.index') }}" class="theme-nav-link {{ request()->routeIs('rooms.index', 'rooms.show') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Browse Rooms</a>
                    @if($cmsPageLive('how-it-works'))
                        <a href="{{ route('pages.how-it-works') }}" class="theme-nav-link {{ request()->routeIs('pages.how-it-works') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">How It Works</a>
                    @endif
                    <a href="{{ Auth::check() ? (Auth::user()->role === 'owner' ? route('owner.dashboard') : route('dashboard')) : route('register', ['role' => 'owner']) }}" class="theme-nav-link {{ request()->routeIs('owner.*') || (request()->routeIs('register') && request('role') === 'owner') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">For Owners</a>
                    <a href="{{ route('blogs.index') }}" class="theme-nav-link {{ request()->routeIs('blogs.*') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Blog</a>
                </div>
                
                <!-- Right Side Actions -->
                <div class="desktop-navbar-actions flex items-center justify-end gap-3" style="overflow: visible;">
                    <!-- Wishlist Icon (Heart) -->
                    <a href="{{ route('wishlist.index') }}" class="h-10 w-10 shrink-0 inline-flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors relative" title="My Wishlist">
                        <i class="far fa-heart text-lg"></i>
                    </a>
                    
                    @auth
                        <!-- Account Dropdown - Fixed position via JS to avoid clipping -->
                        @php
                            $isRenter = Auth::user()->role === 'user';
                            $accountHome = Auth::user()->role === 'owner'
                                ? route('owner.dashboard')
                                : (Auth::user()->role === 'admin' ? route('admin.dashboard') : route('home'));
                        @endphp

                        <div x-data="{
                                open: false,
                                top: 0,
                                right: 0,
                                toggle() {
                                    if (!this.open) {
                                        const r = this.$refs.trigger.getBoundingClientRect();
                                        this.top = r.bottom + 8;
                                        this.right = window.innerWidth - r.right;
                                    }
                                    this.open = !this.open;
                                }
                             }"
                             @click.outside="open = false">
                            <!-- Trigger button -->
                            <button x-ref="trigger"
                                    @click="toggle()"
                                    class="theme-nav-link h-10 flex items-center gap-2 text-slate-700 transition-colors duration-200 bg-slate-50 hover:bg-slate-100 px-3 rounded-xl border border-slate-200/60 whitespace-nowrap">
                                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/images/default-avatar.svg') }}"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                <span class="hidden xl:inline text-xs font-semibold">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Dropdown menu — position: fixed, calculated from button rect -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 :style="'position:fixed; top:' + top + 'px; right:' + right + 'px; z-index:9999;'"
                                 class="w-56 rounded-xl bg-white border border-slate-100 shadow-xl py-2"
                                 style="display:none;">
                                @if($isRenter)
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> My Profile
                                    </a>
                                    <a href="{{ route('unlocks.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-address-book text-emerald-500 w-4 text-sm"></i> My Unlocked Contacts
                                    </a>
                                    @if(\App\Models\Setting::get('wallet_enabled', '1') === '1')
                                        <a href="{{ route('wallet') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="theme-account-icon fas fa-wallet w-4 text-sm"></i> My Wallet
                                        </a>
                                    @endif
                                    <a href="{{ route('plans') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-crown text-amber-400 w-4 text-sm"></i> View Plans
                                    </a>
                                    @if(\App\Models\Setting::get('referral_enabled', '1') === '1')
                                        <a href="{{ route('referral.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="fas fa-gift text-emerald-400 w-4 text-sm"></i> Refer & Earn
                                        </a>
                                    @endif
                                    <a href="{{ route('complaints.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-headset text-blue-400 w-4 text-sm"></i> Support Tickets
                                    </a>
                                @else
                                    <a href="{{ $accountHome }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-tachometer-alt w-4 text-sm"></i> Dashboard
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> Profile
                                    </a>
                                @endif

                                <div class="h-px bg-slate-100 my-1 mx-3"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt text-red-400 w-4 text-sm"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Post Property Button for Logged In -->
                        @if(Auth::user()->role === 'owner')
                            <a href="{{ route('rooms.create') }}"
                               class="theme-primary-button h-10 px-4 rounded-xl text-sm font-bold transition-all duration-200 shadow-md flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fas fa-plus text-xs"></i> Post Property
                            </a>
                        @endif
                    @else
                        <!-- Guest Actions -->
                        <a href="{{ route('login') }}" 
                           class="theme-nav-link h-10 inline-flex items-center text-slate-700 font-bold transition-colors duration-200 text-sm px-3 whitespace-nowrap">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="theme-primary-button h-10 inline-flex items-center px-4 rounded-xl text-sm font-bold transition-all duration-200 shadow-md whitespace-nowrap">
                            Sign Up
                        </a>
                        <a href="{{ route('register') }}?role=owner"
                           class="theme-secondary-button h-10 inline-flex items-center border px-4 rounded-xl text-sm font-bold transition-all duration-200 whitespace-nowrap">
                            Post Property
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile App Loading Indicator -->
    @include('partials.mobile-loading')

    <!-- Main Content -->
    <main class="pt-16 md:pt-0 {{ Route::is('pages.*', 'cms-pages.show') ? 'cms-content-main' : '' }}">
        @yield('content')
    </main>

    <!-- Stay Updated Banner Section -->
    @if(!Route::is('home') && !Route::is('pages.*') && !Route::is('cms-pages.show') && !Route::is('admin.*') && !Route::is('owner.*') && !Route::is('complaints.*') && !Route::is('rooms.create', 'rooms.edit') && !Route::is('dashboard', 'profile.edit', 'wallet', 'referral.index', 'plans', 'unlocks.index', 'login', 'register'))
    <div class="theme-newsletter hidden lg:block border-t py-8">
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="theme-newsletter-icon w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                    <i class="far fa-envelope-open text-xl"></i>
                </div>
                <div>
                    <h4 class="text-slate-900 font-bold text-lg leading-tight">Stay Updated</h4>
                    <p class="text-slate-600 text-sm">Subscribe to get updates on new rooms and offers.</p>
                </div>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="theme-newsletter-form flex w-full max-w-md bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all">
                @csrf
                <input type="email" name="email" required placeholder="Enter your email" 
                       class="w-full bg-transparent text-slate-800 px-4 py-3 text-sm focus:outline-outline placeholder-slate-400 border-0 outline-none">
                <button type="submit" class="theme-primary-button px-6 font-bold text-sm transition-colors whitespace-nowrap">
                    Subscribe
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Redesigned Footer Section -->
    <footer class="site-footer relative text-slate-400 pt-12 pb-6 hidden lg:block overflow-hidden border-t" @if(Route::is('admin.*') || Route::is('owner.*') || Route::is('complaints.*') || Route::is('rooms.create', 'rooms.edit') || Route::is('dashboard', 'profile.edit', 'wallet', 'referral.index', 'plans', 'unlocks.index', 'login', 'register')) style="display:none !important" @endif>
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                <!-- Brand Info (Col span 3) -->
                <div class="lg:col-span-3 space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group" aria-label="RoomRental Home">
                        @php $footerLogo = \App\Models\Setting::get('footer_logo'); @endphp
                        @if($footerLogo)
                            <img src="{{ asset('storage/' . $footerLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="footer-brand-logo">
                        @else
                            <div class="flex items-center gap-2">
                                <div class="theme-brand-mark w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-home text-lg"></i>
                                </div>
                                <span class="text-xl font-black text-white tracking-tight">Apna<span class="theme-brand-text">Nest</span></span>
                            </div>
                        @endif
                    </a>
                    <p class="text-slate-400 text-xs leading-relaxed font-medium">
                        India's most trusted platform for room rentals. Connect directly with verified owners. Find your stay with zero brokerage.
                    </p>
                    <div class="flex gap-3">
                        @php
                            $socialLinks = [
                                'facebook-f' => \App\Models\Setting::get('facebook_url', '#'),
                                'twitter' => \App\Models\Setting::get('twitter_url', '#'),
                                'instagram' => \App\Models\Setting::get('instagram_url', '#'),
                                'linkedin-in' => \App\Models\Setting::get('linkedin_url', '#')
                            ];
                        @endphp
                        @foreach($socialLinks as $icon => $url)
                            @continue(!$url || $url === '#')
                            <a href="{{ $url }}" aria-label="Visit us on {{ ucfirst(str_replace(['-f', '-in'], '', $icon)) }}" class="theme-social-link w-8 h-8 rounded-lg bg-white/5 text-slate-400 flex items-center justify-center transition-all duration-300 border border-white/5" {{ $url != '#' ? 'target="_blank"' : '' }}>
                                <i class="fa-brands fa-{{ $icon }} text-xs" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Columns -->
                <!-- Discover -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Discover</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li><a href="{{ route('rooms.index') }}" class="text-slate-400 hover:text-white transition-all">Browse Rooms</a></li>
                        <li><a href="{{ route('rooms.index', ['room_type' => [\App\Models\RoomOption::idForKey('room_type', 'shared_room')]]) }}" class="text-slate-400 hover:text-white transition-all">PG</a></li>
                        <li><a href="{{ route('rooms.index', ['room_type' => [\App\Models\RoomOption::idForKey('room_type', '1bhk')]]) }}" class="text-slate-400 hover:text-white transition-all">Apartments</a></li>
                        @if($cmsPageLive('how-it-works'))<li><a href="{{ route('pages.how-it-works') }}" class="text-slate-400 hover:text-white transition-all">How It Works</a></li>@endif
                        <li><a href="{{ route('plans') }}" class="text-slate-400 hover:text-white transition-all">Pricing</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Company</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        @if($cmsPageLive('about-us'))<li><a href="{{ route('pages.about') }}" class="text-slate-400 hover:text-white transition-all">About Us</a></li>@endif
                        @if($cmsPageLive('careers'))<li><a href="{{ route('pages.careers') }}" class="text-slate-400 hover:text-white transition-all">Careers</a></li>@endif
                        @if($cmsPageLive('terms-and-conditions'))<li><a href="{{ route('pages.terms') }}" class="text-slate-400 hover:text-white transition-all">Terms of Service</a></li>@endif
                        @if($cmsPageLive('privacy-policy'))<li><a href="{{ route('pages.privacy') }}" class="text-slate-400 hover:text-white transition-all">Privacy Policy</a></li>@endif
                        @if($cmsPageLive('owner-guidelines'))<li><a href="{{ route('pages.owner-guidelines') }}" class="text-slate-400 hover:text-white transition-all">Owner Guidelines</a></li>@endif
                        @if($cmsPageLive('user-guidelines'))<li><a href="{{ route('pages.user-guidelines') }}" class="text-slate-400 hover:text-white transition-all">User Guidelines</a></li>@endif
                        @if($cmsPageLive('contact-us'))<li><a href="{{ route('pages.contact') }}" class="text-slate-400 hover:text-white transition-all">Contact Us</a></li>@endif
                    </ul>
                </div>

                <!-- Support -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Support</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        @if($cmsPageLive('faq'))<li><a href="{{ route('pages.faq') }}" class="text-slate-400 hover:text-white transition-all">Help Center</a></li>@endif
                        @if($cmsPageLive('how-it-works'))<li><a href="{{ route('pages.how-it-works') }}" class="text-slate-400 hover:text-white transition-all">How It Works</a></li>@endif
                        @if($cmsPageLive('safety-tips'))<li><a href="{{ route('pages.safety-tips') }}" class="text-slate-400 hover:text-white transition-all">Safety Tips</a></li>@endif
                        <li><a href="{{ Auth::check() ? route('complaints.create') : route('login') }}" class="text-slate-400 hover:text-white transition-all">Report an Issue</a></li>
                    </ul>
                </div>

                @php
                    $playStoreUrl = trim((string) \App\Models\Setting::get('play_store_url'));
                    $appStoreUrl = trim((string) \App\Models\Setting::get('app_store_url'));
                @endphp
                @if($playStoreUrl || $appStoreUrl)
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Download Our App</h4>
                    <p class="mb-4 text-xs font-medium leading-relaxed text-slate-400">Get the ApnaNest app for a better room-search experience.</p>
                    <div class="flex flex-wrap gap-2">
                        @if($playStoreUrl)
                            <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/10">
                                <i class="fab fa-google-play text-base"></i><span>Google Play</span>
                            </a>
                        @endif
                        @if($appStoreUrl)
                            <a href="{{ $appStoreUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/10">
                                <i class="fab fa-apple text-lg"></i><span>App Store</span>
                            </a>
                        @endif
                    </div>
                </div>
                @else
                <!-- Contact -->
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Contact</h4>
                    <ul class="space-y-3 text-xs font-semibold">
                        <li class="flex items-center gap-2 text-white">
                            <i class="theme-footer-accent fas fa-phone-alt"></i>
                            <a href="tel:{{ \App\Models\Setting::get('contact_phone', '+911234567890') }}" class="theme-footer-contact font-bold">{{ \App\Models\Setting::get('contact_phone', '+91 12345 67890') }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="theme-footer-accent far fa-envelope"></i>
                            <a href="mailto:{{ \App\Models\Setting::get('contact_email', 'support@apnanest.com') }}" class="hover:text-white transition-all">{{ \App\Models\Setting::get('contact_email', 'support@apnanest.com') }}</a>
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            <i class="theme-footer-accent far fa-clock"></i>
                            <span>Mon - Sun: 9AM - 8PM</span>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <!-- Footer Bottom -->
            <div class="pt-6 border-t border-slate-900 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-500 font-semibold">
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::get('website_name', 'ApnaNest') }}. All rights reserved.
                </p>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                        <i class="fas fa-shield-alt text-emerald-500" aria-hidden="true"></i> Secure Payments
                    </span>
                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                        <i class="theme-footer-accent fas fa-check-circle" aria-hidden="true"></i> Verified Listings
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- App Bottom Navigation - Enhanced Design -->
    <!-- App Bottom Navigation -->
    @include('partials.mobile-bottom-nav')


    <!-- Google Ads Conversion Tracking (Fire after successful payment) -->
    @if(session('google_ads_conversion') && app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
        @php
            $conv = session('google_ads_conversion');
            $convLabel = \App\Models\Setting::get('google_ads_conversion_label');
            $adsId = \App\Models\Setting::get('google_ads_tag_id');
        @endphp
        @if($adsId && $convLabel && isset($conv['amount']))
        <script>
            if (typeof trackAdsConversion === 'function') {
                trackAdsConversion('{{ $convLabel }}', {{ $conv['amount'] }}, 'INR');
            } else if (typeof gtag !== 'undefined') {
                gtag('event', 'conversion', {
                    'send_to': '{{ $adsId }}/{{ $convLabel }}',
                    'value': {{ $conv['amount'] }},
                    'currency': 'INR'
                });
            }
        </script>
        @endif
        {{ session()->forget('google_ads_conversion') }}
    @endif

    <!-- Scripts Loaded in Footer for Performance -->
    @stack('sweetalert') {{-- Only load SweetAlert2 when needed --}}
    <script defer>
        // Suppress console warnings from third-party libraries (Tailwind, Google Maps)
        const originalConsoleWarn = console.warn;
        console.warn = function (message) {
            if (typeof message === 'string' && (
                message.includes('cdn.tailwindcss.com should not be used in production') ||
                message.includes('google.maps.places.Autocomplete is not available to new customers') ||
                message.includes('google.maps.Marker is deprecated')
            )) {
                return;
            }
            originalConsoleWarn.apply(console, arguments);
        };

        // Global Utility for Distance Calculation (Haversine Formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            if (!lat1 || !lon1 || !lat2 || !lon2) return null;
            const R = 6371; // Radius of the earth in km
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a =
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return (R * c).toFixed(1);
        }

        // Global User Location Tracking
        let userCoords = null;
        function detectUserLocation(callback) {
            if (sessionStorage.getItem('user_lat') && sessionStorage.getItem('user_lng')) {
                userCoords = {
                    lat: parseFloat(sessionStorage.getItem('user_lat')),
                    lng: parseFloat(sessionStorage.getItem('user_lng'))
                };
                if (callback) callback(userCoords);
                return;
            }

            const isSecure = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

            if (navigator.geolocation && isSecure) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        userCoords = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        sessionStorage.setItem('user_lat', userCoords.lat);
                        sessionStorage.setItem('user_lng', userCoords.lng);

                        if (callback) callback(userCoords);
                    },
                    (error) => {
                        getLocationByIP(callback);
                    },
                    { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
                );
            } else {
                getLocationByIP(callback);
            }
        }

        function getLocationByIP(callback) {
            fetch('https://ipapi.co/json/')
                .then(res => res.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        userCoords = { lat: data.latitude, lng: data.longitude };
                        sessionStorage.setItem('user_lat', userCoords.lat);
                        sessionStorage.setItem('user_lng', userCoords.lng);

                        if (callback) callback(userCoords);
                    }
                })
                .catch(err => {
                    fetch('http://ip-api.com/json/')
                        .then(res => res.json())
                        .then(data => {
                            if (data.lat && data.lon) {
                                userCoords = { lat: data.lat, lng: data.lon };
                                sessionStorage.setItem('user_lat', userCoords.lat);
                                sessionStorage.setItem('user_lng', userCoords.lng);
                                if (callback) callback(userCoords);
                            }
                        });
                });
        }

        // Global Razorpay Loader
        window.loadRazorpaySDK = function() {
            return new Promise((resolve, reject) => {
                if (window.Razorpay) {
                    resolve(window.Razorpay);
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://checkout.razorpay.com/v1/checkout.js';
                script.async = true;
                script.onload = () => resolve(window.Razorpay);
                script.onerror = () => reject(new Error('Razorpay SDK failed to load'));
                document.body.appendChild(script);
            });
        };

        document.addEventListener('DOMContentLoaded', () => detectUserLocation());
    </script>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/toastr.min.js') }}"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        @if(Session::has('success'))
            toastr.success(@json(session('success')), 'Success');
        @endif

        @if(Session::has('error'))
            toastr.error(@json(session('error')), 'Error');
        @endif

        @if(Session::has('info'))
            toastr.info(@json(session('info')), 'Info');
        @endif

        @if(Session::has('warning'))
            toastr.warning(@json(session('warning')), 'Warning');
        @endif
    </script>
    <script>
        (() => {
            const progress = document.getElementById('page-scroll-progress');
            if (!progress) return;

            let ticking = false;
            const updateProgress = () => {
                const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
                const value = documentHeight > 0
                    ? Math.min(100, Math.max(0, (window.scrollY / documentHeight) * 100))
                    : 0;

                progress.style.width = value + '%';
                progress.setAttribute('aria-valuenow', Math.round(value));
                ticking = false;
            };

            const requestUpdate = () => {
                if (!ticking) {
                    window.requestAnimationFrame(updateProgress);
                    ticking = true;
                }
            };

            document.addEventListener('scroll', requestUpdate, { passive: true });
            window.addEventListener('resize', requestUpdate, { passive: true });
            requestUpdate();
        })();
    </script>
    @stack('scripts')
    @unless(request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*', 'plans'))
        @include('partials.offer-banner', ['placement' => 'popup'])
    @endunless
    {{-- Google Ads Signup Conversion --}}
    @if(session('signup_success') && app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
        @php
            $signupLabel = \App\Models\Setting::get('google_ads_signup_label');
        @endphp
        @if($signupLabel)
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof trackAdsConversion === 'function') {
                        trackAdsConversion('{{ $signupLabel }}', 0, 'INR');
                    }
                });
            </script>
        @endif
        {{ session()->forget('signup_success') }}
    @endif
</body>
</html>

