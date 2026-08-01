@extends('layouts.app')

@section('title', (request('city') ? 'Verified Rooms & PG in ' . request('city') : 'Browse Rooms & PG for Rent') . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', (request('city') ? 'Find the best verified rooms, apartments, and PG in ' . request('city') . '. Browse listings with photos, rents, and owner contacts.' : 'Browse verified room listings in your city. Find apartments, houses, and rooms for rent with verified owners.'))
@section('keywords', (request('city') ? 'pg in ' . request('city') . ', room for rent in ' . request('city') . ', ' : '') . 'browse rooms, room listings, ' . \App\Models\Setting::get('seo_meta_keywords', 'apartment, house, property'))

@php
    $cityContext = $cityContext ?? ['isFallback' => false, 'activeCityName' => request('city') ?? session('user_city'), 'launchingSoonCityName' => null];
    $displayCity = $cityContext['launchingSoonCityName'] ?? $cityContext['activeCityName'] ?? request('city') ?? session('user_city');
    $hasMetaSearchIntent = request()->hasAny(['city', 'min_rent', 'max_rent', 'room_type', 'tenant_type', 'furnishing_type', 'available_now', 'availability_from']);
@endphp

@push('styles')
@include('partials.listings-ld')
<style>
    @media (max-width: 1023px) {
        .navbar, footer { display: none !important; }
        body { padding-bottom: 70px; background-color: #f8fafc; }
    }
    .custom-shadow {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
    }
    .filter-sticky {
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
    }
    /* Scrollbar styling for sidebar */
    .filter-sticky {
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--primary-rgb), .35) transparent;
    }
    .filter-sticky::-webkit-scrollbar { width: 4px; }
    .filter-sticky::-webkit-scrollbar-track { background: transparent; }
    .filter-sticky::-webkit-scrollbar-thumb { background: rgba(var(--primary-rgb), .35); border-radius: 999px; }
    .filter-sticky::-webkit-scrollbar-thumb:hover { background: rgba(var(--primary-rgb), .58); }

    .rooms-search-shell {
        background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
        padding: 1rem 0;
    }
    .rooms-search-panel {
        border-radius: 1.125rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .055);
    }
    .rooms-main {
        background: #f8fafc;
        min-height: 65vh;
    }
    .rooms-filter-panel {
        border-color: #e2e8f0;
        border-radius: 1.125rem !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .045);
    }
    .rooms-results-head {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: .9rem 1rem;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .035);
    }
    .room-listing-card {
        border-color: #e2e8f0;
        border-radius: 1.125rem !important;
        box-shadow: 0 5px 18px rgba(15, 23, 42, .05);
    }
    .room-listing-card:hover {
        border-color: color-mix(in srgb, var(--primary) 35%, #e2e8f0);
        box-shadow: 0 18px 38px rgba(15, 23, 42, .11);
    }
    .room-listing-card .room-image {
        height: 13.25rem;
    }
    .room-listing-card .room-card-body {
        padding: 1.125rem;
    }
    .rooms-search-shell > .container,
    .rooms-main > .container {
        max-width: 1320px !important;
    }
    .rooms-search-panel input,
    .rooms-search-panel select {
        min-height: 42px;
        font-size: .8125rem !important;
    }
    .rooms-search-panel button[type="submit"] {
        min-height: 42px;
        font-size: .8125rem !important;
    }
    .rooms-filter-panel form > div {
        padding-bottom: 1.15rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .rooms-filter-panel form > div:last-of-type {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .rooms-filter-panel label {
        line-height: 1.35;
    }
    .rooms-results-head h2 {
        font-size: 1.5rem !important;
        letter-spacing: -.025em;
    }
    .room-listing-card h3 {
        font-size: .9375rem !important;
        line-height: 1.4;
        min-height: 2.625rem;
    }
    .room-listing-card .room-card-body > div.flex.items-center {
        font-size: .8125rem !important;
    }
    .room-listing-card .room-card-body > a,
    .room-listing-card .room-card-body button {
        min-height: 40px;
        font-size: .8125rem !important;
    }
    .room-listing-card .room-card-body .flex.flex-wrap span {
        font-size: .625rem !important;
        padding: .35rem .5rem !important;
    }
    .city-fallback-banner { border: 1px solid #fed7aa; background: #fff7ed; color: #9a3412; border-radius: 1rem; padding: .95rem 1rem; }
    @media (max-width: 1279px) {
        .room-listing-card .room-image { height: 13rem; }
    }
    @media (min-width: 1024px) and (max-width: 1279px) {
        .rooms-main > .container > div:last-child { gap: 1.25rem !important; }
    }
    @media (max-width: 767px) {
        .rooms-main { padding-top: .75rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .room-listing-card, .room-listing-card img { transition: none !important; }
        .room-listing-card:hover { transform: none !important; }
    }
    /* Reference-led rooms directory */
    .rooms-search-shell{padding:1.5rem 0;background:#f8fafc}
    .rooms-search-panel{padding:1.15rem 1.25rem!important;border-radius:1rem;box-shadow:0 12px 34px rgba(15,23,42,.07)}
    .rooms-search-panel form{gap:1rem!important;flex-wrap:nowrap!important}
    .rooms-search-panel form>div{min-width:0!important;border-right:0!important;padding-right:0!important}
    .rooms-search-panel form>div:last-child{width:160px!important;min-width:160px!important}
    .rooms-search-panel label{color:#64748b!important;font-size:.65rem!important;letter-spacing:.04em!important;text-transform:none!important}
    .rooms-search-panel input,.rooms-search-panel select{height:46px;border-radius:.65rem!important;background:#fff!important}
    .rooms-search-panel button[type="submit"]{height:46px;border-radius:.65rem!important}
    .rooms-main{background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
    .rooms-main>.container{padding-top:1.4rem!important;padding-bottom:3rem!important}
    .rooms-filter-panel{padding:1.25rem!important;border-radius:1rem!important;box-shadow:0 8px 25px rgba(15,23,42,.05)}
    .rooms-filter-panel h3{font-size:1.1rem!important}
    .rooms-filter-panel form{font-size:.78rem}
    .rooms-filter-panel input[type="text"],.rooms-filter-panel input[type="number"],.rooms-filter-panel input[type="date"],.rooms-filter-panel select{min-height:40px;border-radius:.6rem!important}
    .rooms-results-head{padding:.25rem 0 1rem;border:0;background:transparent;box-shadow:none}
    .rooms-results-head h2{font-size:1.65rem!important}
    .rooms-results-head h2+span{display:block;margin-top:.25rem;color:var(--primary)!important}
    .room-listing-card{border-radius:.9rem!important;box-shadow:0 8px 24px rgba(15,23,42,.07)}
    .room-listing-card:hover{transform:translateY(-5px)!important;box-shadow:0 20px 42px rgba(15,23,42,.13)}
    .room-listing-card .room-image{height:14.5rem}
    .room-listing-card .room-card-body{padding:1rem}
    .room-listing-card h3{min-height:2.7rem;font-size:1rem!important;color:var(--primary)!important}
    .room-price-tag{display:inline-flex;align-items:baseline;gap:.2rem;border:1px solid rgba(var(--primary-rgb),.2)!important;background:#fff!important;color:var(--primary)!important;box-shadow:0 8px 18px rgba(15,23,42,.13)!important}
    .room-price-tag span:last-child{color:#64748b!important}
    .room-owner-row{display:flex;align-items:center;gap:.55rem;margin:.15rem 0 .85rem;padding-top:.75rem;border-top:1px solid #eef2f7}
    .room-owner-row img{width:1.65rem;height:1.65rem;border-radius:999px;object-fit:cover;background:#eef2ff}
    .room-owner-row span{font-size:.7rem;color:#64748b}
    .room-owner-row strong{color:#334155}
    .rooms-trust-strip{margin-top:0!important;padding:2.4rem 0!important;border-top:1px solid #e8edf5!important;background:#f8fafc!important;color:#0f172a!important}
    .rooms-trust-strip>.container{max-width:1320px}
    .rooms-trust-strip .rooms-trust-grid{gap:1rem!important}
    .rooms-trust-strip .rooms-trust-item{padding:1rem;border:1px solid #e2e8f0;border-radius:.85rem;background:#fff;box-shadow:0 5px 18px rgba(15,23,42,.035)}
    .rooms-trust-strip .rooms-trust-item span:first-child{color:#0f172a!important;font-size:.78rem!important}
    .rooms-trust-strip .rooms-trust-item span:last-child{color:#64748b!important;font-size:.68rem!important}
    @media(max-width:1199px){
        .rooms-search-panel form{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))}
        .rooms-search-panel form>div:last-child{width:auto!important;grid-column:1/-1}
        .room-listing-card .room-image{height:13.25rem}
    }
    @media(max-width:1023px){
        .rooms-main>.container{padding-inline:1rem!important}
    }
    @media(max-width:767px){
        .rooms-results-head{align-items:flex-start!important}
        .rooms-results-head h2{font-size:1.35rem!important}
        .rooms-trust-strip{display:none}
    }
</style>
@endpush

@section('content')
<!-- ===== TOP SEARCH HEADER BAR ===== -->
<div class="rooms-search-shell border-b border-slate-200/80 hidden md:block">
    <div class="container mx-auto px-6">
        <div class="rooms-search-panel bg-white border border-slate-200 p-4">
            <form action="{{ route('rooms.index') }}" method="GET" class="flex flex-wrap gap-4 items-center justify-between">
                <!-- Location -->
                <div class="flex-1 min-w-[200px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center justify-between mb-1">
                        <span>Location</span>
                        <button type="button" onclick="detectLocation(true)" class="text-[9px] text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 font-bold">
                            <i class="fas fa-location-crosshairs"></i> Near Me
                        </button>
                    </label>
                    <div class="relative">
                        <i class="fas fa-map-pin absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="city" id="hero-city-input"
                               value="{{ $displayCity }}"
                               placeholder="City or area..."
                               class="w-full py-2 pl-8 pr-7 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition-all">
                        @if(request('city') || session('user_city'))
                            <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500">
                                <i class="fas fa-times-circle text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Property Type -->
                <div class="flex-1 min-w-[180px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Property Type</label>
                    <div class="relative">
                        <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select name="room_type[]" class="w-full py-2 pl-8 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none appearance-none transition-all">
                            <option value="">Any Type</option>
                            @foreach($roomTypeOptions as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, (array)request('room_type')) ? 'selected' : '' }}>{{ $option->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Budget -->
                <div class="flex-1 min-w-[150px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Max Budget (₹/mo)</label>
                    <div class="relative">
                        <i class="fas fa-rupee-sign absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="Max"
                               class="w-full py-2 pl-7 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none transition-all">
                    </div>
                </div>

                <!-- Gender -->
                <div class="flex-1 min-w-[150px] border-r border-slate-100 pr-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-1">Preferred For</label>
                    <div class="relative">
                        <i class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select name="tenant_type[]" class="w-full py-2 pl-8 pr-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:bg-white outline-none appearance-none transition-all">
                            <option value="">Any Gender</option>
                            @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                <option value="{{ $option->id }}" {{ in_array($option->id, (array)request('tenant_type')) ? 'selected' : '' }}>{{ $option->label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div class="w-[120px] pl-1">
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 text-xs">
                        <i class="fas fa-search text-[10px]"></i> Search Rooms
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- Mobile layout search box -->
<div class="md:hidden">
    @include('partials.mobile-search')
</div>

<!-- ===== MAIN CONTAINER ===== -->
<div class="rooms-main">
<div class="container mx-auto px-4 sm:px-6 py-6">
    @if($cityContext['isFallback'])
        <div class="city-fallback-banner mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <strong class="block text-sm">Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong>
                <span class="text-xs">We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span>
            </div>
            <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}" class="text-xs font-black text-indigo-700">View {{ $cityContext['activeCityName'] }}</a>
        </div>
    @endif

    <!-- Breadcrumb -->
    <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-4 font-semibold">
        <a href="{{ url('/') }}" class="hover:text-indigo-600 transition-colors">Home</a>
        <i class="fas fa-chevron-right text-[8px]"></i>
        <span class="text-slate-600">Rooms in {{ $displayCity ?? 'India' }}</span>
    </div>

    <!-- Outer container (Flexbox for robust layout) -->
    <div class="flex flex-col lg:flex-row gap-6 xl:gap-7 items-start">

        <!-- ===== LEFT SIDEBAR (FILTERS) ===== -->
        <div class="w-full lg:w-[280px] xl:w-[300px] flex-shrink-0 hidden lg:block">
            <div class="rooms-filter-panel filter-sticky bg-white border rounded-2xl p-5 space-y-4">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-black text-slate-800 text-base">Filters</h3>
                    <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="text-xs font-bold text-red-500 hover:text-red-700 transition-colors flex items-center gap-1">
                        <i class="fas fa-rotate-left text-[10px]"></i> Reset
                    </a>
                </div>

                <form action="{{ route('rooms.index') }}" method="GET" class="space-y-4">
                    <!-- Locality Input -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Location</label>
                        <input type="text" name="city" value="{{ request('city') }}" placeholder="Enter locality or area..."
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                        
                        {{-- City dropdown — fully dynamic from DB popular cities --}}
                        <select name="city_dropdown" onchange="if(this.value){ document.querySelector('input[name=city]').value = this.value; }"
                                class="w-full py-2 px-3 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none transition-all">
                            <option value="">Select City</option>
                            @foreach($popularCities as $pCity)
                                <option value="{{ $pCity->name }}" {{ $displayCity === $pCity->name ? 'selected' : '' }}>
                                    {{ $pCity->name }} {{ $pCity->is_active ? '' : '(Coming Soon)' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Property Type — dynamic: only shows types that exist in DB -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Property Type</label>
                        <div class="space-y-2.5">
                            @forelse($roomTypeOptions as $option)
                                @php
                                    $count = $roomTypeCounts[$option->id] ?? 0;
                                    $isChecked = in_array($option->id, (array)request('room_type'));
                                @endphp
                                <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <input type="checkbox" name="room_type[]" value="{{ $option->id }}" {{ $isChecked ? 'checked' : '' }}
                                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/20">
                                        <span>{{ $option->label }}</span>
                                    </span>
                                    <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $count }}</span>
                                </label>
                            @empty
                                <p class="rounded-lg bg-amber-50 px-3 py-2 text-[11px] font-semibold text-amber-700">
                                    No active property types configured.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Budget Range -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Budget (per month)</label>
                        <div class="space-y-2">
                            @php
                                $budgetRanges = [
                                    ['label' => 'Under ₹5,000', 'min' => 0, 'max' => 5000],
                                    ['label' => '₹5,000 - ₹10,000', 'min' => 5000, 'max' => 10000],
                                    ['label' => '₹10,000 - ₹15,000', 'min' => 10000, 'max' => 15000],
                                    ['label' => '₹15,000 - ₹20,000', 'min' => 15000, 'max' => 20000],
                                    ['label' => 'Above ₹20,000', 'min' => 20000, 'max' => 999999],
                                ];
                            @endphp
                            @foreach($budgetRanges as $range)
                                @php
                                    $isSel = request('min_rent') == $range['min'] && request('max_rent') == $range['max'];
                                @endphp
                                <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600 transition-colors">
                                    <input type="radio" name="budget_range" onchange="document.querySelector('input[name=min_rent]').value='{{ $range['min'] }}'; document.querySelector('input[name=max_rent]').value='{{ $range['max'] }}';"
                                           {{ $isSel ? 'checked' : '' }}
                                           class="text-indigo-600 focus:ring-indigo-500/20 border-slate-300">
                                    <span>{{ $range['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <!-- Manual Min / Max inputs -->
                        <div class="grid grid-cols-2 gap-2 pt-2">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Min</span>
                                <input type="number" name="min_rent" value="{{ request('min_rent') }}" placeholder="₹ Min"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Max</span>
                                <input type="number" name="max_rent" value="{{ request('max_rent') }}" placeholder="₹ Max"
                                       class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Gender Preference — dynamic from DB tenant type counts -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Gender Preference</label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                @php
                                    $tCount = $tenantTypeCounts[$option->id] ?? 0;
                                    $tChecked = in_array($option->id, (array)request('tenant_type'));
                                @endphp
                                @if($tCount > 0 || $tChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="tenant_type[]" value="{{ $option->id }}" {{ $tChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300 text-indigo-600">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $tCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Furnishing Type — dynamic from DB -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Furnishing</label>
                        <div class="space-y-2">
                            @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                                @php
                                    $fCount = $furnishingCounts[$option->id] ?? 0;
                                    $fChecked = in_array($option->id, (array)request('furnishing_type'));
                                @endphp
                                @if($fCount > 0 || $fChecked)
                                    <label class="flex items-center justify-between text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" name="furnishing_type[]" value="{{ $option->id }}" {{ $fChecked ? 'checked' : '' }}
                                                   class="rounded border-slate-300 text-indigo-600">
                                            <span>{{ $option->label }}</span>
                                        </span>
                                        <span class="text-[9px] text-slate-400 font-bold bg-slate-100 px-1.5 py-0.5 rounded-full">{{ $fCount }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Amenities</label>
                        <div class="space-y-2">
                            @php
                                $amenityOpts = [
                                    'wifi' => 'Wi-Fi',
                                    'ac' => 'AC',
                                    'parking' => 'Parking',
                                    'kitchen' => 'Kitchen',
                                    'power_backup' => 'Power Backup',
                                    'washing_machine' => 'Washing Machine'
                                ];
                            @endphp
                            @foreach($amenityOpts as $key => $lbl)
                                <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600">
                                    <input type="checkbox" name="amenities[]" value="{{ $key }}" {{ in_array($key, (array)request('amenities')) ? 'checked' : '' }}
                                           class="rounded border-slate-300 text-indigo-600">
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-700 uppercase tracking-wider block">Availability</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 text-xs text-slate-600 font-semibold cursor-pointer hover:text-indigo-600">
                                <input type="checkbox" name="available_now" value="1" {{ request('available_now') == '1' ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-indigo-600">
                                <span>Available Now</span>
                            </label>
                            
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase">Available From</span>
                                <input type="date" name="availability_from" value="{{ request('availability_from') }}"
                                       class="w-full py-1.5 px-2 bg-slate-50 border border-slate-200 text-slate-800 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1.5 text-xs shadow-indigo-600/10">
                        Apply Filters
                    </button>
                </form>
            </div>
            <div class="mt-5">
                @include('partials.offer-banner', ['placement' => 'sidebar'])
            </div>
        </div>

        <!-- ===== RIGHT COLUMN (ROOMS GRID) ===== -->
        <div class="flex-grow min-w-0">
            <!-- Header bar inside list -->
            <div class="rooms-results-head flex items-center justify-between mb-5 flex-wrap gap-3">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 font-heading">
                        All Rooms in {{ $cityContext['activeCityName'] ?? $displayCity ?? 'India' }}
                    </h2>
                    <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">
                        {{ $rooms->total() }}+ Rooms Found
                    </span>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Sort selection -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-slate-400">Sort by:</span>
                        <select onchange="const url = new URL(window.location.href); url.searchParams.set('sort_by', this.value); window.location.href = url.toString();"
                                class="py-1.5 pl-3 pr-8 bg-white border border-slate-200 text-slate-700 rounded-xl text-xs font-bold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none appearance-none cursor-pointer">
                            <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="rent_asc" {{ request('sort_by') == 'rent_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="rent_desc" {{ request('sort_by') == 'rent_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                    <!-- Layout Grid/List selectors -->
                    <div class="hidden sm:flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                        <button class="w-7 h-7 bg-white text-indigo-600 rounded-lg flex items-center justify-center text-xs shadow-sm" title="Grid view">
                            <i class="fas fa-grip-vertical"></i>
                        </button>
                        <button class="w-7 h-7 text-slate-400 hover:text-slate-600 rounded-lg flex items-center justify-center text-xs transition-colors" title="List view">
                            <i class="fas fa-list-ul"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filters Pills row -->
            @php
                $activeFilters = [];
                if (request('city')) $activeFilters['city'] = ['label' => 'City: ' . request('city'), 'param' => 'city'];
                if (request('room_type')) {
                    foreach((array)request('room_type') as $t) {
                        $activeFilters['room_type_' . $t] = ['label' => \App\Models\RoomOption::getLabel('room_type', $t), 'param' => 'room_type', 'value' => $t];
                    }
                }
                if (request('min_rent') || request('max_rent')) {
                    $label = 'Budget: ';
                    if (request('min_rent') && request('max_rent')) $label .= '₹' . request('min_rent') . ' - ₹' . request('max_rent');
                    elseif (request('min_rent')) $label .= 'Min ₹' . request('min_rent');
                    else $label .= 'Max ₹' . request('max_rent');
                    $activeFilters['budget'] = ['label' => $label, 'param' => ['min_rent', 'max_rent']];
                }
                if (request('tenant_type')) {
                    foreach((array)request('tenant_type') as $t) {
                        $activeFilters['tenant_type_' . $t] = ['label' => \App\Models\RoomOption::getLabel('tenant_type', $t), 'param' => 'tenant_type', 'value' => $t];
                    }
                }
                if (request('furnishing_type')) {
                    foreach((array)request('furnishing_type') as $f) {
                        $activeFilters['furnishing_type_' . $f] = ['label' => \App\Models\RoomOption::getLabel('furnishing_type', $f), 'param' => 'furnishing_type', 'value' => $f];
                    }
                }
                if (request('amenities')) {
                    foreach((array)request('amenities') as $a) {
                        $activeFilters['amenity_' . $a] = ['label' => ucwords(str_replace('_', ' ', $a)), 'param' => 'amenities', 'value' => $a];
                    }
                }
            @endphp
            @if(!empty($activeFilters))
                <div class="flex items-center gap-2 flex-wrap mb-6 bg-slate-50 border border-slate-200/60 rounded-2xl p-3">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Active Filters:</span>
                    @foreach($activeFilters as $key => $filter)
                        @php
                            if (is_array($filter['param'])) {
                                $newParams = request()->except($filter['param']);
                            } else {
                                if (isset($filter['value'])) {
                                    $arr = (array)request($filter['param']);
                                    $newArr = array_filter($arr, fn($val) => $val !== $filter['value']);
                                    $newParams = request()->except($filter['param']);
                                    if(!empty($newArr)) {
                                        $newParams[$filter['param']] = array_values($newArr);
                                    }
                                } else {
                                    $newParams = request()->except($filter['param']);
                                }
                            }
                            unset($newParams['clear']);
                        @endphp
                        <a href="{{ route('rooms.index', $newParams) }}"
                           class="inline-flex items-center gap-1 bg-white border border-slate-200/80 hover:border-red-300 text-slate-600 hover:text-red-500 text-[10px] font-bold px-2.5 py-0.5 rounded-full transition-all">
                            <span>{{ $filter['label'] }}</span>
                            <i class="fas fa-times text-[8px]"></i>
                        </a>
                    @endforeach
                    <a href="{{ route('rooms.index', ['clear' => 1]) }}"
                       class="text-[10px] font-black text-red-500 hover:text-red-700 transition-colors uppercase tracking-wider ml-1">
                        Clear All
                    </a>
                </div>
            @endif

            <!-- Rooms list -->
            @if($rooms->count() > 0)
                <!-- Desktop Columns Grid (Flexbox wrapper for guaranteed column layout) -->
                <div class="hidden md:flex flex-wrap -mx-2.5">
                    @foreach($rooms as $room)
                        <div class="w-full md:w-1/2 xl:w-1/3 px-2.5 mb-5 flex flex-col">
                            <div class="room-listing-card group bg-white rounded-2xl border transition-all duration-300 overflow-hidden flex flex-col h-full hover:-translate-y-1">
                                <!-- Image Area -->
                                <a href="{{ route('rooms.show', $room->id) }}" class="room-image relative block overflow-hidden bg-slate-100">
                                    @if($room->photo_url)
                                        <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                                            <i class="fas fa-image text-3xl mb-1"></i>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">No Image</span>
                                        </div>
                                    @endif

                                    <!-- Status Badges -->
                                    <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5 z-10">
                                        @if($room->is_featured)
                                            <span class="bg-amber-500 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">Featured</span>
                                        @endif
                                        <span class="bg-white/90 backdrop-blur-sm text-indigo-700 text-[8px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-lg border border-white/40 shadow-sm">
                                            {{ $room->roomTypeLabel() }}
                                        </span>
                                        @if($room->listing_type === 'broker')
                                            <span class="bg-orange-500 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">Broker Fee</span>
                                        @else
                                            <span class="bg-emerald-600 text-white text-[8px] font-black uppercase tracking-wider px-2 py-0.5 rounded-lg">No Broker Fee</span>
                                        @endif
                                    </div>

                                    <!-- Wishlist heart -->
                                    <button onclick="toggleWishlist(event, {{ $room->id }})" id="wishlist-btn-{{ $room->id }}"
                                            class="absolute top-2.5 right-2.5 w-8 h-8 rounded-xl bg-white/95 backdrop-blur-sm shadow-md text-slate-400 hover:text-red-500 active:scale-90 transition-all flex items-center justify-center">
                                        <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas text-red-500' : 'far' }} fa-heart text-sm"></i>
                                    </button>

                                    <!-- Price tag overlay -->
                                    <div class="absolute bottom-2.5 left-2.5">
                                        <div class="room-price-tag px-3 py-1 rounded-xl">
                                            <span class="text-sm font-black">₹{{ number_format($room->rent) }}</span>
                                            <span class="text-[8px] font-bold text-indigo-100">/mo</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- Card content -->
                                <div class="room-card-body flex flex-col flex-grow">
                                    <h3 class="font-bold text-sm text-slate-900 line-clamp-2 mb-2 group-hover:text-indigo-600 transition-colors">
                                        <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
                                    </h3>

                                    <div class="flex items-center text-slate-500 text-xs mb-3">
                                        <i class="fas fa-location-dot mr-1.5 text-indigo-500"></i>
                                        <span>{{ $room->city }}</span>
                                        <div class="distance-tag hidden ml-2 flex items-center gap-1" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                            <div class="w-1 h-1 bg-emerald-500 rounded-full"></div>
                                            <span class="text-[9px] font-extrabold text-emerald-600 uppercase tracking-widest"><span class="distance-km">0</span> km</span>
                                        </div>
                                    </div>

                                    <!-- Quick Specs -->
                                    <div class="flex flex-wrap gap-1.5 mb-4 mt-auto">
                                        <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                            <i class="fas fa-couch text-indigo-400"></i> {{ $room->furnishingTypeLabel() }}
                                        </span>
                                        @if($room->tenantTypeLabel() !== 'N/A')
                                            <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-lg flex items-center gap-1">
                                                <i class="fas fa-users text-indigo-400"></i> {{ $room->tenantTypeLabel() }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="room-owner-row">
                                        <img src="{{ $room->user?->avatar ? asset('storage/'.$room->user->avatar) : asset('assets/images/default-avatar.svg') }}"
                                             alt="{{ $room->user?->name ?? 'Property owner' }}"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'">
                                        <span>Owner: <strong>{{ $room->user?->name ?? 'Verified owner' }}</strong></span>
                                    </div>

                                    <!-- Bottom actions -->
                                    @auth
                                        @if(Auth::user()->role === 'owner' && Auth::id() === $room->user_id)
                                            <div class="grid grid-cols-2 gap-2 mt-auto">
                                                <a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-center bg-amber-50 text-amber-700 font-extrabold py-2 rounded-xl hover:bg-amber-100 transition-colors text-xs">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('rooms.destroy', $room->id) }}" method="POST" class="delete-room-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center justify-center bg-red-50 text-red-600 font-extrabold py-2 rounded-xl hover:bg-red-100 transition-colors text-xs">
                                                        <i class="fas fa-trash mr-1"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('rooms.show', $room->id) }}" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                                View Details <i class="fas fa-arrow-right text-[10px]"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="{{ route('rooms.show', $room->id) }}" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl transition-all shadow-md flex items-center justify-center gap-1 text-xs mt-auto">
                                            View Details <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Mobile listing support -->
                <div class="md:hidden">
                    @include('rooms.partials.listing-mobile')
                </div>

                <!-- Custom premium layout pagination -->
                <div class="flex justify-center mt-8">
                    {{ $rooms->withQueryString()->links() }}
                </div>
            @else
                <!-- Empty state fallback -->
                <div class="text-center py-16 bg-white border border-slate-200/80 rounded-2xl shadow-sm">
                    <div class="max-w-md mx-auto">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full mb-6 shadow-sm">
                            <i class="fas fa-house-circle-xmark text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-800 mb-2">No Rooms Found</h3>
                        <p class="text-slate-500 mb-6 text-sm">We couldn't find any rooms matching your search criteria. Try modifying your filters or view all rooms.</p>
                        <a href="{{ route('rooms.index', ['clear' => 1]) }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold py-2.5 px-6 rounded-xl transition-all shadow-md text-xs">
                            <i class="fas fa-rotate-left mr-1.5"></i> Clear All Filters
                        </a>
                        
                        @if(request('city'))
                            <div class="mt-8 p-5 border border-indigo-50 bg-indigo-50/20 rounded-2xl">
                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Get Alerted</h4>
                                <p class="text-slate-500 text-xs mb-3">Subscribe and we will email you when new rooms open up in <strong>{{ request('city') }}</strong>.</p>
                                <button onclick="subscribeToAlerts('{{ request('city') }}')" id="notify-btn"
                                        class="py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold rounded-xl text-xs transition-all shadow-sm">
                                    <i class="fas fa-bell mr-1"></i> Notify Me
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
</div>

<!-- ===== BOTTOM TRUST RIBBON ===== -->
<div class="rooms-trust-strip bg-[#0b0f19] text-white py-12 border-t border-slate-900 mt-12">
    <div class="container mx-auto px-6">
        <div class="rooms-trust-grid grid grid-cols-2 md:grid-cols-4 gap-8 text-center md:text-left">
            <div class="rooms-trust-item flex flex-col md:flex-row items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <span class="block text-sm font-black tracking-wide text-white uppercase font-black">100% Verified</span>
                    <span class="block text-xs text-slate-400 mt-0.5">Physical inspections done</span>
                </div>
            </div>
            <div class="rooms-trust-item flex flex-col md:flex-row items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <span class="block text-sm font-black tracking-wide text-white uppercase font-black">Zero Brokerage</span>
                    <span class="block text-xs text-slate-400 mt-0.5">Direct owner connections</span>
                </div>
            </div>
            <div class="rooms-trust-item flex flex-col md:flex-row items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <span class="block text-sm font-black tracking-wide text-white uppercase font-black">Secure Payments</span>
                    <span class="block text-xs text-slate-400 mt-0.5">Safe online transactions</span>
                </div>
            </div>
            <div class="rooms-trust-item flex flex-col md:flex-row items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-headset"></i>
                </div>
                <div>
                    <span class="block text-sm font-black tracking-wide text-white uppercase font-black">24/7 Support</span>
                    <span class="block text-xs text-slate-400 mt-0.5">Help throughout renting</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const hasSearchIntent = @json($hasMetaSearchIntent);
    if (hasSearchIntent) {
        window.trackRoomNestEvent?.('Search', {
            search_string: @json(request('city') ?? session('user_city') ?? ''),
            city: @json($displayCity ?? ''),
            content_type: 'room'
        });
    }
});
</script>
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

    @if(!request('city') && !session('user_city') && !session('no_auto'))
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => detectLocation(), 2000);
        });
    @endif
</script>

<script defer>
    document.addEventListener('DOMContentLoaded', () => {
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

<!-- Infinite Scroll Script for Mobile -->
<script defer>
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
@endpush
@endsection
