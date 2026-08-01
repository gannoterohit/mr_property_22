@extends('layouts.base')

@push('styles')
<style>
    body > nav, body > footer, body > .mobile-app-header, body > .mobile-bottom-nav,
    body > #mobile-app-menu { display: none !important; }
    body { background: #f4f6f9 !important; color: #1f2937; }
    .admin-shell {
        --admin-primary: var(--primary, #4f46e5);
        --admin-secondary: var(--secondary, #10b981);
        --admin-primary-rgb: var(--primary-rgb, 79,70,229);
        --admin-secondary-rgb: var(--secondary-rgb, 16,185,129);
    }
    body > main { padding-top: 0 !important; }
    .admin-shell { min-height: 100vh; display: flex; background: #f4f6f9; }
    .admin-main { min-width: 0; flex: 1; min-height:100vh; overflow:visible; }
    #adminSidebar { display:flex; flex-direction:column; flex:0 0 280px; width:280px; overflow:hidden; min-height:100vh; max-height:100vh; }
    #adminSidebar > nav { padding-bottom:120px !important; }
    #adminSidebar > nav {
        scrollbar-width: thin;
        scrollbar-color: rgba(var(--admin-primary-rgb), .38) transparent;
    }
    #adminSidebar > nav::-webkit-scrollbar {
        width: 4px;
    }
    #adminSidebar > nav::-webkit-scrollbar-track {
        background: transparent;
    }
    #adminSidebar > nav::-webkit-scrollbar-thumb {
        background: rgba(var(--admin-primary-rgb), .34);
        border-radius: 999px;
    }
    #adminSidebar > nav::-webkit-scrollbar-thumb:hover {
        background: rgba(var(--admin-primary-rgb), .58);
    }
    #adminSidebarFooter { position:absolute; left:0; right:0; bottom:0; z-index:5; padding-bottom:max(.625rem, env(safe-area-inset-bottom)); }
    .admin-topbar { height: 64px; background: rgba(255,255,255,.96); border-bottom: 1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; padding:0 24px; position:sticky; top:0; z-index:30; backdrop-filter:blur(10px); }
    .admin-content { padding: 24px 24px 64px; max-width: 1680px; margin: 0 auto; min-height:calc(100vh - 64px); }
    .admin-content > .min-h-screen, .admin-content > .flex.h-screen, .admin-content > .flex.h-\[calc\(100vh-64px\)\] { min-height: auto !important; height: auto !important; background: transparent !important; }
    .admin-content > .h-screen,
    .admin-content > .flex.h-screen,
    .admin-content > [class*="h-[calc(100vh"],
    .admin-content > div > .h-screen,
    .admin-content > div > .flex.h-screen {
        height:auto !important;
        min-height:0 !important;
        max-height:none !important;
        overflow:visible !important;
    }
    .admin-content > .overflow-hidden,
    .admin-content > .flex.overflow-hidden,
    .admin-content > div > .overflow-hidden,
    .admin-content > div > .flex.overflow-hidden,
    .admin-content > div > .flex > .flex-1.overflow-hidden,
    .admin-content > .flex > .flex-1.overflow-hidden {
        overflow:visible !important;
        height:auto !important;
        max-height:none !important;
    }
    .admin-content > div > .flex > .flex-1,
    .admin-content > .flex > .flex-1 {
        min-height:0 !important;
        height:auto !important;
    }
    .admin-content [class*="max-h-[calc(100vh"] {
        max-height:none !important;
        overflow:visible !important;
    }
    .admin-content .container { max-width: none !important; }
    .admin-content .container.mx-auto { padding-left: 0 !important; padding-right: 0 !important; }
    .admin-content .shadow-lg, .admin-content .shadow-xl, .admin-content .shadow-2xl { box-shadow: 0 1px 3px rgba(15,23,42,.08), 0 1px 2px rgba(15,23,42,.04) !important; }
    .admin-content .shadow-md { box-shadow: 0 1px 2px rgba(15,23,42,.06) !important; }
    .admin-content .bg-white { border-color:#e5e7eb; }
    .admin-content table { font-size: .875rem; }
    .admin-content thead { background:#f8fafc !important; }
    .admin-content th { color:#64748b !important; font-size:.72rem; letter-spacing:.04em; text-transform:uppercase; white-space:nowrap; }
    .admin-content td, .admin-content th { padding-top:.8rem !important; padding-bottom:.8rem !important; }
    .admin-content input:not([type=checkbox]):not([type=radio]), .admin-content select, .admin-content textarea { border:1px solid #dbe1ea !important; box-shadow:none !important; }
    .admin-content input:focus, .admin-content select:focus, .admin-content textarea:focus { border-color:#4f46e5 !important; box-shadow:0 0 0 3px rgba(79,70,229,.1) !important; }

    /* Admin design system helpers use settings-driven colors at source level. */
    .admin-content input:focus, .admin-content select:focus, .admin-content textarea:focus {
        border-color:var(--admin-primary) !important;
        box-shadow:0 0 0 3px rgba(var(--admin-primary-rgb),.1) !important;
    }
    body.admin-page #toast-container.toast-top-right {
        top: 1rem;
        right: 1rem;
        left: auto;
    }
    .admin-theme-text { color: var(--admin-primary); }
    .admin-theme-bg { background: var(--admin-primary); color: #fff; }
    .admin-theme-bg:hover { filter: brightness(.94); }
    .admin-theme-soft { background: rgba(var(--admin-primary-rgb), .08); color: var(--admin-primary); }
    .admin-theme-hover-bg:hover { background: var(--admin-primary); color: #fff; }
    .admin-theme-hover-text:hover { color: var(--admin-primary); }
    .admin-switch-track { background: #d1d5db; }
    .peer:checked ~ .admin-switch-track { background: var(--admin-primary); }
    .admin-theme-hover-card:hover {
        border-color: rgba(var(--admin-primary-rgb), .28);
        background: rgba(var(--admin-primary-rgb), .04);
    }
    .admin-theme-hover-card:hover strong { color: var(--admin-primary); }
    .admin-detail-stats {
        display: grid !important;
        grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        gap: 12px;
    }
    .admin-detail-workspace {
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) 360px !important;
        gap: 20px;
        align-items: start;
    }
    .admin-detail-workspace > aside { position: sticky; top: 86px; }
    .admin-plan-page { padding: 24px; }
    .admin-plan-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 20px;
        align-items: start;
    }
    .admin-plan-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .admin-plan-field {
        height: 44px;
        width: 100%;
        border-radius: 12px;
        border-color: #cbd5e1;
        font-size: 14px;
    }
    .admin-plan-field:focus {
        border-color: var(--admin-primary);
        box-shadow: 0 0 0 3px rgba(var(--admin-primary-rgb), .14);
    }
    .admin-theme-link { color: var(--admin-primary); }
    .admin-theme-link:hover { background: rgba(var(--admin-primary-rgb), .07); color: var(--admin-primary); }
    .admin-theme-avatar {
        background: var(--admin-primary);
        color: #fff;
    }
    .admin-sidebar-active {
        background: var(--admin-primary);
        color: #fff;
        box-shadow: 0 8px 18px rgba(var(--admin-primary-rgb), .16);
    }
    .admin-sidebar-icon-active {
        background: var(--admin-primary);
        color: #fff;
    }
    .admin-sidebar-icon-idle {
        background: #fff;
        color: var(--admin-primary);
    }
    .admin-sidebar-group-active {
        border-color: rgba(var(--admin-primary-rgb), .18);
        background: rgba(var(--admin-primary-rgb), .08);
        color: var(--admin-primary);
    }
    .admin-sidebar-submenu {
        border-color: rgba(var(--admin-primary-rgb), .18);
    }
    .admin-sidebar-subitem-active {
        background: #fff;
        color: var(--admin-primary);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        border: 1px solid rgba(var(--admin-primary-rgb), .12);
    }
    .admin-sidebar-subitem-active::before {
        content: "";
        position: absolute;
        left: -14px;
        height: 1.5rem;
        width: 3px;
        border-radius: 999px;
        background: var(--admin-primary);
    }
    .admin-sidebar-active-icon {
        color: var(--admin-primary);
    }
    .admin-sidebar-idle-icon-hover:hover span {
        background: #fff;
        color: var(--admin-primary);
    }

    /* Compact all legacy admin screens without removing their content. */
    .admin-content .text-6xl, .admin-content .text-5xl { font-size:2rem !important; line-height:2.35rem !important; }
    .admin-content .text-4xl { font-size:1.65rem !important; line-height:2rem !important; }
    .admin-content .text-3xl { font-size:1.4rem !important; line-height:1.8rem !important; }
    .admin-content .text-2xl { font-size:1.2rem !important; line-height:1.65rem !important; }
    .admin-content .rounded-3xl { border-radius:1rem !important; }
    .admin-content .p-8 { padding:1.25rem !important; }
    .admin-content .p-6 { padding:1.1rem !important; }
    .admin-content .py-12 { padding-top:2rem !important; padding-bottom:2rem !important; }
    .admin-content .gap-8 { gap:1.25rem !important; }
    .admin-content .mb-8 { margin-bottom:1.25rem !important; }
    .admin-content .mt-8 { margin-top:1.25rem !important; }
    .admin-content .container-fluid { width:100%; }
    .admin-content > div > .flex > .flex-1[class*="p-4"],
    .admin-content > .flex > .flex-1[class*="p-4"] { padding:0 !important; }
    .admin-content button, .admin-content a { transition-duration:150ms !important; }
    .admin-content button[class*="hover:scale"], .admin-content a[class*="hover:scale"] { transform:none !important; }
    @media (min-width: 1024px) {
        #adminSidebar { position:fixed !important; left:0; top:0; bottom:0; height:auto !important; min-height:0 !important; max-height:none !important; transform:translateX(0) !important; visibility:visible !important; }
        .admin-main { margin-left:280px; width:calc(100% - 280px); max-width:calc(100% - 280px); }
        #adminSidebarOpen, #adminSidebarBackdrop { display:none !important; }
    }
    @media (max-width: 1023px) {
        #adminSidebar { position:fixed !important; top:0; left:0; flex-basis:280px; width:280px; }
        .admin-main { margin-left:0; width:100%; }
        .admin-content { padding:16px; }
        .admin-topbar { padding:0 16px 0 64px; }
        body.admin-page #toast-container.toast-top-right {
            top: 1rem;
            right: 1rem;
            left: 1rem;
        }
        .admin-detail-workspace,
        .admin-plan-grid {
            grid-template-columns: 1fr !important;
        }
        .admin-detail-workspace > aside { position: static; }
    }
    @media (max-width: 767px) {
        .admin-detail-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 640px) {
        .admin-plan-page { padding: 16px; }
    }
</style>
@endpush

@section('content')
<div class="admin-shell">
    @include('admin.partials.sidebar')
    <div class="admin-main">
        <header class="admin-topbar">
            <div class="min-w-0">
                <p class="text-[11px] uppercase tracking-[.16em] font-bold text-slate-400">Administration</p>
                <h1 class="text-base font-bold text-slate-800 truncate">@yield('title', 'Admin Panel')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" target="_blank" class="admin-theme-link hidden sm:flex items-center gap-2 px-3 py-2 text-xs font-semibold text-slate-600 rounded-lg transition">
                    <i class="fas fa-external-link-alt"></i> View website
                </a>
                <div class="h-10 pl-1.5 pr-3 rounded-xl border border-slate-200 bg-white flex items-center gap-2.5 shadow-sm">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-lg object-cover ring-1 ring-slate-200">
                    @else
                        <div class="admin-theme-avatar w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shadow-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    @endif
                    <div class="hidden sm:block leading-tight max-w-[140px]">
                        <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Administrator</p>
                    </div>
                </div>
            </div>
        </header>
        @php
            $maintenanceActive = filter_var(\App\Models\Setting::get('maintenance_mode', '0'), FILTER_VALIDATE_BOOLEAN);
            $pausedModules = collect([
                'Registration' => 'registration_enabled',
                'New listings' => 'new_listings_enabled',
                'Payments & unlocks' => 'payments_enabled',
                'Owner panel' => 'owner_panel_enabled',
                'User panel' => 'user_panel_enabled',
            ])->filter(fn ($key) => !filter_var(\App\Models\Setting::get($key, '1'), FILTER_VALIDATE_BOOLEAN))->keys();
        @endphp
        @if($maintenanceActive || $pausedModules->isNotEmpty())
            <div class="border-b {{ $maintenanceActive ? 'border-red-200 bg-red-50 text-red-800' : 'border-amber-200 bg-amber-50 text-amber-800' }} px-5 py-2.5 text-xs font-semibold">
                <div class="mx-auto flex max-w-[1680px] flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span><i class="fas fa-triangle-exclamation mr-2"></i>{{ $maintenanceActive ? 'Global maintenance mode is ON.' : 'Temporarily paused: '.$pausedModules->join(', ').'.' }}</span>
                    <a href="{{ route('admin.maintenance') }}" class="font-extrabold underline underline-offset-2">Manage availability</a>
                </div>
            </div>
        @endif
        <div class="admin-content">
            @yield('admin-content')
        </div>
    </div>
</div>
@endsection
