@extends('layouts.public')

@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
@endphp

@section('title', 'Verified Real Estate Agencies & Agents | ' . $siteName)
@section('description', 'Find and connect directly with top KYC-verified real estate agents and agencies. Browse verified rental flats, rooms, and commercial properties with zero unlock charges.')

@push('styles')
<style>
/* ===================================================
   Agency Directory – 100% Self-Contained Scoped CSS
   Does NOT rely on uncompiled Tailwind utility classes
   =================================================== */

.agencies-directory-page {
    background-color: #f8fafc;
    min-height: 100vh;
    width: 100%;
    overflow-x: hidden;
    box-sizing: border-box;
}

.agency-container {
    width: 100%;
    max-width: 1180px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 1rem;
    padding-right: 1rem;
    box-sizing: border-box;
}

/* ── Hero Section ─────────────────────────────────── */
.agency-hero {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 28%, #0f172a) 0%,
        color-mix(in srgb, var(--primary) 14%, #020617) 55%,
        #020617 100%) !important;
    position: relative;
    overflow: hidden;
    padding-top: 3rem;
    padding-bottom: 5.5rem;
    width: 100%;
    box-sizing: border-box;
}

.agency-hero-glow-1 {
    position: absolute;
    top: -100px;
    right: -80px;
    width: 480px;
    height: 480px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(var(--primary-rgb), 0.32) 0%, transparent 70%);
    pointer-events: none;
}

.agency-hero-glow-2 {
    position: absolute;
    bottom: -100px;
    left: -80px;
    width: 440px;
    height: 440px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
    pointer-events: none;
}

.agency-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #94a3b8;
    margin-bottom: 1.25rem;
}

.agency-breadcrumb a {
    color: #94a3b8;
    text-decoration: none;
    transition: color 0.15s ease;
}

.agency-breadcrumb a:hover {
    color: #ffffff;
}

/* 2-Column Hero Grid */
.agency-hero-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    align-items: center;
    width: 100%;
}

@media (min-width: 992px) {
    .agency-hero-grid {
        grid-template-columns: 1fr 340px;
        gap: 3rem;
    }
}

.agency-hero-left {
    width: 100%;
    min-width: 0;
}

.agency-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 9999px;
    background: rgba(var(--primary-rgb), 0.25);
    border: 1px solid rgba(var(--primary-rgb), 0.5);
    color: #a7f3d0;
    font-size: 11.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}

.agency-hero-badge .pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: agencyPulse 2s infinite;
    flex-shrink: 0;
}

@keyframes agencyPulse {
    0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70%  { transform: scale(1);    box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.agency-hero-title {
    font-size: clamp(26px, 4vw, 44px);
    font-weight: 900;
    line-height: 1.18;
    color: #ffffff !important;
    letter-spacing: -0.025em;
    margin: 0 0 0.85rem 0;
    word-break: break-word;
}

.agency-hero-gradient-text {
    background: linear-gradient(135deg, #a7f3d0 0%, #34d399 50%, #38bdf8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.agency-hero-sub {
    color: #cbd5e1 !important;
    font-size: 14.5px;
    line-height: 1.65;
    max-width: 620px;
    margin: 0 0 1.25rem 0;
}

.agency-stat-chips-wrap {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    padding-top: 1.25rem;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    width: 100%;
}

.agency-stat-chip {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 7px 15px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #e2e8f0;
    font-size: 12.5px;
    white-space: nowrap;
}

.agency-stat-chip-icon {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Hero Right Feature Box ───────────────── */
.hero-trust-box {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 22px;
    padding: 18px;
    backdrop-filter: blur(14px);
    width: 100%;
    box-sizing: border-box;
}

.hero-feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 9px;
    transition: all 0.2s ease;
}

.hero-feature-item:last-child {
    margin-bottom: 0;
}

.hero-feature-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.18);
    transform: translateX(3px);
}

.hero-feature-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* ── Floating Search & Filter Bar ─────────── */
.agency-search-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 12px 16px;
    box-shadow: 0 16px 40px -10px rgba(15, 23, 42, 0.12), 0 2px 10px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    margin-top: -34px;
    position: relative;
    z-index: 25;
    box-sizing: border-box;
}

.agency-search-form {
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 100%;
}

@media (min-width: 768px) {
    .agency-search-form {
        flex-direction: row;
        align-items: center;
    }
}

.agency-input-keyword {
    flex: 1;
    min-width: 0;
    position: relative;
    display: flex;
    align-items: center;
}

.agency-input-city {
    width: 100%;
    position: relative;
    display: flex;
    align-items: center;
}

@media (min-width: 768px) {
    .agency-input-city {
        width: 210px;
        flex-shrink: 0;
    }
}

.agency-input-sort {
    width: 100%;
    position: relative;
    display: flex;
    align-items: center;
}

@media (min-width: 768px) {
    .agency-input-sort {
        width: 190px;
        flex-shrink: 0;
    }
}

.agency-input-icon {
    position: absolute;
    left: 14px;
    pointer-events: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    z-index: 2;
}

.agency-search-input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 13px;
    font-size: 13.5px;
    font-weight: 600;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.agency-search-input:focus {
    background: #ffffff;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.14);
}

.agency-search-btn {
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 85%, #000) 0%,
        var(--primary) 100%);
    color: #ffffff;
    padding: 11px 22px;
    border-radius: 13px;
    font-size: 13.5px;
    font-weight: 800;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
}

.agency-search-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.4);
}

.agency-reset-btn {
    padding: 11px 14px;
    border-radius: 13px;
    background: #f1f5f9;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.15s ease;
}

.agency-reset-btn:hover {
    background: #e2e8f0;
    color: #334155;
}

/* ── Centered Section Header ──────────────── */
.agency-section-header-centered {
    text-align: center;
    max-width: 680px;
    margin: 4.5rem auto 2.25rem auto;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    align-items: center;
    box-sizing: border-box;
}

.agency-section-title {
    font-size: 24px;
    font-weight: 900;
    color: #0f172a;
    margin: 0 0 8px 0;
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.agency-section-desc {
    font-size: 14px;
    color: #64748b;
    margin: 0;
    line-height: 1.6;
    text-align: center;
}

.agency-count-badge {
    font-size: 12px;
    padding: 3px 12px;
    border-radius: 999px;
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    font-weight: 800;
    border: 1px solid rgba(var(--primary-rgb), 0.2);
}

.agency-filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 12px;
    background: rgba(var(--primary-rgb), 0.06);
    border: 1px solid rgba(var(--primary-rgb), 0.2);
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
    margin-top: 10px;
}

/* ── Centered Cards Grid ──────────────────── */
.agencies-centered-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 24px;
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
}

.agency-card-item {
    width: 100%;
    max-width: 380px;
    flex: 0 1 380px;
    display: flex;
    box-sizing: border-box;
}

.agency-card {
    background: #ffffff;
    border-radius: 22px;
    border: 1.5px solid #e2e8f0;
    overflow: hidden;
    box-shadow: 0 4px 18px -2px rgba(15, 23, 42, 0.06);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    width: 100%;
    box-sizing: border-box;
}

.agency-card:hover {
    transform: translateY(-4px);
    border-color: rgba(var(--primary-rgb), 0.35);
    box-shadow: 0 18px 36px -8px rgba(var(--primary-rgb), 0.16);
}

.agency-card-banner {
    height: 92px;
    background: linear-gradient(135deg,
        color-mix(in srgb, var(--primary) 35%, #0f172a) 0%,
        color-mix(in srgb, var(--primary) 50%, #1e293b) 100%);
    position: relative;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 12px 16px;
    box-sizing: border-box;
}

.agency-card-avatar-wrap {
    position: absolute;
    left: 18px;
    bottom: -32px;
    z-index: 5;
}

.agency-card-avatar {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    border: 3.5px solid #ffffff;
    background: linear-gradient(135deg, var(--primary) 0%, color-mix(in srgb, var(--primary) 65%, #0f172a) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 26px;
    font-weight: 900;
    box-shadow: 0 8px 18px -3px rgba(15, 23, 42, 0.25);
    overflow: hidden;
    position: relative;
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
    border: 2px solid #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.agency-card-body {
    padding: 42px 20px 18px;
    flex-grow: 1;
}

.agency-name-link {
    font-size: 19px;
    font-weight: 850;
    color: #0f172a;
    text-decoration: none;
    transition: color 0.15s ease;
    display: inline-block;
    line-height: 1.3;
}

.agency-name-link:hover {
    color: var(--primary);
}

.agency-stat-badge {
    padding: 9px 12px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
}

.agency-btn-primary {
    background: var(--primary);
    color: #ffffff;
    border-radius: 13px;
    padding: 11px 18px;
    font-size: 13.5px;
    font-weight: 800;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.18s ease;
    flex: 1;
    box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.25);
}

.agency-btn-primary:hover {
    background: color-mix(in srgb, var(--primary) 85%, #000);
    color: #ffffff;
    transform: translateY(-1px);
}

.agency-btn-whatsapp {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    background: #25D366;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.18s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(37, 211, 102, 0.28);
}

.agency-btn-whatsapp:hover {
    background: #1eb956;
    color: #ffffff;
    transform: translateY(-1px);
}

.agency-btn-phone {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    background: #f1f5f9;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.18s ease;
    flex-shrink: 0;
    border: 1.5px solid #e2e8f0;
}

.agency-btn-phone:hover {
    background: rgba(var(--primary-rgb), 0.08);
    color: var(--primary);
    border-color: rgba(var(--primary-rgb), 0.25);
    transform: translateY(-1px);
}

/* ── Bottom Trust Section ─────────────────── */
.agency-trust-section {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 26px;
    padding: 2.75rem 2rem;
    box-shadow: 0 8px 25px -5px rgba(15, 23, 42, 0.04);
    position: relative;
    overflow: hidden;
    margin-top: 5rem;
    box-sizing: border-box;
}

.agency-trust-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #10b981 0%, var(--primary) 50%, #38bdf8 100%);
}

.trust-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    width: 100%;
}

@media (min-width: 768px) {
    .trust-cards-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.trust-feature-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 22px;
    transition: all 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
}

.trust-feature-card:hover {
    background: #ffffff;
    border-color: rgba(var(--primary-rgb), 0.35);
    box-shadow: 0 10px 24px -5px rgba(15, 23, 42, 0.07);
    transform: translateY(-2px);
}

.agency-trust-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-bottom: 1rem;
}

.icon-box-emerald {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.icon-box-amber {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}

.icon-box-blue {
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #bae6fd;
}
</style>
@endpush

@section('content')
<div class="agencies-directory-page pb-20">

    {{-- Hero Section --}}
    <section class="agency-hero">
        <div class="agency-hero-glow-1"></div>
        <div class="agency-hero-glow-2"></div>

        <div class="agency-container" style="position: relative; z-index: 10;">
            {{-- Breadcrumb --}}
            <nav class="agency-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <span style="color: #ffffff; font-weight: 700;">Verified Agencies</span>
            </nav>

            {{-- 2-Column Responsive CSS Grid --}}
            <div class="agency-hero-grid">
                {{-- Left: Heading, Description & Stat Chips --}}
                <div class="agency-hero-left">
                    <div class="agency-hero-badge">
                        <span class="pulse-dot"></span>
                        <svg style="width: 14px; height: 14px; color: #34d399; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                        </svg>
                        <span>100% KYC &amp; License Verified Real Estate Directory</span>
                    </div>

                    <h1 class="agency-hero-title">
                        Verified Real Estate <span class="agency-hero-gradient-text">Agencies &amp; Consultants</span>
                    </h1>

                    <p class="agency-hero-sub">
                        Connect directly with trusted property consultants and authorized real estate agencies. Browse verified rental rooms, flats, and commercial spaces with <strong>zero unlock fees</strong> and direct WhatsApp contact.
                    </p>

                    {{-- Stat Chips --}}
                    <div class="agency-stat-chips-wrap">
                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
                                <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5h-.75V3.75a.75.75 0 000-1.5H4.5zm3 4.5a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm-6 6a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm-6 6a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span><strong style="color: #ffffff; font-size: 13.5px;">{{ $totalAgenciesCount }}</strong> Verified Agencies</span>
                        </div>

                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8;">
                                <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                                    <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
                                </svg>
                            </span>
                            <span><strong style="color: #ffffff; font-size: 13.5px;">{{ $totalBrokerProperties }}</strong> Active Properties</span>
                        </div>

                        <div class="agency-stat-chip">
                            <span class="agency-stat-chip-icon" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
                                <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.444 1.223z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span>Direct WhatsApp Connect</span>
                        </div>
                    </div>
                </div>

                {{-- Right: High-Trust Showcase Box --}}
                <div>
                    <div class="hero-trust-box">
                        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #a7f3d0; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <svg style="width: 14px; height: 14px; color: #34d399;" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            <span>Verified Agency Guarantee</span>
                        </div>

                        {{-- Feature 1: KYC & License Verified --}}
                        <div class="hero-feature-item">
                            <div class="hero-feature-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);">
                                <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                    <path d="m9 12 2 2 4-4"/>
                                </svg>
                            </div>
                            <div>
                                <div style="color: #ffffff; font-size: 13.5px; font-weight: 700;">KYC &amp; License Verified</div>
                                <div style="color: #94a3b8; font-size: 11.5px; margin-top: 1px;">Every agency is ID-checked</div>
                            </div>
                        </div>

                        {{-- Feature 2: Zero Unlock Fee --}}
                        <div class="hero-feature-item">
                            <div class="hero-feature-icon" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3);">
                                <svg style="width: 20px; height: 20px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                </svg>
                            </div>
                            <div>
                                <div style="color: #ffffff; font-size: 13.5px; font-weight: 700;">Zero Unlock Fee</div>
                                <div style="color: #94a3b8; font-size: 11.5px; margin-top: 1px;">Contact brokers for free</div>
                            </div>
                        </div>

                        {{-- Feature 3: Direct WhatsApp --}}
                        <div class="hero-feature-item">
                            <div class="hero-feature-icon" style="background: rgba(37, 211, 102, 0.2); color: #25D366; border: 1px solid rgba(37, 211, 102, 0.3);">
                                <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <div>
                                <div style="color: #ffffff; font-size: 13.5px; font-weight: 700;">Direct WhatsApp</div>
                                <div style="color: #94a3b8; font-size: 11.5px; margin-top: 1px;">Instant agent contact</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Floating Search & Filter Bar --}}
    <div class="agency-container">
        <div class="agency-search-card">
            <form method="GET" action="{{ route('agencies.index') }}" class="agency-search-form">
                {{-- Keyword Search --}}
                <div class="agency-input-keyword">
                    <span class="agency-input-icon">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Search agency name, agent, locality or license..."
                           class="agency-search-input">
                </div>

                {{-- City Selector --}}
                <div class="agency-input-city">
                    <span class="agency-input-icon" style="color: #f43f5e;">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <select name="city" class="agency-search-input" style="cursor: pointer; appearance: none; padding-right: 32px;">
                        <option value="">All Cities</option>
                        @foreach($availableCities as $c)
                            <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                    <span style="position: absolute; right: 12px; pointer-events: none; color: #94a3b8; display: flex;">
                        <svg style="width: 14px; height: 14px;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>

                {{-- Sort Selector --}}
                <div class="agency-input-sort">
                    <span class="agency-input-icon" style="color: var(--primary);">
                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="6" x2="16" y2="6"></line>
                            <line x1="4" y1="12" x2="12" y2="12"></line>
                            <line x1="4" y1="18" x2="8" y2="18"></line>
                            <polyline points="15 15 18 18 21 15"></polyline>
                            <line x1="18" y1="9" x2="18" y2="18"></line>
                        </svg>
                    </span>
                    <select name="sort_by" class="agency-search-input" style="cursor: pointer; appearance: none; padding-right: 32px;">
                        <option value="popular" {{ request('sort_by') === 'popular' ? 'selected' : '' }}>Most Active</option>
                        <option value="properties" {{ request('sort_by') === 'properties' ? 'selected' : '' }}>Most Properties</option>
                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Agency Name (A-Z)</option>
                    </select>
                    <span style="position: absolute; right: 12px; pointer-events: none; color: #94a3b8; display: flex;">
                        <svg style="width: 14px; height: 14px;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="submit" class="agency-search-btn">
                        <svg style="width: 15px; height: 15px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Find Agencies</span>
                    </button>

                    @if(request()->filled('q') || request()->filled('city') || request()->filled('sort_by'))
                        <a href="{{ route('agencies.index') }}" class="agency-reset-btn" title="Clear Filters">
                            <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Agencies Listing Section --}}
    <main class="agency-container">
        {{-- Centered Section Header --}}
        <div class="agency-section-header-centered">
            <h2 class="agency-section-title">
                <span>Featured Real Estate Partners</span>
                <span class="agency-count-badge">
                    {{ $agencies->total() }} {{ Str::plural('Partner', $agencies->total()) }} Listed
                </span>
            </h2>
            <p class="agency-section-desc">
                Connect directly with authorized real estate consultants with complete transparency and zero unlock fees.
            </p>

            @if(request()->filled('city'))
                <div class="agency-filter-pill">
                    <svg style="width: 13px; height: 13px; color: #f43f5e;" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    <span>Filtered: {{ request('city') }}</span>
                    <a href="{{ route('agencies.index', request()->except('city')) }}"
                       style="color: inherit; opacity: 0.6; display: flex; text-decoration: none;" aria-label="Remove filter">
                        <svg style="width: 13px; height: 13px;" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"/>
                        </svg>
                    </a>
                </div>
            @endif
        </div>

        @if($agencies->count() > 0)
            {{-- Centered Flexible Grid --}}
            <div class="agencies-centered-grid">
                @foreach($agencies as $agency)
                    @php
                        $agencyDisplayName = $agency->agency_name ?: ($agency->name . ' Real Estate');
                        $operatingCities = $agency->rooms ? $agency->rooms->pluck('city')->filter()->unique()->values() : collect();
                        $primaryCity = $operatingCities->first() ?: ($agency->city ?: '');
                        $rawPhone = trim((string) ($agency->phone ?? ''));
                        $digits = preg_replace('/\D+/', '', $rawPhone);
                        if (strlen($digits) === 10) $digits = '91' . $digits;
                        elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) $digits = '91' . substr($digits, 1);
                        $waMsg = "Hello {$agencyDisplayName}! Maine aapki agency profile {$siteName} par dekhi hai. Mujhe rental properties ke baare mein jaankari chahiye.";
                        $waLink = !empty($digits) ? "https://wa.me/{$digits}?text=" . rawurlencode($waMsg) : null;
                    @endphp

                    <div class="agency-card-item">
                        <div class="agency-card">
                            <div>
                                {{-- Top Header Banner --}}
                                <div class="agency-card-banner">
                                    {{-- Left: KYC Badge --}}
                                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 999px; background: rgba(0, 0, 0, 0.35); backdrop-filter: blur(8px); color: #6ee7b7; font-size: 11px; font-weight: 700; border: 1px solid rgba(255, 255, 255, 0.15);">
                                        <svg style="width: 12px; height: 12px; color: #34d399;" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                        </svg>
                                        <span>KYC Verified</span>
                                    </span>

                                    {{-- Right: Primary City Tag --}}
                                    @if($primaryCity)
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 999px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); color: #ffffff; font-size: 11px; font-weight: 700; border: 1px solid rgba(255, 255, 255, 0.25);">
                                            <svg style="width: 12px; height: 12px; color: #fda4af;" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $primaryCity }}</span>
                                        </span>
                                    @endif

                                    {{-- Avatar overlapping banner --}}
                                    <div class="agency-card-avatar-wrap">
                                        <div class="agency-card-avatar">
                                            @if($agency->avatar)
                                                <img src="{{ asset('storage/' . $agency->avatar) }}" alt="{{ $agencyDisplayName }}">
                                            @else
                                                <span>{{ strtoupper(substr($agencyDisplayName, 0, 1)) }}</span>
                                            @endif
                                            <span class="agency-card-avatar-badge" title="Verified Broker">
                                                <svg style="width: 12px; height: 12px;" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="agency-card-body">
                                    {{-- Agency Name --}}
                                    <div style="margin-bottom: 6px;">
                                        <a href="{{ route('agency.show', $agency) }}" class="agency-name-link" title="{{ $agencyDisplayName }}">
                                            {{ $agencyDisplayName }}
                                        </a>
                                    </div>

                                    {{-- Agent Details --}}
                                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 12px;">
                                        <span style="color: #1e293b; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg style="width: 13px; height: 13px; color: #94a3b8;" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
                                            </svg>
                                            <span>{{ $agency->name }}</span>
                                        </span>
                                        <span style="color: #cbd5e1;">·</span>
                                        <span style="color: #047857; font-weight: 700; background: #ecfdf5; padding: 2px 8px; border-radius: 999px; font-size: 10.5px; border: 1px solid #a7f3d0; display: inline-flex; align-items: center; gap: 3px;">
                                            <svg style="width: 10px; height: 10px;" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Authorized Partner</span>
                                        </span>
                                    </div>

                                    {{-- License / RERA badge if available --}}
                                    @if($agency->broker_license)
                                        <div style="margin-bottom: 12px; padding: 5px 10px; border-radius: 10px; background: #fffbeb; border: 1px solid #fef3c7; font-size: 11px; font-weight: 700; color: #92400e; display: inline-flex; align-items: center; gap: 6px;">
                                            <svg style="width: 14px; height: 14px; color: #d97706; flex-shrink: 0;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                                <path d="M7 15h.01M17 15h.01M7 9h10"/>
                                            </svg>
                                            <span>Lic / RERA: {{ $agency->broker_license }}</span>
                                        </div>
                                    @endif

                                    {{-- Office Address or tagline --}}
                                    @if($agency->agency_address)
                                        <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0 0 14px 0; display: flex; align-items: flex-start; gap: 6px;" title="{{ $agency->agency_address }}">
                                            <svg style="width: 14px; height: 14px; color: #94a3b8; margin-top: 2px; flex-shrink: 0;" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5h-.75V3.75a.75.75 0 000-1.5H4.5zm3 4.5a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm-6 6a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm-6 6a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5zm6 0a.75.75 0 01.75-.75h1.5a.75.75 0 01.75.75v1.5a.75.75 0 01-.75.75h-1.5a.75.75 0 01-.75-.75v-1.5z" clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $agency->agency_address }}</span>
                                        </p>
                                    @else
                                        <p style="font-size: 12px; color: #64748b; margin: 0 0 14px 0; display: flex; align-items: center; gap: 6px;">
                                            <svg style="width: 14px; height: 14px; color: #10b981; flex-shrink: 0;" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Zero Brokerage · Direct Owner Connection</span>
                                        </p>
                                    @endif

                                    {{-- Stats Ribbon --}}
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 6px;">
                                        <div class="agency-stat-badge">
                                            <span style="font-size: 10px; color: #94a3b8; display: block; font-weight: 700; text-transform: uppercase;">Portfolio</span>
                                            <span style="font-weight: 900; color: #0f172a; font-size: 13.5px; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                                <svg style="width: 13px; height: 13px; color: #10b981;" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                                                    <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.432z" />
                                                </svg>
                                                {{ $agency->active_rooms_count }} {{ Str::plural('Listing', $agency->active_rooms_count) }}
                                            </span>
                                        </div>

                                        <div class="agency-stat-badge">
                                            <span style="font-size: 10px; color: #94a3b8; display: block; font-weight: 700; text-transform: uppercase;">Trust Status</span>
                                            <span style="font-weight: 900; color: #059669; font-size: 13.5px; display: flex; align-items: center; gap: 5px; margin-top: 2px;">
                                                <svg style="width: 13px; height: 13px; color: #10b981;" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                                </svg>
                                                100% Verified
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Card Actions Footer --}}
                            <div style="padding: 14px 18px 18px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 8px;">
                                <a href="{{ route('agency.show', $agency) }}" class="agency-btn-primary">
                                    <span>View Portfolio</span>
                                    <svg style="width: 13px; height: 13px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </a>

                                @if($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="agency-btn-whatsapp" title="Chat on WhatsApp">
                                        <svg style="width: 20px; height: 20px; fill: currentColor;" viewBox="0 0 24 24">
                                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                        </svg>
                                    </a>
                                @endif

                                @if(!empty($rawPhone))
                                    <a href="tel:{{ $rawPhone }}" class="agency-btn-phone" title="Call Agent">
                                        <svg style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($agencies->hasPages())
                <div style="margin-top: 3rem; display: flex; justify-content: center;">
                    {{ $agencies->links() }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div style="background: #ffffff; border-radius: 24px; padding: 3.5rem 2rem; text-align: center; border: 1px solid #e2e8f0; max-width: 500px; margin: 2rem auto; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <div style="width: 72px; height: 72px; border-radius: 20px; background: rgba(var(--primary-rgb), 0.08); color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                    <svg style="width: 36px; height: 36px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"/>
                        <path d="M19 21v-4"/>
                        <path d="M19 13v-2"/>
                        <path d="M19 7V4a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v17"/>
                        <line x1="9" y1="9" x2="9" y2="9.01"/>
                        <line x1="15" y1="9" x2="15" y2="9.01"/>
                        <line x1="9" y1="13" x2="9" y2="13.01"/>
                        <line x1="15" y1="13" x2="15" y2="13.01"/>
                    </svg>
                </div>
                <h3 style="font-size: 18px; font-weight: 900; color: #0f172a; margin: 0 0 6px 0;">No Verified Agencies Found</h3>
                <p style="font-size: 13px; color: #64748b; margin: 0 0 1.5rem 0; line-height: 1.6;">
                    We couldn't find any real estate partners matching your criteria. Try clearing filters or searching for another city.
                </p>
                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 10px;">
                    <a href="{{ route('agencies.index') }}"
                       style="padding: 10px 20px; border-radius: 12px; background: var(--primary); color: #ffffff; font-size: 12.5px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <svg style="width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                        <span>Reset All Filters</span>
                    </a>
                    <a href="{{ route('rooms.index') }}" style="padding: 10px 20px; border-radius: 12px; background: #f1f5f9; color: #334155; font-size: 12.5px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <svg style="width: 14px; height: 14px;" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                        </svg>
                        <span>Browse All Rooms</span>
                    </a>
                </div>
            </div>
        @endif
    </main>

    {{-- Bottom Trust Section: Why Choose Verified Agencies --}}
    <section class="agency-container">
        <div class="agency-trust-section">
            <div style="text-align: center; max-width: 600px; margin: 0 auto 2.5rem;">
                <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 800; border: 1px solid #a7f3d0; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px;">
                    <svg style="width: 13px; height: 13px; color: #059669;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    <span>Direct &amp; Safe Connection</span>
                </span>
                <h3 style="font-size: 26px; font-weight: 900; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.02em;">Why Choose Verified Agencies?</h3>
                <p style="font-size: 13.5px; color: #64748b; margin: 0; line-height: 1.5;">Zero contact unlock charges, verified broker licenses, and direct negotiation with authorized partners.</p>
            </div>

            <div class="trust-cards-grid">
                {{-- 1. Government & KYC Verified --}}
                <div class="trust-feature-card">
                    <div class="agency-trust-icon-box icon-box-emerald">
                        <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <h4 style="font-weight: 800; color: #0f172a; font-size: 15.5px; margin: 0 0 6px 0;">
                        Government &amp; KYC Verified
                    </h4>
                    <p style="color: #64748b; font-size: 13px; line-height: 1.6; margin: 0;">
                        Every agency is verified with identity checks, broker licenses, and verified office details for your safety.
                    </p>
                </div>

                {{-- 2. Zero Contact Unlock Charge --}}
                <div class="trust-feature-card">
                    <div class="agency-trust-icon-box icon-box-amber">
                        <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                        </svg>
                    </div>
                    <h4 style="font-weight: 800; color: #0f172a; font-size: 15.5px; margin: 0 0 6px 0;">
                        Zero Contact Unlock Charge
                    </h4>
                    <p style="color: #64748b; font-size: 13px; line-height: 1.6; margin: 0;">
                        Connect directly via WhatsApp or phone call with brokers. No coin deduction or paywall to view phone numbers.
                    </p>
                </div>

                {{-- 3. Direct Owner / Broker Deals --}}
                <div class="trust-feature-card">
                    <div class="agency-trust-icon-box icon-box-blue">
                        <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.42 4.58a5.4 5.4 0 0 0-7.65 0l-.77.78-.77-.78a5.4 5.4 0 0 0-7.65 0C1.46 6.7 1.33 10.28 4 13l8 8 8-8c2.67-2.72 2.54-6.3.42-8.42z"/>
                        </svg>
                    </div>
                    <h4 style="font-weight: 800; color: #0f172a; font-size: 15.5px; margin: 0 0 6px 0;">
                        Direct Owner &amp; Broker Deals
                    </h4>
                    <p style="color: #64748b; font-size: 13px; line-height: 1.6; margin: 0;">
                        Browse their complete active portfolio of verified rooms, apartments, villas, and commercial spaces.
                    </p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
