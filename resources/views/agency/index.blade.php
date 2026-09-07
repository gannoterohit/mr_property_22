@extends('layouts.public')

@php
    $siteName   = \App\Models\Setting::get('website_name', 'RoomRental');
@endphp

@section('title', 'Verified Real Estate Agencies & Agents | ' . $siteName)
@section('description', 'Find and connect directly with top KYC-verified real estate agents and agencies. Browse verified rental flats, rooms, and commercial properties with zero unlock charges.')

@push('styles')
<style>
/* =============================================
   Agency Directory – Dynamic Themed Styles
   Uses var(--primary) & var(--primary-rgb)
   set dynamically from admin settings
   ============================================= */
.agencies-directory-page {
    background-color: #f8fafc;
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
}

/* ── Hero ─────────────────────────────────── */
.agency-hero {
    background: linear-gradient(145deg,
        color-mix(in srgb, var(--primary) 15%, #000) 0%,
        color-mix(in srgb, var(--primary) 25%, #0f172a) 50%,
        color-mix(in srgb, var(--primary) 35%, #1e293b) 100%) !important;
    color: #ffffff !important;
    position: relative;
    overflow: hidden;
    padding-top: 4rem;
    padding-bottom: 7rem;
    width: 100%;
    display: block;
}

.agency-hero-glow-1 {
    position: absolute;
    top: -80px;
    right: -80px;
    width: 420px;
    height: 420px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.22) 0%, rgba(var(--primary-rgb), 0) 70%);
    pointer-events: none;
}

.agency-hero-glow-2 {
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.18) 0%, rgba(var(--primary-rgb), 0) 70%);
    pointer-events: none;
}

.agency-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 9999px;
    background: rgba(var(--primary-rgb), 0.18);
    border: 1px solid rgba(var(--primary-rgb), 0.38);
    color: #a7f3d0;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 1.25rem;
}

.agency-hero-badge .pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: agencyPulse 2s infinite;
}

@keyframes agencyPulse {
    0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129,0.7); }
    70%  { transform: scale(1);    box-shadow: 0 0 0 6px rgba(16,185,129,0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129,0); }
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
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 70%, white) 0%,
        #a7f3d0 60%, #7dd3fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.agency-hero-sub {
    color: #cbd5e1 !important;
    font-size: 15px;
    line-height: 1.65;
    max-width: 650px;
    margin-top: 0.85rem;
}

.agency-stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 9px 16px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: #e2e8f0;
    font-size: 12.5px;
    backdrop-filter: blur(8px);
}

.agency-stat-chip-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

/* ── Search Bar ──────────────────────────── */
.agency-search-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 16px 20px;
    box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.14), 0 4px 18px rgba(0,0,0,0.04);
    border: 1px solid #e2e8f0;
    margin-top: -38px;
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
    padding: 13px 16px 13px 44px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    outline: none;
    transition: all 0.2s ease;
}

.agency-search-input:focus {
    background: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.14);
}

.agency-search-btn {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 80%, #000) 0%,
        var(--primary) 100%);
    color: #ffffff;
    padding: 13px 26px;
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(var(--primary-rgb), 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    white-space: nowrap;
}

.agency-search-btn:hover {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 60%, #000) 0%,
        color-mix(in srgb, var(--primary) 80%, #000) 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(var(--primary-rgb), 0.45);
}

/* ── Agency Cards ────────────────────────── */
.agency-card {
    background: #ffffff;
    border-radius: 22px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.agency-card:hover {
    transform: translateY(-5px);
    border-color: rgba(var(--primary-rgb), 0.3);
    box-shadow: 0 22px 40px -8px rgba(var(--primary-rgb), 0.16), 0 8px 16px -4px rgba(15,23,42,0.04);
}

.agency-card-banner {
    height: 84px;
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 25%, #0f172a) 0%,
        color-mix(in srgb, var(--primary) 40%, #1e293b) 100%);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 14px 18px;
}

.agency-card-avatar {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    border: 4px solid #ffffff;
    position: absolute;
    left: 22px;
    bottom: -32px;
    background: linear-gradient(135deg, var(--primary) 0%, color-mix(in srgb, var(--primary) 60%, #7c3aed) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 28px;
    font-weight: 900;
    box-shadow: 0 10px 22px -3px rgba(var(--primary-rgb), 0.3);
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
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #10b981;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.agency-card-body {
    padding: 48px 22px 24px;
    flex-grow: 1;
}

.agency-name-link {
    font-size: 17.5px;
    font-weight: 850;
    color: #0f172a;
    text-decoration: none;
    transition: color 0.18s ease;
    display: inline-block;
    line-height: 1.35;
}

.agency-name-link:hover {
    color: var(--primary);
}

.agency-stat-badge {
    padding: 12px 14px;
    border-radius: 14px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

/* ── Card Buttons ────────────────────────── */
.agency-btn-primary {
    background: var(--primary);
    color: #ffffff;
    border-radius: 14px;
    padding: 12px 18px;
    font-size: 13.5px;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    flex: 1;
    box-shadow: 0 4px 14px rgba(var(--primary-rgb), 0.28);
}

.agency-btn-primary:hover {
    background: color-mix(in srgb, var(--primary) 80%, #000);
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(var(--primary-rgb), 0.4);
    transform: translateY(-1px);
}

.agency-btn-whatsapp {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #25D366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    text-decoration: none;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(37, 211, 102, 0.28);
}

.agency-btn-whatsapp:hover {
    background: #1eb956;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 211, 102, 0.4);
}

.agency-btn-phone {
    width: 46px;
    height: 46px;
    border-radius: 14px;
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
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    border-color: rgba(var(--primary-rgb), 0.25);
}

/* ── Section count badge ─────────────────── */
.agency-count-badge {
    font-size: 11.5px;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    font-weight: 800;
    border: 1px solid rgba(var(--primary-rgb), 0.2);
}

/* ── Filter tag ──────────────────────────── */
.agency-filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 14px;
    background: rgba(var(--primary-rgb), 0.06);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
}

/* ── Trust Banner ────────────────────────── */
.agency-trust-banner {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 15%, #0f172a) 0%,
        color-mix(in srgb, var(--primary) 20%, #1e293b) 100%);
    border-radius: 28px;
    padding: 3rem 2.5rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 50px -12px rgba(var(--primary-rgb), 0.22);
}

.agency-trust-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(var(--primary-rgb), 0.15) 0%, transparent 60%);
    pointer-events: none;
}

.agency-trust-icon {
    width: 52px;
    height: 52px;
    min-width: 52px;
    border-radius: 18px;
    background: rgba(255,255,255,0.1);
    display: flex !important;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.12);
}

.agency-trust-icon.icon-primary  { color: var(--primary); }
.agency-trust-icon.icon-emerald  { color: #34d399; }
.agency-trust-icon.icon-amber    { color: #fbbf24; }

/* ── Empty State ─────────────────────────── */
.agency-empty-icon {
    width: 84px;
    height: 84px;
    border-radius: 22px;
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    margin: 0 auto 1.25rem;
    border: 1px solid rgba(var(--primary-rgb), 0.15);
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
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
                <a href="{{ route('home') }}" class="hover:text-white transition text-slate-400">Home</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-200 font-bold">Verified Agencies</span>
            </nav>

            {{-- 2-Column Hero Layout --}}
            <div class="flex flex-col lg:flex-row items-center gap-10 lg:gap-16">
                {{-- Left: Title & Description --}}
                <div class="flex-1 min-w-0">
                    <div class="agency-hero-badge mb-4">
                        <span class="pulse-dot"></span>
                        <i class="fas fa-shield-check"></i>
                        <span>100% KYC &amp; License Verified Real Estate Directory</span>
                    </div>

                    <h1 class="agency-hero-title mb-4">
                        Verified Real Estate <span class="agency-hero-gradient-text">Agencies &amp; Consultants</span>
                    </h1>

                    <p class="agency-hero-sub mb-6">
                        Connect directly with trusted property consultants and authorized real estate agencies. Browse verified rental rooms, flats, and commercial properties with <strong>zero unlock fees</strong> and direct WhatsApp contact.
                    </p>

                    {{-- Stat Chips --}}
                    <div class="flex flex-wrap items-center gap-3 pt-5 border-t border-white/10">
                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon" style="color: #a7f3d0;">
                                <i class="fas fa-building"></i>
                            </span>
                            <span><strong class="text-white text-sm font-extrabold">{{ $totalAgenciesCount }}</strong> Verified Agencies</span>
                        </div>

                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon text-emerald-400">
                                <i class="fas fa-house-circle-check"></i>
                            </span>
                            <span><strong class="text-white text-sm font-extrabold">{{ $totalBrokerProperties }}</strong> Active Properties</span>
                        </div>

                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon text-amber-400">
                                <i class="fas fa-comments"></i>
                            </span>
                            <span>Direct WhatsApp Connect</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Decorative Feature Cards --}}
                <div class="hidden lg:flex flex-col gap-4 w-72 flex-shrink-0">
                    <div class="rounded-2xl p-4 flex items-center gap-4"
                         style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(255,255,255,0.12);">
                            <i class="fas fa-badge-check text-emerald-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white text-sm font-bold">KYC &amp; License Verified</div>
                            <div class="text-slate-400 text-xs mt-0.5">Every agency is ID-checked</div>
                        </div>
                    </div>

                    <div class="rounded-2xl p-4 flex items-center gap-4"
                         style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(255,255,255,0.12);">
                            <i class="fas fa-unlock-keyhole text-amber-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white text-sm font-bold">Zero Unlock Fee</div>
                            <div class="text-slate-400 text-xs mt-0.5">Contact brokers for free</div>
                        </div>
                    </div>

                    <div class="rounded-2xl p-4 flex items-center gap-4"
                         style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); backdrop-filter: blur(8px);">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                             style="background: rgba(255,255,255,0.12);">
                            <i class="fa-brands fa-whatsapp text-green-400 text-lg"></i>
                        </div>
                        <div>
                            <div class="text-white text-sm font-bold">Direct WhatsApp</div>
                            <div class="text-slate-400 text-xs mt-0.5">Instant agent contact</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Floating Search & Filter Bar --}}
    <div class="container mx-auto max-w-6xl px-4" style="position: relative; z-index: 30;">
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

            <div class="agency-input-group md:w-48">
                <i class="fas fa-arrow-down-wide-short" style="color: var(--primary);"></i>
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
    <main class="container mx-auto max-w-6xl px-4 mt-16 sm:mt-20">
        {{-- Section Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-4 border-b border-slate-200/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2.5">
                    <span>Featured Real Estate Partners</span>
                    <span class="agency-count-badge">
                        {{ $agencies->total() }} Listed
                    </span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Contact authorized real estate consultants directly with complete transparency.
                </p>
            </div>

            @if(request()->filled('city'))
                <div class="agency-filter-tag">
                    <i class="fas fa-location-dot text-rose-500"></i>
                    <span>Filtered: {{ request('city') }}</span>
                    <a href="{{ route('agencies.index', request()->except('city')) }}"
                       class="ml-1 opacity-60 hover:opacity-100 transition">
                        <i class="fas fa-xmark"></i>
                    </a>
                </div>
            @endif
        </div>

        @if($agencies->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 items-stretch">
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
                            <div class="mb-1.5">
                                <a href="{{ route('agency.show', $agency) }}" class="agency-name-link truncate max-w-full block" title="{{ $agencyDisplayName }}">
                                    {{ $agencyDisplayName }}
                                </a>
                            </div>

                            {{-- Agent & City details --}}
                            <p class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mb-3.5">
                                <i class="fas fa-user-tie text-[11px]" style="color: var(--primary);"></i>
                                <span class="text-slate-700 font-semibold">{{ $agency->name }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded text-[10px]">
                                    Verified Consultant
                                </span>
                            </p>

                            {{-- License / RERA badge --}}
                            @if($agency->broker_license)
                                <div class="mb-3.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/80 text-[11px] font-bold text-amber-900 inline-flex items-center gap-1.5 max-w-full truncate">
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
                                    <i class="fas fa-shield-heart mr-1" style="color: var(--primary);"></i>Zero brokerage direct contact listing partner
                                </p>
                            @endif

                            {{-- Stats Ribbon --}}
                            <div class="grid grid-cols-2 gap-2.5 mb-4">
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
                        <div class="px-5 pb-5 pt-4 border-t border-slate-100 flex items-center gap-2.5">
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
                <div class="agency-empty-icon">
                    <i class="fas fa-building-circle-xmark"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-1">No Verified Agencies Found</h3>
                <p class="text-xs sm:text-sm text-slate-500 mb-6 leading-relaxed max-w-sm mx-auto">
                    We couldn't find any real estate partners matching your criteria. Try clearing filters or searching for another city.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('agencies.index') }}"
                       class="px-6 py-3 rounded-xl text-white text-xs font-black transition shadow-md inline-flex items-center gap-2"
                       style="background: var(--primary); box-shadow: 0 4px 14px rgba(var(--primary-rgb),0.3);">
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
    <section class="container mx-auto max-w-6xl px-4 mt-20 sm:mt-24">
        <div class="agency-trust-banner">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="agency-trust-icon icon-emerald">
                        <i class="fas fa-badge-check"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-white text-sm mb-1">Government &amp; KYC Verified</h4>
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Every agency is verified with identity checks, broker licenses and verified office details.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="agency-trust-icon icon-primary">
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
                    <div class="agency-trust-icon icon-amber">
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
