@extends('layouts.public')

@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
@endphp

@section('title', 'Verified Real Estate Agencies & Agents | ' . $siteName)
@section('description', 'Find and connect directly with top KYC-verified real estate agents and agencies. Browse verified rental flats, rooms, and commercial properties with zero unlock charges.')

@push('styles')
<style>
/* Scoped Agency Directory Styles */
.agencies-directory-page {
    background-color: #f8fafc;
    min-height: 100vh;
}

/* Hero Section */
.agency-hero {
    background: linear-gradient(135deg, #090e17 0%, #0f172a 50%, #1e1b4b 100%) !important;
    color: #ffffff !important;
    position: relative;
    overflow: hidden;
    padding-top: 3rem;
    padding-bottom: 5.5rem;
}

.agency-hero-glow-1 {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(99, 102, 241, 0) 70%);
    pointer-events: none;
}

.agency-hero-glow-2 {
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.20) 0%, rgba(16, 185, 129, 0) 70%);
    pointer-events: none;
}

.agency-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 9999px;
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: #34d399;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 1rem;
}

.agency-hero-badge .pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseGlow 2s infinite;
}

@keyframes pulseGlow {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.agency-hero-title {
    font-size: clamp(26px, 4.5vw, 46px);
    font-weight: 900;
    line-height: 1.15;
    color: #ffffff !important;
    letter-spacing: -0.025em;
    margin: 0;
}

.agency-hero-gradient-text {
    background: linear-gradient(135deg, #818cf8 0%, #38bdf8 50%, #34d399 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.agency-hero-sub {
    color: #cbd5e1 !important;
    font-size: 15px;
    line-height: 1.65;
    max-width: 650px;
    margin-top: 0.75rem;
}

.agency-stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.07);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: #e2e8f0;
    font-size: 12.5px;
    backdrop-filter: blur(8px);
}

.agency-stat-chip-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

/* Floating Search Bar */
.agency-search-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 12px 14px;
    box-shadow: 0 16px 40px -8px rgba(15, 23, 42, 0.12), 0 4px 14px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    margin-top: -36px;
    position: relative;
    z-index: 25;
}

.agency-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.agency-input-group i {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}

.agency-search-input {
    width: 100%;
    padding: 12px 16px 12px 42px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    outline: none;
    transition: all 0.2s ease;
}

.agency-search-input:focus {
    background: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.agency-search-btn {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
}

.agency-search-btn:hover {
    background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
}

/* Agency Cards */
.agency-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.04);
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.agency-card:hover {
    transform: translateY(-5px);
    border-color: #cbd5e1;
    box-shadow: 0 20px 35px -8px rgba(15, 23, 42, 0.12), 0 8px 16px -4px rgba(15, 23, 42, 0.04);
}

.agency-card-banner {
    height: 72px;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 12px 16px;
}

.agency-card-avatar {
    width: 68px;
    height: 68px;
    border-radius: 18px;
    border: 3.5px solid #ffffff;
    position: absolute;
    left: 20px;
    bottom: -28px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 26px;
    font-weight: 900;
    box-shadow: 0 8px 18px -2px rgba(15, 23, 42, 0.2);
    overflow: hidden;
    z-index: 5;
}

.agency-card-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agency-card-avatar-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9.5px;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.agency-card-body {
    padding: 38px 20px 20px;
    flex-grow: 1;
}

.agency-name-link {
    font-size: 17px;
    font-weight: 850;
    color: #0f172a;
    text-decoration: none;
    transition: color 0.18s ease;
    display: inline-block;
    line-height: 1.3;
}

.agency-name-link:hover {
    color: #4f46e5;
}

.agency-stat-badge {
    padding: 10px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

.agency-btn-primary {
    background: #0f172a;
    color: #ffffff;
    border-radius: 12px;
    padding: 11px 16px;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    flex: 1;
}

.agency-btn-primary:hover {
    background: #4f46e5;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
}

.agency-btn-whatsapp {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #25D366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.28);
}

.agency-btn-whatsapp:hover {
    background: #1eb956;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
}

.agency-btn-phone {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    text-decoration: none;
    transition: all 0.2s ease;
    flex-shrink: 0;
    border: 1px solid #e2e8f0;
}

.agency-btn-phone:hover {
    background: #e2e8f0;
    color: #0f172a;
}
</style>
@endpush

@section('content')
<div class="agencies-directory-page pb-20">

    {{-- Hero Section --}}
    <section class="agency-hero">
        <div class="agency-hero-glow-1"></div>
        <div class="agency-hero-glow-2"></div>

        <div class="container mx-auto max-w-6xl px-4 relative z-10">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-5">
                <a href="{{ route('home') }}" class="hover:text-white transition text-slate-400">Home</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-200 font-bold">Verified Agencies</span>
            </nav>

            <div class="max-w-3xl">
                <div class="agency-hero-badge">
                    <span class="pulse-dot"></span>
                    <i class="fas fa-shield-check"></i>
                    <span>100% KYC & License Verified Real Estate Directory</span>
                </div>

                <h1 class="agency-hero-title">
                    Verified Real Estate <span class="agency-hero-gradient-text">Agencies & Consultants</span>
                </h1>

                <p class="agency-hero-sub">
                    Connect directly with trusted property consultants and authorized real estate agencies. Browse verified rental rooms, flats, and commercial properties with <strong>zero unlock fees</strong> and direct WhatsApp contact.
                </p>
            </div>

            {{-- Stat Chips --}}
            <div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-white/10">
                <div class="agency-stat-chip">
                    <span class="agency-stat-chip-icon text-indigo-400">
                        <i class="fas fa-building"></i>
                    </span>
                    <span><strong class="text-white text-sm font-extrabold">{{ $totalAgenciesCount }}</strong> Verified Agencies</span>
                </div>

                <div class="agency-stat-chip">
                    <span class="agency-stat-chip-icon text-emerald-400">
                        <i class="fas fa-house-circle-check"></i>
                    </span>
                    <span><strong class="text-white text-sm font-extrabold">{{ $totalBrokerProperties }}</strong> Active Managed Properties</span>
                </div>

                <div class="agency-stat-chip">
                    <span class="agency-stat-chip-icon text-amber-400">
                        <i class="fas fa-comments"></i>
                    </span>
                    <span>Direct WhatsApp & Call Connect</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Floating Search & Filter Bar --}}
    <div class="container mx-auto max-w-6xl px-4">
        <form method="GET" action="{{ route('agencies.index') }}" class="agency-search-card flex flex-col md:flex-row items-stretch md:items-center gap-3">
            {{-- Keyword Search --}}
            <div class="agency-input-group flex-1">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Search agency name, agent, locality or license..."
                       class="agency-search-input">
            </div>

            {{-- City Selector --}}
            <div class="agency-input-group md:w-56">
                <i class="fas fa-location-dot text-rose-500"></i>
                <select name="city" class="agency-search-input cursor-pointer appearance-none pr-8">
                    <option value="">All Cities</option>
                    @foreach($availableCities as $c)
                        <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 absolute right-4 left-auto pointer-events-none"></i>
            </div>

            {{-- Sort By --}}
            <div class="agency-input-group md:w-48">
                <i class="fas fa-arrow-down-wide-short text-indigo-500"></i>
                <select name="sort_by" class="agency-search-input cursor-pointer appearance-none pr-8">
                    <option value="popular" {{ request('sort_by') === 'popular' ? 'selected' : '' }}>Most Active</option>
                    <option value="properties" {{ request('sort_by') === 'properties' ? 'selected' : '' }}>Most Properties</option>
                    <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Agency Name (A-Z)</option>
                </select>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 absolute right-4 left-auto pointer-events-none"></i>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="agency-search-btn w-full md:w-auto">
                    <i class="fas fa-search text-xs"></i>
                    <span>Find Agencies</span>
                </button>

                @if(request()->filled('q') || request()->filled('city') || request()->filled('sort_by'))
                    <a href="{{ route('agencies.index') }}" class="p-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center justify-center text-sm" title="Clear Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Agencies Listing Section --}}
    <main class="container mx-auto max-w-6xl px-4 mt-12">
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-3 border-b border-slate-200">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                    <span>Featured Real Estate Partners</span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200/70">
                        {{ $agencies->total() }} Listed
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Contact authorized real estate consultants directly with complete transparency.
                </p>
            </div>

            @if(request()->filled('city'))
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-xs font-bold text-slate-700">
                    <i class="fas fa-location-dot text-rose-500"></i>
                    <span>Filtered by: {{ request('city') }}</span>
                    <a href="{{ route('agencies.index', request()->except('city')) }}" class="ml-1 text-slate-400 hover:text-slate-700">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>
            @endif
        </div>

        @if($agencies->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($agencies as $agency)
                    @php
                        $agencyDisplayName = $agency->agency_name ?: ($agency->name . ' Real Estate');
                        $operatingCities = $agency->rooms ? $agency->rooms->pluck('city')->filter()->unique()->values() : collect();
                        $primaryCity = $operatingCities->first() ?: '';
                        $rawPhone = trim((string) ($agency->phone ?? ''));
                        $digits = preg_replace('/\D+/', '', $rawPhone);
                        if (strlen($digits) === 10) $digits = '91' . $digits;
                        elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) $digits = '91' . substr($digits, 1);
                        $waMsg = "Hello {$agencyDisplayName}! Maine aapki agency profile {$siteName} par dekhi hai. Mujhe rental properties ke baare mein jaankari chahiye.";
                        $waLink = !empty($digits) ? "https://wa.me/{$digits}?text=" . rawurlencode($waMsg) : null;
                    @endphp

                    <div class="agency-card">
                        {{-- Top Header Banner --}}
                        <div class="agency-card-banner">
                            {{-- City Tag or RERA Pill on Banner --}}
                            @if($primaryCity)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
                                    <i class="fas fa-location-dot text-rose-400 text-[10px]"></i>
                                    <span>{{ $primaryCity }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white/15 backdrop-blur-md text-white text-[11px] font-bold border border-white/20">
                                    <i class="fas fa-certificate text-emerald-400 text-[10px]"></i>
                                    <span>Verified</span>
                                </span>
                            @endif

                            {{-- Avatar overlapping banner --}}
                            <div class="agency-card-avatar">
                                @if($agency->avatar)
                                    <img src="{{ asset('storage/' . $agency->avatar) }}" alt="{{ $agencyDisplayName }}">
                                @else
                                    <span>{{ strtoupper(substr($agencyDisplayName, 0, 1)) }}</span>
                                @endif
                                <span class="agency-card-avatar-badge" title="KYC Verified Broker">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="agency-card-body">
                            {{-- Agency Name --}}
                            <div class="mb-1">
                                <a href="{{ route('agency.show', $agency) }}" class="agency-name-link truncate max-w-full block" title="{{ $agencyDisplayName }}">
                                    {{ $agencyDisplayName }}
                                </a>
                            </div>

                            {{-- Agent & City details --}}
                            <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mb-3">
                                <i class="fas fa-user-tie text-indigo-500 text-[11px]"></i>
                                <span class="text-slate-700 font-semibold">{{ $agency->name }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded text-[10px]">
                                    Verified Consultant
                                </span>
                            </p>

                            {{-- License / RERA badge --}}
                            @if($agency->broker_license)
                                <div class="mb-3 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200/80 text-[11px] font-bold text-amber-900 inline-flex items-center gap-1.5 max-w-full truncate">
                                    <i class="fas fa-id-card text-amber-600 shrink-0"></i>
                                    <span class="truncate">RERA / Lic: {{ $agency->broker_license }}</span>
                                </div>
                            @endif

                            {{-- Office Address if available --}}
                            @if($agency->agency_address)
                                <p class="text-xs text-slate-500 line-clamp-2 mb-4 leading-relaxed flex items-start gap-1.5" title="{{ $agency->agency_address }}">
                                    <i class="fas fa-building text-slate-400 mt-0.5 shrink-0 text-[11px]"></i>
                                    <span>{{ $agency->agency_address }}</span>
                                </p>
                            @else
                                <p class="text-xs text-slate-400 italic mb-4">
                                    <i class="fas fa-shield-heart text-indigo-400 mr-1"></i>Zero brokerage direct contact listing partner
                                </p>
                            @endif

                            {{-- Stats Ribbon --}}
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <div class="agency-stat-badge">
                                    <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Active Portfolio</span>
                                    <span class="font-black text-slate-900 text-sm flex items-center gap-1.5 mt-0.5">
                                        <i class="fas fa-house-circle-check text-emerald-500 text-xs"></i>
                                        {{ $agency->active_rooms_count }} {{ Str::plural('Listing', $agency->active_rooms_count) }}
                                    </span>
                                </div>

                                <div class="agency-stat-badge">
                                    <span class="text-[10px] text-slate-400 block font-bold uppercase tracking-wider">Trust Level</span>
                                    <span class="font-black text-amber-600 text-sm flex items-center gap-1.5 mt-0.5">
                                        <i class="fas fa-award text-amber-500 text-xs"></i>
                                        100% KYC OK
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="px-5 pb-5 pt-1 border-t border-slate-100 flex items-center gap-2">
                            <a href="{{ route('agency.show', $agency) }}" class="agency-btn-primary">
                                <span>View Portfolio</span>
                                <i class="fas fa-arrow-right text-[11px]"></i>
                            </a>

                            @if($waLink)
                                <a href="{{ $waLink }}" target="_blank" rel="noopener" class="agency-btn-whatsapp" title="Chat on WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            @endif

                            @if(!empty($rawPhone))
                                <a href="tel:{{ $rawPhone }}" class="agency-btn-phone" title="Call Agent">
                                    <i class="fas fa-phone-alt"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($agencies->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $agencies->links() }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 shadow-sm max-w-lg mx-auto my-8">
                <div class="w-20 h-20 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-5 text-3xl shadow-inner">
                    <i class="fas fa-building-circle-xmark"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-1">No Verified Agencies Found</h3>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed max-w-sm mx-auto">
                    We couldn't find any real estate partners matching your criteria. Try clearing filters or searching for another city.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('agencies.index') }}" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition shadow-md shadow-indigo-200 inline-flex items-center gap-2">
                        <i class="fas fa-rotate-left text-[11px]"></i>
                        <span>Reset All Filters</span>
                    </a>
                    <a href="{{ route('rooms.index') }}" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition inline-flex items-center gap-2">
                        <i class="fas fa-house text-[11px]"></i>
                        <span>Browse All Rooms</span>
                    </a>
                </div>
            </div>
        @endif
    </main>

    {{-- Trust / Why Choose Verified Agencies Banner --}}
    <section class="container mx-auto max-w-6xl px-4 mt-16">
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-3xl p-8 sm:p-10 text-white shadow-xl relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-emerald-400 text-xl shrink-0">
                        <i class="fas fa-badge-check"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-white text-sm mb-1">Government & KYC Verified</h4>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Every agency is verified with identity checks, broker licenses and verified office details.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-indigo-400 text-xl shrink-0">
                        <i class="fas fa-unlock-keyhole"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-white text-sm mb-1">Zero Contact Unlock Charge</h4>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Connect directly via WhatsApp or phone with brokers. No payment required to unlock phone numbers.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-amber-400 text-xl shrink-0">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-white text-sm mb-1">Direct Owner / Broker Deals</h4>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Browse their full active portfolio of independent rooms, furnished flats and commercial spaces.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
