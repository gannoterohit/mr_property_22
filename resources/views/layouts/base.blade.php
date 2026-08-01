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
    
    @yield('layout-seo')
    @yield('layout-structured-data')
    @yield('layout-tracking')

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
    @yield('layout-top-banner')
    @yield('layout-navigation')
    @yield('layout-loading')

    <!-- Main Content -->
    <main class="pt-16 md:pt-0 {{ Route::is('pages.*', 'cms-pages.show') ? 'cms-content-main' : '' }}">
        @yield('content')
    </main>

    @yield('layout-footer')
    @yield('layout-bottom-navigation')


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
        window.renderFormErrors = function (form, errors) {
            if (!form || !errors || typeof errors !== 'object') return;

            form.querySelectorAll('[data-validation-error]').forEach(node => node.remove());
            form.querySelectorAll('[data-validation-invalid]').forEach(field => {
                field.removeAttribute('data-validation-invalid');
                field.classList.remove('border-red-500', 'ring-1', 'ring-red-200');
                field.removeAttribute('aria-invalid');
            });

            Object.entries(errors).forEach(([errorKey, messages]) => {
                const baseKey = errorKey.split('.')[0];
                const fields = Array.from(form.elements).filter(field => {
                    if (!field.name) return false;
                    return field.name === errorKey ||
                        field.name === baseKey ||
                        field.name === `${baseKey}[]` ||
                        field.name.startsWith(`${baseKey}[`);
                });
                const field = fields[0];
                if (!field) return;

                fields.forEach(item => {
                    item.dataset.validationInvalid = 'true';
                    item.classList.add('border-red-500', 'ring-1', 'ring-red-200');
                    item.setAttribute('aria-invalid', 'true');
                });

                const message = document.createElement('p');
                message.dataset.validationError = 'true';
                message.className = 'mt-1 text-xs font-semibold text-red-600';
                message.setAttribute('role', 'alert');
                message.textContent = Array.isArray(messages) ? messages[0] : messages;

                const anchor = field.closest('label') || field;
                anchor.insertAdjacentElement('afterend', message);
            });
        };

        window.restoreOldFormInput = function (oldInput) {
            if (!oldInput || typeof oldInput !== 'object') return;

            const flatten = (value, prefix = '', result = {}) => {
                if (Array.isArray(value) && value.some(item => item !== null && typeof item === 'object')) {
                    value.forEach((child, index) => flatten(child, `${prefix}[${index}]`, result));
                } else if (value !== null && typeof value === 'object' && !Array.isArray(value)) {
                    Object.entries(value).forEach(([key, child]) => flatten(child, prefix ? `${prefix}[${key}]` : key, result));
                } else {
                    result[prefix] = value;
                }
                return result;
            };

            const flattened = flatten(oldInput);
            Object.entries(flattened).forEach(([name, value]) => {
                const baseName = name.split('[')[0];
                const fields = Array.from(document.querySelectorAll('[name]')).filter(field =>
                    field.name === name || field.name === `${baseName}[]`
                );

                fields.forEach(field => {
                    if (['file', 'password'].includes(field.type) || ['_token', '_method'].includes(field.name)) return;
                    if (field.type === 'checkbox' || field.type === 'radio') {
                        const values = Array.isArray(value) ? value.map(String) : [String(value)];
                        field.checked = values.includes(String(field.value));
                    } else if (field.multiple && Array.isArray(value)) {
                        Array.from(field.options).forEach(option => option.selected = value.map(String).includes(option.value));
                    } else if (!Array.isArray(value)) {
                        field.value = value ?? '';
                    }
                });
            });
        };

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

        @if(isset($errors) && $errors->any())
            @foreach($errors->all() as $validationError)
                toastr.error(@json($validationError), 'Please check the form');
            @endforeach

            document.addEventListener('DOMContentLoaded', () => {
                const validationErrors = @json($errors->toArray());
                const oldInput = @json(session()->getOldInput());
                window.restoreOldFormInput(oldInput);

                document.querySelectorAll('form').forEach(form => {
                    const relevantErrors = {};
                    Object.entries(validationErrors).forEach(([key, messages]) => {
                        const baseKey = key.split('.')[0];
                        if (Array.from(form.elements).some(field => field.name === key || field.name === baseKey || field.name === `${baseKey}[]` || field.name?.startsWith(`${baseKey}[`))) {
                            relevantErrors[key] = messages;
                        }
                    });
                    window.renderFormErrors(form, relevantErrors);
                });
            });
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
    @yield('layout-popup')
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
