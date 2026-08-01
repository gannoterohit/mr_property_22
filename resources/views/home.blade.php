@extends('layouts.app')

@php
    $homeCity = request('city') ?? session('user_city');
    $homeText = fn (string $key, string $default = '') => \App\Models\Setting::get($key, $default);
    $homeWhyCards = collect(range(1, 4))->map(fn ($i) => [
        'title' => $homeText("home_why_{$i}_title", ['Verified Listings', 'Zero Brokerage', 'Secure Payments', 'Customer Support'][$i - 1]),
        'description' => $homeText("home_why_{$i}_description", ['Listings reviewed for authenticity.', 'Connect directly with property owners.', 'Protected payments through trusted gateways.', 'Support throughout your rental journey.'][$i - 1]),
        'icon' => $homeText("home_why_{$i}_icon", ['fa-circle-check', 'fa-wallet', 'fa-shield-halved', 'fa-headset'][$i - 1]),
    ]);
    $homeSteps = collect(range(1, 3))->map(fn ($i) => [
        'title' => $homeText("home_step_{$i}_title", ['Search', 'Connect', 'Move In'][$i - 1]),
        'description' => $homeText("home_step_{$i}_description", ['Find rooms by city, budget and preference.', 'Review details and connect with the owner.', 'Verify the property, complete documentation and move in.'][$i - 1]),
    ]);
    $homeTestimonials = collect(range(1, 2))->map(fn ($i) => [
        'name' => $homeText("home_testimonial_{$i}_name", $i === 1 ? 'Rahul Sharma' : 'Neha Verma'),
        'role' => $homeText("home_testimonial_{$i}_role", $i === 1 ? 'Student' : 'Working Professional'),
        'text' => $homeText("home_testimonial_{$i}_text", 'A simple and reliable room-finding experience.'),
    ]);
@endphp
@section('title', ((request('city') ?? session('user_city')) ? 'Verified Rooms & PG in ' . (request('city') ?? session('user_city')) : 'Browse Rooms & PG for Rent') . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', ((request('city') ?? session('user_city')) ? 'Find verified rooms, apartments and PG in ' . (request('city') ?? session('user_city')) . '.' : 'Browse verified room listings and connect with property owners.'))
@section('keywords', ((request('city') ?? session('user_city')) ? 'pg in ' . (request('city') ?? session('user_city')) . ', room for rent, ' : '') . \App\Models\Setting::get('seo_meta_keywords', 'rooms, apartment, rental property'))
@section('og_title', ((request('city') ?? session('user_city')) ? 'Rooms & PG in ' . (request('city') ?? session('user_city')) : 'Browse Rooms') . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('og_description', ((request('city') ?? session('user_city')) ? 'Browse available rooms in ' . (request('city') ?? session('user_city')) . '.' : 'Browse verified room listings in your city.'))
@section('og_url', route('rooms.index', request()->all()))
@section('canonical', route('rooms.index'))

@push('styles')
@include('partials.listings-ld')
<link rel="preload" href="{{ asset('assets/images/hero-bg-desktop.webp') }}" as="image" fetchpriority="high" media="(min-width: 768px)">
<style>
    /* ===== HERO ANIMATIONS ===== */
    @media (max-width: 1023px) {
        .navbar, footer, .hero-mobile { display: none !important; }
        body { padding-bottom: 70px; background-color: #f8fafc; }
    }
    /* Float animation for cards */
    @keyframes floatY {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-10px); }
    }
    @keyframes floatY2 {
        0%, 100% { transform: translateY(0px); }
        50%       { transform: translateY(-7px); }
    }
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeSlideRight {
        from { opacity: 0; transform: translateX(40px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulseRing {
        0%   { box-shadow: 0 0 0 0 rgba(249,115,22,.4); }
        70%  { box-shadow: 0 0 0 12px rgba(249,115,22,0); }
        100% { box-shadow: 0 0 0 0 rgba(249,115,22,0); }
    }
    .float-card-1 { animation: floatY  3.5s ease-in-out infinite; }
    .float-card-2 { animation: floatY2 4.0s ease-in-out infinite 0.8s; }
    .float-card-3 { animation: floatY  3.2s ease-in-out infinite 1.8s; }
    .hero-left    { animation: fadeSlideUp    0.7s ease both; }
    .hero-right   { animation: fadeSlideRight 0.8s ease 0.2s both; }
    .pulse-orange { animation: pulseRing 2s infinite; }

    /* Gradient mesh background */
    .hero-mesh-bg {
        background: #ffffff;
        background-image:
            radial-gradient(ellipse at 80% 20%, rgba(99,102,241,.07) 0%, transparent 55%),
            radial-gradient(ellipse at 10% 80%, rgba(249,115,22,.06) 0%, transparent 50%);
    }

    /* Stat counter shimmer */
    @keyframes statFadeIn {
        from { opacity:0; transform:translateY(16px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .stat-card { animation: statFadeIn 0.6s ease both; }
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }

    /* Scroll-reveal */
    .reveal { opacity:0; transform:translateY(28px); transition: opacity .6s ease, transform .6s ease; }
    .reveal.in-view { opacity:1; transform:translateY(0); }

    /* Why-card hover lift */
    .why-card { transition: transform .25s ease, box-shadow .25s ease; }
    .why-card:hover { transform: translateY(-5px); box-shadow: 0 16px 40px -8px rgba(99,102,241,.18); }

    /* Glass float card */
    .glass-card {
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,0.7);
    }

    /* Professional landing-page visual system */
    @media (min-width: 768px) {
        main > div:not(.md\:hidden) .container,
        main > section .container { max-width: 1280px; }
        main > section { padding-top: 56px !important; padding-bottom: 56px !important; }
    }
    .hero-mesh-bg {
        padding-top: 44px !important;
        padding-bottom: 64px !important;
        background-color:#f8fafc;
        background-image:linear-gradient(180deg, rgba(var(--primary-rgb),.055), rgba(255,255,255,.95) 55%, #fff) !important;
        border-bottom:1px solid #e8edf4;
    }
    .hero-mesh-bg > .absolute { display:none; }
    .hero-left, .hero-right, .float-card-1, .float-card-2, .float-card-3 { animation:none !important; }
    .pulse-orange { animation:none !important; }
    .why-card:hover { transform:translateY(-2px); box-shadow:0 12px 30px -18px rgba(15,23,42,.28); }
    main .bg-orange-500 { background-color:var(--secondary) !important; }
    main .hover\:bg-orange-600:hover { background-color:var(--secondary) !important; filter:brightness(.92); }
    main .text-orange-500, main .text-orange-400, main .hover\:text-orange-600:hover { color:var(--secondary) !important; }
    main .border-orange-400, main .focus\:border-orange-400:focus { border-color:var(--secondary) !important; }
    main .shadow-orange-400\/30, main .shadow-orange-500\/25 { --tw-shadow-color:rgba(var(--secondary-rgb),.18) !important; }
    main h2, main h3 { letter-spacing:-.02em; }
    main section .rounded-\[32px\] { border-radius:20px !important; }
    main section .rounded-3xl { border-radius:18px !important; }
    main .shadow-xl, main .shadow-lg { box-shadow:0 12px 35px -22px rgba(15,23,42,.30) !important; }
    main .reveal { opacity:1; transform:none; }
    @media (prefers-reduced-motion: reduce) { .reveal, .why-card, .hero-left, .hero-right { animation:none !important; transition:none !important; } }

    /* Landing micro-interactions */
    .why-card, .category-card, .trust-tile, .step-item, .testimonial-card, .app-download-card, .owner-cta {
        transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease, background-color .22s ease;
    }
    .why-card:hover, .category-card:hover, .testimonial-card:hover { transform:translateY(-4px); }
    .why-card > div:first-child, .category-card .category-icon, .trust-tile > div:first-child, .step-item > div:first-child {
        transition:transform .24s cubic-bezier(.2,.8,.2,1), background-color .2s ease, color .2s ease;
    }
    .why-card:hover > div:first-child { transform:scale(1.08) rotate(-3deg); }
    .category-card:hover .category-icon { transform:scale(1.08) rotate(3deg); background:var(--primary) !important; color:#fff !important; }
    .category-card:active, .why-card:active, .trust-tile:active, .step-item:active, .testimonial-card:active { transform:scale(.985); }
    .trust-tile:hover { transform:translateY(-3px); background:rgba(255,255,255,.09); border-color:rgba(255,255,255,.12); }
    .trust-tile:hover > div:first-child { transform:scale(1.08); }
    .step-item { border-radius:14px; padding:10px; margin:-10px; }
    .step-item:hover { background:rgba(var(--primary-rgb),.05); transform:translateX(4px); }
    .step-item:hover > div:first-child { transform:scale(1.08); background:var(--primary) !important; color:#fff !important; }
    .testimonial-card:hover { border-color:rgba(var(--primary-rgb),.22); box-shadow:0 12px 25px -20px rgba(15,23,42,.45); }
    .app-download-card:hover { border-color:rgba(var(--primary-rgb),.2); box-shadow:0 14px 28px -22px rgba(15,23,42,.4); }
    .app-download-card:hover .fa-qrcode { transform:scale(1.04); }
    .app-download-card .fa-qrcode { transition:transform .22s ease; }
    .owner-cta:hover { transform:translateY(-2px); box-shadow:0 22px 45px -28px rgba(15,23,42,.55) !important; }
    main a[class*="rounded"], main button[class*="rounded"] { transition:transform .16s ease, filter .16s ease, background-color .16s ease, color .16s ease, border-color .16s ease; }
    main a[class*="rounded"]:active, main button[class*="rounded"]:active { transform:scale(.975); }
    @media (prefers-reduced-motion: reduce) {
        .why-card, .category-card, .trust-tile, .step-item, .testimonial-card, .app-download-card, .owner-cta,
        .why-card > div:first-child, .category-card .category-icon { transition:none !important; transform:none !important; }
    }
    .landing-hero { background:#f8fafc; border-bottom:1px solid #e2e8f0; }
    .landing-hero-shell { position:relative; min-height:500px; border-radius:24px; overflow:hidden; background:#0f172a; box-shadow:0 24px 60px -32px rgba(15,23,42,.48); }
    .landing-hero-photo { position:absolute; inset:0 0 0 43%; background-position:center; background-size:cover; }
    .landing-hero-photo:after { content:""; position:absolute; inset:0; background:linear-gradient(90deg,#0f172a 0%,rgba(15,23,42,.72) 25%,rgba(15,23,42,.08) 68%,rgba(15,23,42,.18) 100%); }
    .landing-hero-copy { position:relative; z-index:2; width:57%; padding:52px 48px 124px; color:#fff; }
    .landing-search-panel { position:absolute; z-index:4; left:40px; right:40px; bottom:32px; padding:16px; border-radius:18px; background:rgba(255,255,255,.97); box-shadow:0 18px 45px -25px rgba(15,23,42,.55); border:1px solid rgba(255,255,255,.65); }
    .landing-search-grid { display:grid; grid-template-columns:1.25fr 1fr 1fr 1fr auto; gap:10px; align-items:end; }
    .landing-search-panel input,.landing-search-panel select { height:44px; width:100%; border:1px solid #e2e8f0 !important; border-radius:10px !important; background:#f8fafc !important; font-size:12px; font-weight:600; color:#334155; }
    .landing-search-panel label { display:block; margin-bottom:6px; font-size:9px; line-height:1; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#64748b; }
    .landing-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border:1px solid #e2e8f0; border-radius:18px; background:#fff; box-shadow:0 10px 35px -28px rgba(15,23,42,.5); }
    .landing-stat { display:flex; align-items:center; justify-content:center; gap:12px; min-height:84px; padding:16px; border-right:1px solid #e2e8f0; }
    .landing-stat:last-child { border-right:0; }
    @media(max-width:1023px){.landing-hero-copy{width:68%;padding-left:32px}.landing-hero-photo{left:35%}.landing-search-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.landing-search-panel{position:relative;left:auto;right:auto;bottom:auto;margin:0 24px 24px}.landing-hero-copy{padding-bottom:36px}.landing-hero-shell{min-height:auto}.landing-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.landing-stat:nth-child(2){border-right:0}.landing-stat:nth-child(-n+2){border-bottom:1px solid #e2e8f0}}
    /* Compact, market-ready landing page rhythm */
    @media (min-width:1280px) {
        main > section .container,
        main > div .container { width:calc(100% - 48px) !important; max-width:1600px !important; padding-left:0 !important; padding-right:0 !important; }
    }
    main > section:not(.landing-hero) { padding-top:40px !important; padding-bottom:40px !important; }
    main > div.bg-white.py-16 { padding-top:40px !important; padding-bottom:40px !important; }
    main > section .container > .text-center.mb-12 { margin-bottom:28px !important; }
    main > section .mb-8, main > div .mb-8 { margin-bottom:24px !important; }
    main > section .gap-12 { gap:28px !important; }
    main > section .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top:20px !important; }
    main > section h2 { font-size:1.65rem !important; line-height:2rem !important; }
    .why-card { padding:20px !important; }
    .why-card > div:first-child { width:42px !important; height:42px !important; margin-bottom:12px !important; }
    .category-card { padding:16px !important; }
    .popular-area-card { transition:transform .2s ease,border-color .2s ease,box-shadow .2s ease; }
    .popular-area-card:hover { transform:translateY(-3px); border-color:#a5b4fc; box-shadow:0 14px 30px -24px rgba(15,23,42,.55); }
    .reveal.in-view .why-card,.reveal.in-view .category-card,.reveal.in-view .popular-area-card { animation:landingCardIn .5s ease both; }
    .reveal.in-view .why-card:nth-child(2),.reveal.in-view .category-card:nth-child(2),.reveal.in-view .popular-area-card:nth-child(2){animation-delay:.06s}
    .reveal.in-view .why-card:nth-child(3),.reveal.in-view .category-card:nth-child(3),.reveal.in-view .popular-area-card:nth-child(3){animation-delay:.12s}
    .reveal.in-view .why-card:nth-child(4),.reveal.in-view .category-card:nth-child(4),.reveal.in-view .popular-area-card:nth-child(4){animation-delay:.18s}
    @keyframes landingCardIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
    @media(max-width:767px){main > section:not(.landing-hero){padding-top:30px !important;padding-bottom:30px !important}main > div.bg-white.py-16{padding-top:30px !important;padding-bottom:30px !important}main > section h2{font-size:1.4rem !important}.landing-card-animation{animation:none!important}}
</style>
@endpush

@section('content')
<!-- Mobile Search (Visible on Mobile Only) -->
<div class="md:hidden relative bg-white">
    @include('partials.mobile-search')
</div>

<section class="hidden md:block landing-hero py-8">
    <div class="container mx-auto px-6">
        <div class="landing-hero-shell">
            <div class="landing-hero-photo" style="background-image:url('{{ $heroRoom?->photo_url ?: asset('assets/images/hero-bg-desktop.webp') }}')"></div>
            <div class="landing-hero-copy">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-indigo-100"><i class="fas fa-circle-check text-emerald-400"></i>{{ $homeText('home_hero_eyebrow','Verified rooms · Direct owner contact') }}</span>
                <h1 class="mt-5 text-4xl lg:text-5xl font-black leading-[1.08] tracking-tight">{{ $homeText('home_hero_title','Find Verified Rooms') }}<br><span class="text-orange-400">in {{ $homeCity ?: $homeText('home_hero_highlight','Your City') }}</span></h1>
                <p class="mt-4 max-w-lg text-sm leading-6 text-slate-300">{{ $homeText('home_hero_description','Explore verified rooms, PGs and apartments. Unlock owner contact and connect directly—without brokerage.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3 text-xs font-semibold text-slate-200"><span><i class="fas fa-ban mr-1.5 text-orange-400"></i>No brokerage</span><span><i class="fas fa-user-check mr-1.5 text-emerald-400"></i>Verified owners</span><span><i class="fas fa-shield-halved mr-1.5 text-sky-400"></i>Secure payments</span></div>
            </div>
            <form action="{{ route('rooms.index') }}" method="GET" class="landing-search-panel">
                <div class="landing-search-grid">
                    <div><label>Location</label><input type="text" name="city" id="hero-city-input" value="{{ request('city') ?? session('user_city') }}" placeholder="Enter city"></div>
                    <div><label>Property type</label><select name="room_type"><option value="">Any type</option>@foreach($roomCategories as $cat)<option value="{{ $cat->room_type_option_id }}">{{ $cat->label }}</option>@endforeach</select></div>
                    <div><label>Minimum rent</label><input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="₹ Minimum"></div>
                    <div><label>Maximum rent</label><input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="₹ Maximum"></div>
                    <button type="submit" class="h-11 rounded-xl bg-indigo-600 px-7 text-sm font-extrabold text-white hover:bg-indigo-700"><i class="fas fa-search mr-2"></i>Search</button>
                </div>
                @if($popularAreas->count())<div class="mt-3 flex items-center gap-2 overflow-hidden"><span class="shrink-0 text-[10px] font-bold text-slate-400">Popular:</span>@foreach($popularAreas->take(6) as $area)<a href="{{ route('rooms.index',['city'=>$homeCity,'area'=>$area->area_name]) }}" class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600 hover:bg-indigo-50 hover:text-indigo-700">{{ $area->area_name }}</a>@endforeach</div>@endif
            </form>
        </div>
        @include('partials.offer-banner', ['placement' => 'home_hero'])
        @include('partials.adsense-slot', ['placement' => 'home_top'])

        <div class="landing-stats mt-5">
            @foreach([['fa-house-circle-check',number_format($totalRooms).'+','Verified rooms','bg-indigo-50 text-indigo-600'],['fa-user-check',number_format($totalOwners).'+','Property owners','bg-orange-50 text-orange-600'],['fa-location-dot',number_format($totalAreas).'+','Cities & areas','bg-emerald-50 text-emerald-600'],['fa-star','4.8/5','Average rating','bg-amber-50 text-amber-600']] as $stat)
                <div class="landing-stat"><span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat[3] }}"><i class="fas {{ $stat[0] }}"></i></span><div><strong class="block text-lg text-slate-950">{{ $stat[1] }}</strong><small class="text-xs font-semibold text-slate-500">{{ $stat[2] }}</small></div></div>
            @endforeach
        </div>
    </div>
</section>

@include('partials.adsense-slot', ['placement' => 'home_bottom'])

<!-- ===== DESKTOP HERO ===== -->
<div class="hidden hero-mesh-bg pt-8 pb-16 overflow-hidden relative">
    <!-- Decorative orbs -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-100/30 blur-[120px] pointer-events-none -translate-y-1/4 translate-x-1/4"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full bg-orange-100/40 blur-[100px] pointer-events-none translate-y-1/4 -translate-x-1/4"></div>

    <div class="container mx-auto px-6 relative z-10">
        <!-- Breadcrumb -->
        <div class="flex items-center gap-1.5 text-[11px] font-bold text-slate-400 mb-6">
            <span class="text-indigo-600">{{ $homeText('home_hero_eyebrow', 'Verified Rooms · No Brokerage · Direct Owner Contact') }}</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start justify-between">

            <!-- ===== LEFT CONTENT ===== -->
            <div class="w-full lg:w-[65%] space-y-6 hero-left">

                <!-- Headline -->
                <h1 class="text-4xl lg:text-[50px] font-black text-slate-900 leading-[1.1] tracking-tight">
                    {{ $homeText('home_hero_title', 'Find Verified Rooms') }}<br>
                    @if($homeCity)
                        in <span class="text-orange-500 relative">
                            {{ $homeCity }}
                            <svg class="absolute -bottom-1 left-0 w-full" height="5" viewBox="0 0 200 5" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                <path d="M0 4 Q100 0 200 4" stroke="#f97316" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-500 text-white ml-2 align-middle shadow-md" style="font-size:18px;"><i class="fas fa-check text-sm"></i></span>
                    @else
                        in <span class="text-orange-500 relative">
                            {{ $homeText('home_hero_highlight', 'Your City') }}
                            <svg class="absolute -bottom-1 left-0 w-full" height="5" viewBox="0 0 200 5" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                                <path d="M0 4 Q100 0 200 4" stroke="#f97316" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                            </svg>
                        </span>
                    @endif
                </h1>

                <p class="text-slate-500 text-sm font-medium max-w-xl leading-relaxed">
                    {{ $homeText('home_hero_description', 'Verified rooms, PGs and apartments. No brokerage. Connect directly with owners.') }}
                </p>

                <!-- Trust Badge Row -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm text-xs font-bold text-slate-700">
                        <div class="w-5 h-5 rounded-md bg-indigo-50 text-indigo-600 flex items-center justify-center text-[9px]"><i class="fas fa-ban"></i></div>
                        No brokerage
                    </div>
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm text-xs font-bold text-slate-700">
                        <div class="w-5 h-5 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center text-[9px]"><i class="fas fa-user-check"></i></div>
                        Owner Verified
                    </div>
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm text-xs font-bold text-slate-700">
                        <div class="w-5 h-5 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[9px]"><i class="fas fa-shield-halved"></i></div>
                        Safe &amp; Secure
                    </div>
                    <div class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-xl px-3 py-1.5 shadow-sm text-xs font-bold text-slate-700">
                        <div class="w-5 h-5 rounded-md bg-purple-50 text-purple-600 flex items-center justify-center text-[9px]"><i class="fas fa-headset"></i></div>
                        24x7 Support
                    </div>
                </div>

                <!-- Search Card — 5 fields + full-width button -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-lg p-5">
                    <form action="{{ route('rooms.index') }}" method="GET">
                        <!-- Row 1: Fields -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                            <!-- Location -->
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-between">
                                    <span>Location</span>
                                    <button type="button" onclick="detectLocation(true)" class="text-[9px] text-orange-500 hover:text-orange-700 font-bold flex items-center gap-0.5">
                                        <i class="fas fa-location-crosshairs text-[8px]"></i>
                                    </button>
                                </label>
                                <div class="relative">
                                    <i class="fas fa-map-pin absolute left-2.5 top-1/2 -translate-y-1/2 text-orange-400 text-[10px]"></i>
                                    <input type="text" name="city" id="hero-city-input-legacy"
                                           value="{{ request('city') ?? session('user_city') }}"
                                           placeholder="{{ $homeCity ?? 'City...' }}"
                                           class="w-full py-2.5 pl-7 pr-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 focus:bg-white outline-none transition-all">
                                </div>
                            </div>
                            <!-- Property Type -->
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Property Type</label>
                                <div class="relative">
                                    <i class="fas fa-building absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                    <select name="room_type" class="w-full py-2.5 pl-7 pr-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 focus:bg-white outline-none appearance-none transition-all">
                                        <option value="">Any Type</option>
                                        @foreach($roomCategories as $cat)
                                            <option value="{{ $cat->room_type_option_id }}" {{ request('room_type') == $cat->room_type_option_id ? 'selected' : '' }}>{{ $cat->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- Budget -->
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Budget</label>
                                <div class="flex items-center gap-1">
                                    <input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="₹ Min"
                                           class="w-full py-2.5 px-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-[10px] font-semibold focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 focus:bg-white outline-none transition-all">
                                    <span class="text-slate-300 font-bold">–</span>
                                    <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="Max"
                                           class="w-full py-2.5 px-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-lg text-[10px] font-semibold focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 focus:bg-white outline-none transition-all">
                                </div>
                            </div>
                            <!-- Preferred Tenant -->
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Preferred Tenant</label>
                                <div class="relative">
                                    <i class="fas fa-users absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                                    <select name="tenant_type" class="w-full py-2.5 pl-7 pr-2 bg-slate-50 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 focus:bg-white outline-none appearance-none transition-all">
                                        <option value="">Any</option>
                                        @foreach(\App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                            <option value="{{ $option->id }}" {{ (string) request('tenant_type') === (string) $option->id ? 'selected' : '' }}>{{ $option->label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Row 2: Full-width Search Button -->
                        <button type="submit" id="hero-search-btn"
                                class="w-full py-3 bg-orange-500 hover:bg-orange-600 active:scale-[0.99] text-white font-extrabold rounded-xl shadow-lg shadow-orange-400/30 transition-all flex items-center justify-center gap-2 text-sm pulse-orange">
                            <i class="fas fa-search"></i> {{ $homeText('home_search_button', 'Search Rooms') }}
                        </button>

                    </form>
                </div>
            </div>

            <!-- ===== RIGHT — Hero Room Card ===== -->
            <div class="w-full lg:w-[32%] hero-right">
                @if($heroRoom)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xl overflow-hidden">
                    <!-- Room Photo -->
                    <div class="relative h-52 overflow-hidden">
                        <img src="{{ $heroRoom->photo_url }}" alt="{{ $heroRoom->title }}"
                             class="w-full h-full object-cover" loading="eager">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                        @if($heroRoom->is_featured)
                        <div class="absolute top-3 left-3 bg-orange-500 text-white text-[9px] font-extrabold px-2 py-1 rounded-lg flex items-center gap-1">
                            <i class="fas fa-bolt text-[8px]"></i> Featured
                        </div>
                        @endif
                        <div class="absolute bottom-3 left-3">
                            <span class="block text-white font-black text-xl">₹{{ number_format($heroRoom->rent) }}<span class="text-slate-300 text-xs font-medium">/month</span></span>
                        </div>
                        <div class="absolute top-3 right-3 bg-emerald-500 text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full">
                            <i class="fas fa-circle-check text-[8px] mr-0.5"></i> Verified
                        </div>
                    </div>
                    <!-- Card info -->
                    <div class="p-4">
                        <p class="font-bold text-slate-900 text-sm line-clamp-1">{{ $heroRoom->title }}</p>
                        <p class="text-slate-500 text-xs mt-1 flex items-center gap-1">
                            <i class="fas fa-map-pin text-orange-500 text-[10px]"></i>
                            {{ $heroRoom->city }}{{ $heroRoom->address ? ', '.$heroRoom->address : '' }}
                        </p>
                        <div class="flex items-center justify-between mt-3">
                            <div class="flex text-amber-400 text-xs gap-0.5">
                                @for($i=0;$i<5;$i++)<i class="fas fa-star"></i>@endfor
                                <span class="text-slate-500 text-[10px] ml-1 font-bold">4.8</span>
                            </div>
                            <a href="{{ route('rooms.show', $heroRoom->slug) }}"
                               class="text-xs bg-orange-500 hover:bg-orange-600 text-white font-bold px-3 py-1.5 rounded-lg transition-all">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @else
                <!-- Fallback card if no rooms -->
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-2xl p-8 text-white text-center shadow-xl">
                    <i class="fas fa-home text-5xl text-indigo-300 mb-4 block"></i>
                    <p class="font-bold text-lg">Find Your Perfect Room</p>
                    <p class="text-indigo-300 text-sm mt-2">Browse verified listings near you</p>
                    <a href="{{ route('rooms.index') }}" class="mt-4 inline-block bg-white text-indigo-700 font-bold px-5 py-2 rounded-xl text-sm hover:bg-indigo-50 transition-all">Browse Rooms</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- ===== WHY CHOOSE APNANEST ===== -->
<section class="bg-[#f8fafc] py-16 reveal">
    <div class="container mx-auto px-6">
        <div class="text-center max-w-xl mx-auto mb-12">
            <h2 class="text-3xl font-black text-slate-900 font-heading">{{ $homeText('home_why_title', 'Why Choose ApnaNest?') }}</h2>
            <p class="text-slate-500 text-sm font-medium mt-2">{{ $homeText('home_why_description', 'A safe, secure and hassle-free renting experience.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="why-card bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center text-xl mb-4 shadow-md shadow-indigo-200">
                    <i class="fas {{ $homeText('home_why_1_icon', 'fa-circle-check') }}"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900">{{ $homeText('home_why_1_title', 'Verified Listings') }}</h3>
                <p class="text-slate-500 text-xs mt-2 leading-relaxed">{{ $homeText('home_why_1_description', 'Listings reviewed for authenticity.') }}</p>
            </div>
            <div class="why-card bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-400 to-orange-600 text-white flex items-center justify-center text-xl mb-4 shadow-md shadow-orange-200">
                    <i class="fas {{ $homeText('home_why_2_icon', 'fa-wallet') }}"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900">{{ $homeText('home_why_2_title', 'Zero Brokerage') }}</h3>
                <p class="text-slate-500 text-xs mt-2 leading-relaxed">{{ $homeText('home_why_2_description', 'Connect directly with property owners.') }}</p>
            </div>
            <div class="why-card bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center text-xl mb-4 shadow-md shadow-emerald-200">
                    <i class="fas {{ $homeText('home_why_3_icon', 'fa-shield-halved') }}"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900">{{ $homeText('home_why_3_title', 'Secure Payments') }}</h3>
                <p class="text-slate-500 text-xs mt-2 leading-relaxed">{{ $homeText('home_why_3_description', 'Protected payments through trusted gateways.') }}</p>
            </div>
            <div class="why-card bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-700 text-white flex items-center justify-center text-xl mb-4 shadow-md shadow-purple-200">
                    <i class="fas {{ $homeText('home_why_4_icon', 'fa-headset') }}"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900">{{ $homeText('home_why_4_title', 'Customer Support') }}</h3>
                <p class="text-slate-500 text-xs mt-2 leading-relaxed">{{ $homeText('home_why_4_description', 'Support throughout your rental journey.') }}</p>
            </div>
        </div>
    </div>
</section>


@if($popularAreas->count())
<section class="bg-white reveal border-b border-slate-100">
    <div class="container mx-auto px-6">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div><span class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-600">Explore locally</span><h2 class="mt-1 font-black text-slate-950">Popular areas in {{ $homeCity ?: 'your city' }}</h2><p class="mt-1 text-sm text-slate-500">Start with neighbourhoods renters search most often.</p></div>
            <a href="{{ route('rooms.index',['city'=>$homeCity]) }}" class="hidden sm:inline-flex text-xs font-bold text-indigo-600">View all areas <i class="fas fa-arrow-right ml-2"></i></a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
            @foreach($popularAreas->take(8) as $area)
                <a href="{{ route('rooms.index',['city'=>$homeCity,'area'=>$area->area_name]) }}" class="popular-area-card rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
                    <span class="mx-auto flex h-9 w-9 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm"><i class="fas fa-location-dot"></i></span>
                    <strong class="mt-2 block truncate text-xs text-slate-900">{{ $area->area_name }}</strong><small class="mt-0.5 block text-[10px] font-semibold text-slate-400">{{ $area->total }} rooms · from ₹{{ number_format($area->min_rent) }}</small>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif



<!-- Browse by Category -->
<section class="bg-white py-16 reveal border-b border-slate-100">
    <div class="container mx-auto px-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-[.16em] text-indigo-600 mb-2"><span class="w-6 h-px bg-indigo-500"></span>{{ $homeText('home_category_eyebrow', 'Property Types') }}</span>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900">{{ $homeText('home_category_title', 'Browse by Category') }}</h2>
                <p class="text-slate-500 text-sm font-medium mt-2">{{ $homeText('home_category_description', 'Explore rental options available near you.') }}</p>
            </div>
            <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 hover:text-indigo-700 group">
                View all properties <i class="fas fa-arrow-right text-[10px] transition-transform group-hover:translate-x-1"></i>
            </a>
        </div>

        @if($roomCategories->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($roomCategories->take(8) as $cat)
                    <a href="{{ route('rooms.index', ['room_type' => [$cat->room_type_option_id]]) }}" class="category-card group relative bg-white border border-slate-200 rounded-2xl p-5 flex items-center gap-4 hover:border-indigo-300 hover:shadow-[0_12px_30px_-18px_rgba(15,23,42,.35)] transition-all">
                        <div class="category-icon w-12 h-12 shrink-0 rounded-xl flex items-center justify-center text-lg transition-colors" style="background:rgba(var(--primary-rgb),.08);color:var(--primary)">
                            <i class="{{ $cat->icon }}"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="block text-slate-900 font-bold text-sm truncate group-hover:text-indigo-600 transition-colors">{{ $cat->label }}</span>
                            <span class="block text-[11px] text-slate-400 font-semibold mt-1">{{ number_format($cat->total) }} {{ \Illuminate\Support\Str::plural('listing', $cat->total) }}</span>
                        </div>
                        <span class="w-7 h-7 shrink-0 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors"><i class="fas fa-chevron-right text-[9px]"></i></span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-slate-50 border border-dashed border-slate-200 rounded-2xl text-slate-400 text-sm">
                <i class="fas fa-layer-group text-3xl mb-3 block text-slate-300"></i>
                <p class="font-semibold">No categories available yet.</p>
            </div>
        @endif
    </div>
</section>

<!-- Latest Verified Rooms Section -->
<div class="bg-white py-16">
    <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-8">
            <div>
                <h2 class="text-3xl font-black text-slate-900 font-heading">{{ $homeText('home_latest_title', 'Latest Verified Rooms') }}</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">{{ $homeText('home_latest_description', 'Handpicked verified listings just for you.') }}</p>
            </div>
            
            <a href="{{ route('rooms.index') }}" class="text-indigo-600 hover:text-indigo-700 font-bold text-sm flex items-center gap-1 mt-4 md:mt-0">
                View All Rooms <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        
        <?php if($rooms->count() > 0): ?>
            @include('rooms.partials.listing-mobile', ['homePage' => true])
            @include('rooms.partials.listing-desktop', ['homePage' => true])
        <?php else: ?>
            <!-- Empty state fallback -->
            <div class="text-center py-12 bg-slate-50 border border-slate-200/60 rounded-2xl">
                <i class="fas fa-house-chimney text-4xl text-slate-300 mb-2"></i>
                <h3 class="font-bold text-slate-700 text-sm">No Listings Available</h3>
                <p class="text-slate-500 text-xs mt-1">Check back later or change your filters.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Trust & Info Ribbon -->
<div class="bg-[#0b0f19] text-white py-8">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
            <div class="trust-tile flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-4 border border-transparent">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas {{ $homeText('home_trust_1_icon', 'fa-calendar-check') }}"></i>
                </div>
                <div>
                    <span class="block font-bold text-sm text-white leading-tight">{{ $homeText('home_trust_1_title', 'Available Today') }}</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $homeText('home_trust_1_description', 'Move in hassle free') }}</span>
                </div>
            </div>
            <div class="trust-tile flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-4 border border-transparent">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas {{ $homeText('home_trust_2_icon', 'fa-bolt') }}"></i>
                </div>
                <div>
                    <span class="block font-bold text-sm text-white leading-tight">{{ $homeText('home_trust_2_title', 'Quick Enquiries') }}</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $homeText('home_trust_2_description', 'Connect without delays') }}</span>
                </div>
            </div>
            <div class="trust-tile flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-4 border border-transparent">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas {{ $homeText('home_trust_3_icon', 'fa-file-signature') }}"></i>
                </div>
                <div>
                    <span class="block font-bold text-sm text-white leading-tight">{{ $homeText('home_trust_3_title', 'Easy Documentation') }}</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $homeText('home_trust_3_description', 'A simpler rental process') }}</span>
                </div>
            </div>
            <div class="trust-tile flex items-center gap-3 bg-white/5 rounded-2xl px-4 py-4 border border-transparent">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fas {{ $homeText('home_trust_4_icon', 'fa-shield-halved') }}"></i>
                </div>
                <div>
                    <span class="block font-bold text-sm text-white leading-tight">{{ $homeText('home_trust_4_title', 'Verified Listings') }}</span>
                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $homeText('home_trust_4_description', 'Trusted property information') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Multi-Section Grid: How it Works, Testimonials, App Download -->
<section class="bg-white py-16 border-b border-slate-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- 1. How It Works (Col span 4) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-xl font-black text-slate-900 font-heading">{{ $homeText('home_steps_title', 'How It Works?') }}</h3>
                    <p class="text-slate-500 text-xs font-semibold mt-1">{{ $homeText('home_steps_description', 'Three simple steps to find your next home.') }}</p>
                </div>
                
                <div class="space-y-6">
                    <div class="step-item flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $homeText('home_step_1_title', 'Search') }}</h4>
                            <p class="text-slate-500 text-xs mt-1 leading-relaxed">{{ $homeText('home_step_1_description', 'Find rooms by city, budget and preference.') }}</p>
                        </div>
                    </div>
                    
                    <div class="step-item flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-orange-50 text-orange-500 flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $homeText('home_step_2_title', 'Connect') }}</h4>
                            <p class="text-slate-500 text-xs mt-1 leading-relaxed">{{ $homeText('home_step_2_description', 'Review details and connect with the owner.') }}</p>
                        </div>
                    </div>
                    
                    <div class="step-item flex items-start gap-4">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $homeText('home_step_3_title', 'Move In') }}</h4>
                            <p class="text-slate-500 text-xs mt-1 leading-relaxed">{{ $homeText('home_step_3_description', 'Verify the property, complete documentation and move in.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 2. Testimonials (Col span 5) -->
            <div class="lg:col-span-5 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-xl font-black text-slate-900 font-heading">{{ $homeText('home_testimonials_title', 'What Our Users Say') }}</h3>
                    <p class="text-slate-500 text-xs font-semibold mt-1">{{ $homeText('home_testimonials_description', 'Experiences shared by tenants using our platform.') }}</p>
                </div>
                
                <div class="space-y-4">
                    <!-- Review 1 -->
                    <div class="testimonial-card bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col gap-2">
                        <div class="flex text-amber-400 text-xs">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-slate-600 text-xs italic">“{{ $homeText('home_testimonial_1_text', 'A simple and reliable room-finding experience.') }}”</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 font-bold flex items-center justify-center text-xs">R</div>
                            <div>
                                <span class="block text-slate-800 font-bold text-xs">{{ $homeText('home_testimonial_1_name', 'Rahul Sharma') }}</span>
                                <span class="block text-[9px] text-slate-500">{{ $homeText('home_testimonial_1_role', 'Student') }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Review 2 -->
                    <div class="testimonial-card bg-slate-50 border border-slate-100 rounded-2xl p-4 flex flex-col gap-2">
                        <div class="flex text-amber-400 text-xs">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-slate-600 text-xs italic">“{{ $homeText('home_testimonial_2_text', 'A simple and reliable room-finding experience.') }}”</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center text-xs">N</div>
                            <div>
                                <span class="block text-slate-800 font-bold text-xs">{{ $homeText('home_testimonial_2_name', 'Neha Verma') }}</span>
                                <span class="block text-[9px] text-slate-500">{{ $homeText('home_testimonial_2_role', 'Working Professional') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 3. App Download (Col span 3) -->
            <div class="lg:col-span-3 space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-xl font-black text-slate-900 font-heading">{{ $homeText('home_app_title', 'Download Our App') }}</h3>
                    <p class="text-slate-500 text-xs font-semibold mt-1">{{ $homeText('home_app_description', 'Find stays on the go with our mobile app.') }}</p>
                </div>
                
                <div class="app-download-card bg-slate-50 border border-slate-200 rounded-2xl p-5 flex flex-col items-center justify-center gap-4 text-center">
                    <!-- Simulated QR Code SVG -->
                    <div class="w-28 h-28 bg-white border border-slate-200 rounded-xl p-2 shadow-sm flex items-center justify-center">
                        <i class="fas fa-qrcode text-6xl text-slate-800"></i>
                    </div>
                    <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest leading-none">Scan to Download</span>
                    
                    @php
                        $iosAppUrl = trim($homeText('home_ios_url', ''));
                        $androidAppUrl = trim($homeText('home_android_url', ''));
                    @endphp
                    @if($iosAppUrl || $androidAppUrl)
                    <div class="flex flex-col gap-2 w-full">
                        @if($iosAppUrl)
                        <a href="{{ $iosAppUrl }}" target="_blank" rel="noopener" class="bg-black text-white hover:bg-slate-900 px-4 py-2 rounded-xl flex items-center justify-center gap-2.5 shadow-sm transition-colors text-xs font-bold w-full">
                            <i class="fab fa-apple text-base"></i>
                            <div class="text-left leading-tight">
                                <span class="block text-[8px] font-medium text-slate-400">Download on</span>
                                <span class="block text-xs font-black">App Store</span>
                            </div>
                        </a>
                        @endif
                        @if($androidAppUrl)
                        <a href="{{ $androidAppUrl }}" target="_blank" rel="noopener" class="bg-black text-white hover:bg-slate-900 px-4 py-2 rounded-xl flex items-center justify-center gap-2.5 shadow-sm transition-colors text-xs font-bold w-full">
                            <i class="fab fa-google-play text-base text-emerald-400"></i>
                            <div class="text-left leading-tight">
                                <span class="block text-[8px] font-medium text-slate-400">Get it on</span>
                                <span class="block text-xs font-black">Google Play</span>
                            </div>
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Own a Property CTA Banner -->
<section class="bg-white py-12">
    <div class="container mx-auto px-6">
        <div class="owner-cta bg-[#0b0f19] rounded-[32px] overflow-hidden relative border border-slate-900 shadow-xl flex flex-col lg:flex-row items-center justify-between p-8 lg:p-12 gap-8">
            <!-- Background lights inside block -->
            <div class="absolute -top-32 -left-32 w-80 h-80 bg-indigo-600/10 rounded-full blur-[100px] pointer-events-none"></div>
            
            <div class="space-y-4 max-w-2xl relative z-10">
                <h3 class="text-2xl lg:text-3xl font-black text-white font-heading">{{ $homeText('home_owner_title', 'Own a Property?') }}</h3>
                <p class="text-slate-400 text-sm font-medium">{{ $homeText('home_owner_description', 'List your property and connect with genuine tenants.') }}</p>
                <div class="grid grid-cols-2 gap-3 text-xs font-bold text-slate-300">
                    <span class="flex items-center gap-2"><i class="fas fa-check text-emerald-500"></i> Flexible Listing Plans</span>
                    <span class="flex items-center gap-2"><i class="fas fa-check text-emerald-500"></i> Zero Brokerage</span>
                    <span class="flex items-center gap-2"><i class="fas fa-check text-emerald-500"></i> Verified Tenants</span>
                    <span class="flex items-center gap-2"><i class="fas fa-check text-emerald-500"></i> Direct Tenant Contact</span>
                </div>
            </div>
            
            <div class="relative z-10 flex-shrink-0">
                <a href="{{ route('register') }}?role=owner" class="px-6 py-3.5 bg-orange-500 hover:bg-orange-600 text-white font-extrabold rounded-xl shadow-lg shadow-orange-500/25 transition-all text-sm flex items-center gap-2 hover:-translate-y-0.5">
                    {{ $homeText('home_owner_button', 'List Your Property') }} <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Blog and FAQs Section -->
<section class="bg-[#f8fafc] py-16">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Left Side: Latest from Blog (Col span 7) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="flex items-end justify-between border-b border-slate-200/80 pb-4">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 font-heading">{{ $homeText('home_blog_title', 'Latest from Blog') }}</h3>
                        <p class="text-slate-500 text-xs font-semibold mt-1">{{ $homeText('home_blog_description', 'Tips, guides and rental insights.') }}</p>
                    </div>
                    <a href="{{ route('blogs.index') }}" class="text-indigo-600 hover:text-indigo-700 font-bold text-xs flex items-center gap-0.5">
                        View All Blogs <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @if(isset($latestBlogs) && $latestBlogs->count() > 0)
                        @foreach($latestBlogs as $blog)
                            <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col h-full">
                                <div class="h-28 bg-slate-100 relative">
                                    <img src="{{ $blog->featured_image ?? 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=300&h=200&fit=crop&q=80' }}" 
                                         alt="{{ $blog->title }}" class="w-full h-full object-cover">
                                </div>
                                <div class="p-3.5 flex flex-col flex-grow">
                                    <span class="text-[9px] bg-indigo-50 text-indigo-600 font-extrabold uppercase px-2 py-0.5 rounded-full inline-block self-start mb-2">Guide</span>
                                    <h4 class="font-bold text-slate-900 text-xs line-clamp-2 leading-tight mb-2 hover:text-indigo-600"><a href="{{ route('blogs.show', $blog->slug) }}">{{ $blog->title }}</a></h4>
                                    <div class="flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-auto">
                                        <span>{{ $blog->created_at->format('M d, Y') }}</span>
                                        <span>5 min read</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Mock data Fallback in case of no database posts -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm flex flex-col">
                            <div class="h-28 bg-slate-100 relative">
                                <img src="https://images.unsplash.com/photo-1513694203232-719a280e022f?w=300&h=200&fit=crop&q=80" alt="Blog stay guide" class="w-full h-full object-cover">
                            </div>
                            <div class="p-3.5 flex flex-col flex-grow">
                                <span class="text-[9px] bg-indigo-50 text-indigo-600 font-extrabold uppercase px-2 py-0.5 rounded-full inline-block self-start mb-2">Rental Guide</span>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-2 leading-tight mb-2">How to Rent a Room Safely in India</h4>
                                <div class="flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-auto">
                                    <span>May 20, 2026</span>
                                    <span>5 min read</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm flex flex-col">
                            <div class="h-28 bg-slate-100 relative">
                                <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=300&h=200&fit=crop&q=80" alt="PG locations" class="w-full h-full object-cover">
                            </div>
                            <div class="p-3.5 flex flex-col flex-grow">
                                <span class="text-[9px] bg-indigo-50 text-indigo-600 font-extrabold uppercase px-2 py-0.5 rounded-full inline-block self-start mb-2">PG Guide</span>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-2 leading-tight mb-2">Top PG Areas in Bhopal for Students</h4>
                                <div class="flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-auto">
                                    <span>May 18, 2026</span>
                                    <span>6 min read</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-sm flex flex-col">
                            <div class="h-28 bg-slate-100 relative">
                                <img src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=300&h=200&fit=crop&q=80" alt="Move tips" class="w-full h-full object-cover">
                            </div>
                            <div class="p-3.5 flex flex-col flex-grow">
                                <span class="text-[9px] bg-indigo-50 text-indigo-600 font-extrabold uppercase px-2 py-0.5 rounded-full inline-block self-start mb-2">Tips</span>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-2 leading-tight mb-2">Checklist For Moving Into a New Room</h4>
                                <div class="flex items-center justify-between text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-auto">
                                    <span>May 15, 2026</span>
                                    <span>4 min read</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Right Side: Frequently Asked Questions (Col span 5) -->
            @php
                $homeFaqCmsPage = \App\Models\CmsPage::where('slug', 'faq')->first();
                $showHomeFaq = !$homeFaqCmsPage || $homeFaqCmsPage->isPublished();
            @endphp
            @if($showHomeFaq)
            <div class="lg:col-span-5 space-y-6">
                <div class="border-b border-slate-200/80 pb-4">
                    <h3 class="text-2xl font-black text-slate-900 font-heading">{{ $homeText('home_faq_title', 'Frequently Asked Questions') }}</h3>
                    <p class="text-slate-500 text-xs font-semibold mt-1">{{ $homeText('home_faq_description', 'Quick answers to common questions.') }}</p>
                </div>
                
                <div class="space-y-3">
                    <?php
                        $faqPage = \App\Models\CmsPage::where('slug', 'faq')->published()->first();
                        $faqs = json_decode($faqPage?->content ?: \App\Models\Setting::get('faq_content', '[]'), true);
                        if (!is_array($faqs) || count($faqs) === 0) $faqs = [
                            ['q' => 'How do I contact a room owner?', 'a' => 'Open a room, review its details and unlock the owner contact using a contact credit or single unlock. You can then call or message the owner directly.'],
                            ['q' => 'Is there any brokerage charge?', 'a' => 'No! ApnaNest connects owners and tenants directly. There are no brokerage charges or hidden fees involved.'],
                            ['q' => 'How can I contact the owner?', 'a' => 'You can view the owner\'s verified contact details after unlocking the contact section on the stay details page.'],
                            ['q' => 'Is my payment information secure?', 'a' => 'Yes, absolutely. All payments on ApnaNest are processed via Razorpay secure gateways. We do not store any card or credential details.'],
                            ['q' => 'Can I visit the property before finalizing?', 'a' => 'Yes. Unlock the owner contact and schedule a physical visit. ApnaNest provides the listing and contact service; you and the owner finalize independently.']
                        ];
                    ?>
                    @foreach($faqs as $i => $faq)
                        <div class="bg-white border border-slate-200/85 rounded-xl overflow-hidden shadow-sm faq-item">
                            <button onclick="toggleFaqAccordion({{ $i }})" class="w-full text-left p-4 font-bold text-slate-800 text-xs md:text-sm flex justify-between items-center hover:bg-slate-50/50 transition-colors">
                                <span>{{ $faq['question'] ?? $faq['q'] ?? '' }}</span>
                                <span class="faq-icon-{{ $i }} transition-transform"><i class="fas fa-plus text-slate-400"></i></span>
                            </button>
                            <div class="faq-content-{{ $i }} hidden px-4 pb-4 text-xs text-slate-500 leading-relaxed border-t border-slate-100 pt-3">
                                {!! $faq['answer'] ?? $faq['a'] ?? '' !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Stay Updated Newsletter Section -->
<section class="bg-[#f8fafc] pb-16 reveal">
    <div class="container mx-auto px-6">
        <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl border border-indigo-100">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-base md:text-lg">{{ $homeText('home_newsletter_title', 'Stay Updated') }}</h3>
                    <p class="text-slate-500 text-xs md:text-sm font-medium">{{ $homeText('home_newsletter_description', 'Get updates on new rooms and offers.') }}</p>
                </div>
            </div>
            <div class="w-full md:w-auto flex-1 max-w-md">
                <form action="#" method="POST" class="flex gap-2" onsubmit="event.preventDefault(); toastr.success('Thank you for subscribing!', 'Success');">
                    <input type="email" required placeholder="Enter your email address"
                           class="flex-1 py-3 px-4 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-extrabold rounded-xl shadow-md transition-all text-xs whitespace-nowrap">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Accordion Toggle JavaScript -->
<script>
    function toggleFaqAccordion(index) {
        const content = document.querySelector(`.faq-content-${index}`);
        const icon = document.querySelector(`.faq-icon-${index}`);
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(45deg)';
        } else {
            content.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
</script>

@push('scripts')
@auth
    @if(Auth::user()->role === 'owner')
        @push('sweetalert')
            <script src="{{ asset('assets/js/sweetalert2.min.js') }}" defer></script>
        @endpush
    @endif
    <script defer>
    async function toggleWishlist(event, roomId) {
        event.preventDefault();
        event.stopPropagation();
        
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        try {
            const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Failed to toggle wishlist');
            const data = await response.json();

            if (data.success) {
                const updateBtn = (id) => {
                    const btn = document.getElementById(id);
                    if (btn) {
                        const icon = btn.querySelector('i');
                        if (data.status === 'added') {
                            icon.classList.remove('far');
                            icon.classList.add('fas', 'text-red-500');
                        } else {
                            icon.classList.remove('fas', 'text-red-500');
                            icon.classList.add('far');
                        }
                    }
                };
                updateBtn(`wishlist-btn-${roomId}`);
                updateBtn(`wishlist-btn-mobile-${roomId}`);
            }
        } catch (error) {
            console.error(error);
        }
    }
    const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
    
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-room-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const result = await Swal.fire({
                    title: 'Delete Room?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                });

                if (!result.isConfirmed) return;
                
                const formData = new FormData(this);
                const roomId = this.dataset.roomId;
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Deleting...';
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(async response => {
                    if (response.status === 419) {
                        throw new Error('CSRF token mismatch. Please refresh the page and try again.');
                    }
                    
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to delete room');
                        }
                        return data;
                    } else {
                        const text = await response.text();
                        throw new Error(text || 'Invalid response from server');
                    }
                })
                .then(data => {
                    if (data.success) {
                        toastr.success('Room deleted successfully', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr.error(data.message || 'Failed to delete room', 'Error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error(error.message || 'Failed to delete room', 'Error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });
    });

    async function markBooked(roomId) {
        const result = await Swal.fire({
            title: 'Mark as Rented?',
            text: "This room will be hidden from users.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: ROOM_PRIMARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark it!'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch(`{{ route('rooms.markBooked', ':id') }}`.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire(
                    'Rented!',
                    'Room has been marked as rented.',
                    'success'
                ).then(() => {
                    location.reload();
                });
            } else {
                toastr.error(data.message || 'Failed to mark room as rented', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function markAvailable(roomId) {
        const result = await Swal.fire({
            title: 'Make Available?',
            text: "Making this room available will charge listing fee.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: ROOM_SECONDARY_COLOR,
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Continue'
        });

        if (!result.isConfirmed) return;
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const response = await fetch(`{{ route('rooms.markAvailable', ':id') }}`.replace(':id', roomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (data.subscription_used) {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room made available using subscription!',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else if (data.payment_id) {
                    await initiatePayment(data.payment_id, data.amount, 'listing', roomId);
                } else {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Room marked as available.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } else {
                toastr.error(data.message || 'Failed to make room available', 'Error');
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong', 'Error');
        }
    }

    async function initiatePayment(paymentId, amount, type, referenceId) {
        try {
            // Lazy load Razorpay SDK
            const Razorpay = await loadRazorpaySDK();

            if (!razorpayKey || razorpayKey === '' || razorpayKey === 'null') {
                toastr.error('Razorpay key not configured. Please add it in Business Settings.', 'Error');
                return;
            }
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            
            const orderResponse = await fetch('{{ route("razorpay.createOrder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ payment_id: paymentId }),
                credentials: 'same-origin'
            });
            
            if (!orderResponse.ok) {
                const errorData = await orderResponse.json().catch(() => ({ message: 'Failed to create order' }));
                throw new Error(errorData.message || 'Failed to create order');
            }
            
            const orderData = await orderResponse.json();
            
            if (!orderData.success || !orderData.order_id) {
                throw new Error(orderData.message || 'Failed to create order');
            }
            
            const options = {
                key: razorpayKey,
                amount: orderData.amount * 100,
                currency: 'INR',
                name: '{{ \App\Models\Setting::get("website_name", "RoomRental") }}',
                description: 'Make Room Available - Listing Fee',
                order_id: orderData.order_id,
                handler: async function(response) {
                    console.log('Razorpay Response:', response);
                    
                    if (!response.razorpay_order_id || !response.razorpay_signature) {
                        alert('Payment failed: Missing order ID or signature. Please try again.');
                        console.error('Missing properties in response', response);
                        return;
                    }

                    try {
                        const verifyResponse = await fetch('{{ route("razorpay.verify") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id || orderData.order_id,
                                razorpay_signature: response.razorpay_signature,
                                payment_id: paymentId,
                                type: type,
                                reference_id: referenceId
                            }),
                            credentials: 'same-origin'
                        });
                        
                        if (!verifyResponse.ok) {
                            const errorData = await verifyResponse.json().catch(() => ({ message: 'Payment verification failed' }));
                            throw new Error(errorData.message || 'Payment verification failed');
                        }
                        
                        const verifyData = await verifyResponse.json();
                        
                        if (verifyData.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Payment successful! Room is now available.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            toastr.error(verifyData.message || 'Payment verification failed', 'Error');
                        }
                    } catch (error) {
                        console.error('Verification error:', error);
                        toastr.error(error.message || 'Payment verification failed', 'Error');
                    }
                },
                prefill: {
                    name: '{{ Auth::user()->name ?? "" }}',
                    email: '{{ Auth::user()->email ?? "" }}'
                },
                theme: {
                    color: ROOM_PRIMARY_COLOR
                },
                method: {
                    upi: true,
                    card: true,
                    netbanking: true,
                    wallet: true
                }
            };
            
            const razorpay = new Razorpay(options);
            razorpay.on('payment.failed', function(response) {
                toastr.error('Payment failed: ' + (response.error.description || 'Unknown error'), 'Payment Failed');
            });
            razorpay.open();
            
        } catch (error) {
            console.error('Payment error:', error);
            toastr.error('Payment initialization failed: ' + error.message, 'Error');
        }
    }
    async function subscribeToAlerts(city) {
        @guest
            window.location.href = '{{ route("login") }}';
            return;
        @endguest

        const btn = document.getElementById('notify-btn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Subscribing...';

        try {
            const response = await fetch('{{ route("city-alerts.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ city })
            });

            const data = await response.json();
            if (data.success) {
                toastr.success(data.message, 'Success');
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Subscribed';
                btn.classList.replace('bg-indigo-50', 'bg-green-50');
                btn.classList.replace('text-indigo-700', 'text-green-700');
            } else {
                toastr.error(data.message || 'Failed to subscribe', 'Error');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        } catch (error) {
            console.error('Error:', error);
            toastr.error('Something went wrong. Please try again.', 'Error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }
    </script>
@endauth

    <!-- Auto-City Detection -->
    <script>
    const ROOM_PRIMARY_COLOR = '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}';
    const ROOM_SECONDARY_COLOR = '{{ \App\Models\Setting::get("secondary_color", "#10B981") }}';
        async function detectLocation(force = false) {
            if (!navigator.geolocation) return;

            // Show loading state in input
            const cityInput = document.getElementById('hero-city-input');
            const originalPlaceholder = cityInput ? cityInput.placeholder : '';
            if (cityInput) cityInput.placeholder = 'Detecting location...';

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`);
                    const data = await response.json();
                    const city = data.address.city || data.address.town || data.address.village || data.address.suburb || data.address.state_district;
                    
                    if (city) {
                        if (cityInput) {
                            cityInput.value = city;
                            cityInput.placeholder = originalPlaceholder;
                        }
                        // Save to session and redirect
                        await fetch(`{{ route('set-city') }}?city=${encodeURIComponent(city)}&lat=${lat}&lng=${lng}&verified=true`);
                        window.location.href = window.location.pathname + `?lat=${lat}&lng=${lng}&city=${encodeURIComponent(city)}`;
                    } else if (cityInput) {
                        cityInput.placeholder = originalPlaceholder;
                    }
                } catch (error) {
                    console.error('Location error:', error);
                    if (cityInput) cityInput.placeholder = originalPlaceholder;
                }
            }, (error) => {
                console.warn('Geolocation failed:', error);
                if (cityInput) cityInput.placeholder = 'Location denied. Type city manually.';
            });
        }

        // Run automatically catch-all if no city and NOT search
        @if(!request('city') && !session('user_city') && !session('no_auto'))
            document.addEventListener('DOMContentLoaded', () => {
                // Don't annoy user with prompt instantly, maybe wait 2 seconds
                setTimeout(() => detectLocation(), 2000);
            });
        @endif
    </script>

<script defer>
    document.addEventListener('DOMContentLoaded', () => {
        // Wait for user coordinates before updating
        detectUserLocation((coords) => {
            const tags = document.querySelectorAll('.distance-tag');
            tags.forEach(tag => {
                const roomLat = parseFloat(tag.dataset.lat);
                const roomLng = parseFloat(tag.dataset.lng);
                
                if (roomLat && roomLng) {
                    const dist = calculateDistance(coords.lat, coords.lng, roomLat, roomLng);
                    if (dist) {
                        const kmSpan = tag.querySelector('.distance-km');
                        if (kmSpan) kmSpan.textContent = dist;
                        tag.classList.remove('hidden');
                    }
                }
            });
        });
    });
</script>

<!-- Infinite Scroll Script -->
<script defer>
    // Infinite Scroll for Mobile
    document.addEventListener('DOMContentLoaded', function() {
        const mobileRoomList = document.getElementById('mobile-room-list');
        const loader = document.getElementById('infinite-loader');
        let isLoading = false;
        let hasMore = @json($rooms->hasMorePages());
        let nextPage = @json($rooms->currentPage() + 1);

        if (!mobileRoomList || !loader) return;

        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !isLoading && hasMore) {
                loadMoreRooms();
            }
        }, { threshold: 0.1 });

        observer.observe(loader);

        async function loadMoreRooms() {
            isLoading = true;
            loader.classList.remove('hidden');

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('page', nextPage);

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Failed to load rooms');

                const data = await response.json();
                
                if (data.html) {
                    mobileRoomList.insertAdjacentHTML('beforeend', data.html);
                    nextPage++;
                    hasMore = data.hasMore;
                    
                    if (!hasMore) {
                        loader.remove();
                        observer.disconnect();
                    }
                }
            } catch (error) {
                console.error('Error loading more rooms:', error);
            } finally {
                isLoading = false;
                if (hasMore) {
                    loader.classList.add('hidden');
                }
            }
        }
    });
</script>
<script>
    // ===== SCROLL-REVEAL =====
    (function () {
        const revealEls = document.querySelectorAll('.reveal');
        if (!revealEls.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('in-view');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => observer.observe(el));
    })();
</script>
@endpush
@endsection
