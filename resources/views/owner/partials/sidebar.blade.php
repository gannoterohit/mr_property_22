@php
    $activeOwnerSection = $active ?? '';
    $owner = Auth::user();
    $ownerItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-chart-pie', 'href' => route('owner.dashboard')],
    ['key' => 'create', 'label' => 'Add New Property', 'icon' => 'fa-square-plus', 'href' => route('rooms.create')],
    ['key' => 'rooms', 'label' => 'My Properties', 'icon' => 'fa-building', 'href' => route('owner.rooms')],
        ['key' => 'enquiries', 'label' => 'Enquiries', 'icon' => 'fa-address-card', 'href' => route('owner.enquiries')],
        ['key' => 'plans', 'label' => 'Plans & Pricing', 'icon' => 'fa-tags', 'href' => route('plans')],
    ];

    if (\App\Models\Setting::isEnabled('wallet_enabled', true)) {
        $ownerItems[] = ['key' => 'wallet', 'label' => 'My Wallet', 'icon' => 'fa-wallet', 'href' => route('wallet')];
    }

    if (\App\Models\Setting::isEnabled('referral_enabled', true)) {
        $ownerItems[] = ['key' => 'referral', 'label' => 'Refer & Earn', 'icon' => 'fa-gift', 'href' => route('referral.index')];
    }

    $ownerItems[] = ['key' => 'complaints', 'label' => 'My Complaints', 'icon' => 'fa-shield-halved', 'href' => route('complaints.index')];
    $ownerItems[] = ['key' => 'profile', 'label' => 'Profile Settings', 'icon' => 'fa-user-gear', 'href' => route('profile.edit')];
    $ownerLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
@endphp

@once
<style>
    .owner-workspace {
        display: flex !important;
        flex-direction: row !important;
        align-items: stretch !important;
        width: 100% !important;
        min-width: 0;
        background: #f8fafc;
    }
    .owner-workspace > .owner-sidebar {
        display: flex !important;
        flex: 0 0 16rem !important;
        width: 16rem !important;
        max-width: 16rem !important;
        position: fixed !important;
        top: 4rem !important;
        bottom: 0 !important;
        left: 0 !important;
        z-index: 35 !important;
        height: calc(100vh - 4rem) !important;
        min-height: 0 !important;
        max-height: calc(100vh - 4rem) !important;
        overflow: hidden !important;
    }
    .owner-workspace .owner-sidebar nav { display: block !important; flex: 1 1 auto; min-height: 0; overflow: hidden !important; }
    .owner-workspace .owner-sidebar nav > a { display: flex !important; width: 100%; }
    .owner-workspace > .owner-sidebar > div:first-child,
    .owner-workspace > .owner-sidebar > div:last-child { flex: 0 0 auto; }
    .owner-workspace > main {
        display: block;
        flex: 1 1 auto !important;
        width: calc(100% - 16rem);
        min-width: 0;
        overflow: visible;
        margin-left: 16rem !important;
    }
    .owner-workspace:has(> .owner-form-shell) { height: auto !important; min-height: 100vh; overflow: visible !important; align-items: flex-start !important; }
    .owner-workspace > .owner-form-shell { display: flex !important; align-items: flex-start !important; width: 100% !important; min-width: 0; height: auto !important; min-height: 100vh; overflow: visible !important; }
    .owner-workspace > .owner-form-shell > .owner-sidebar { display: flex !important; flex: 0 0 16rem !important; width: 16rem !important; max-width: 16rem !important; position: fixed !important; top: 4rem !important; bottom: 0 !important; left: 0 !important; z-index: 35 !important; height: calc(100vh - 4rem) !important; }
    .owner-workspace > .owner-form-shell > main { display: block; flex: 1 1 auto !important; width: calc(100% - 16rem); margin-left: 16rem !important; min-width: 0; height: auto !important; max-height: none !important; overflow: visible !important; padding-bottom: 5rem !important; }
    .owner-workspace .owner-page-header {
        background: #fff !important;
        color: #0f172a !important;
        border-bottom: 1px solid #e2e8f0;
        padding: 1.25rem 1.5rem !important;
    }
    .owner-workspace .owner-page-header p { color: #64748b !important; }
    .owner-workspace .owner-form-page-header { padding: .875rem 1.5rem !important; }
    .owner-workspace .owner-form-page-header h1 { font-size: 1.25rem !important; line-height: 1.75rem !important; margin-bottom: .125rem !important; }
    .owner-workspace .owner-form-page-header p { font-size: .8125rem !important; line-height: 1.25rem !important; margin: 0 !important; }
    .owner-workspace .rounded-\[2rem\],
    .owner-workspace .rounded-\[2\.5rem\] { border-radius: 1rem !important; }
    .owner-workspace .rounded-3xl { border-radius: .875rem !important; }
    .owner-workspace .p-8 { padding: 1.25rem !important; }
    .owner-workspace .p-6 { padding: 1rem !important; }
    .owner-workspace .p-12 { padding: 2rem !important; }
    .owner-workspace .mb-8 { margin-bottom: 1.25rem !important; }
    .owner-workspace .gap-8 { gap: 1.25rem !important; }
    .owner-workspace .space-y-8 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.25rem !important; }
    .owner-workspace .text-3xl { font-size: 1.5rem !important; line-height: 2rem !important; }
    .owner-workspace .w-24.h-24 { width: 4rem !important; height: 4rem !important; }
    .owner-workspace input,
    .owner-workspace select,
    .owner-workspace textarea { border: 1px solid #e2e8f0 !important; border-radius: .75rem !important; }
    .owner-workspace input:focus,
    .owner-workspace select:focus,
    .owner-workspace textarea:focus { background: #fff !important; }
    .owner-workspace main button[type="submit"] { border-radius: .75rem !important; padding-top: .8rem !important; padding-bottom: .8rem !important; }
    .owner-room-form-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 1.25rem;
    }
    .owner-room-form-grid > * { margin-top: 0 !important; min-width: 0; }
    .owner-room-form-grid > .owner-form-wide { grid-column: 1 / -1; }
    .owner-room-form-grid label[class*="p-10"],
    .owner-room-form-grid label[class*="p-8"] { padding: 1.5rem !important; }
    .owner-room-form-grid .h-80 { height: 15rem !important; }
    .owner-room-form-grid .min-h-\[300px\] { min-height: 15rem !important; }
    .owner-room-form-grid input[class*="py-4"],
    .owner-room-form-grid select[class*="py-4"],
    .owner-room-form-grid textarea[class*="py-4"] { padding-top: .7rem !important; padding-bottom: .7rem !important; }
    .owner-room-form-grid h3 { margin-bottom: 1rem !important; padding-bottom: .75rem !important; }
    .owner-room-form-grid > div { align-self: start; border-color: #e2e8f0 !important; box-shadow: 0 1px 3px rgba(15, 23, 42, .05) !important; }
    .owner-room-form-grid > div:hover { border-color: #cbd5e1 !important; }
    .owner-room-form-grid h3 { color: #0f172a !important; font-size: 1.05rem !important; letter-spacing: -.01em; }
    .owner-room-form-grid label { line-height: 1.35; }
    .owner-room-form-grid input:not([type="checkbox"]):not([type="radio"]),
    .owner-room-form-grid select,
    .owner-room-form-grid textarea { background: #f8fafc !important; border: 1px solid #e2e8f0 !important; color: #0f172a !important; font-weight: 500 !important; }
    .owner-room-form-grid input:focus,
    .owner-room-form-grid select:focus,
    .owner-room-form-grid textarea:focus { border-color: #6366f1 !important; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12) !important; }
    @media (max-width: 1023px) {
        .owner-workspace { display: block !important; }
        .owner-workspace > .owner-sidebar { display: none !important; }
        .owner-workspace > main { width: 100% !important; margin-left: 0 !important; }
        .owner-workspace > .owner-form-shell { display: block !important; }
        .owner-workspace > .owner-form-shell > .owner-sidebar { display: none !important; }
        .owner-workspace > .owner-form-shell > main { width: 100% !important; margin-left: 0 !important; }
        .owner-workspace .p-8,
        .owner-workspace .p-6 { padding: 1rem !important; }
        .owner-room-form-grid { display: block; }
        .owner-room-form-grid > * + * { margin-top: 1rem !important; }
    }
</style>
@endonce

<aside class="owner-sidebar hidden lg:flex bg-white border-r border-slate-200 flex-col sticky top-16 h-[calc(100vh-4rem)]">
    <div class="px-5 py-5 border-b border-slate-100">
        <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-3">
            @if($ownerLogo)
                <img src="{{ asset('storage/' . $ownerLogo) }}" alt="Owner panel" class="w-10 h-10 rounded-xl object-contain border border-slate-200 bg-white p-1">
            @else
                <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                    <i class="fas fa-house"></i>
                </span>
            @endif
            <span class="min-w-0">
                <strong class="block text-sm text-slate-900 truncate">Owner Workspace</strong>
                <small class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Manage properties</small>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1.5" aria-label="Owner navigation">
        @foreach($ownerItems as $item)
            @php $isActive = $activeOwnerSection === $item['key']; @endphp
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $isActive ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
               @if($isActive) aria-current="page" @endif>
                <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isActive ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                    <i class="fas {{ $item['icon'] }} text-xs"></i>
                </span>
                <span>{{ $item['label'] }}</span>
                @if($isActive)<i class="fas fa-chevron-right text-[9px] ml-auto"></i>@endif
            </a>
        @endforeach
    </nav>

    <div class="p-3 border-t border-slate-100">
        <div class="flex items-center gap-3 px-3 py-2 mb-1">
            <img src="{{ $owner?->avatar ? asset('storage/'.$owner->avatar) : asset('assets/images/default-avatar.svg') }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'" alt="Owner profile" class="w-9 h-9 rounded-full border border-slate-200 bg-indigo-50 object-cover">
            <span class="min-w-0">
                <strong class="block text-xs text-slate-800 truncate">{{ $owner?->name }}</strong>
                <small class="block text-[10px] text-slate-400 truncate">Property Owner</small>
            </span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                <span class="w-8 text-center"><i class="fas fa-right-from-bracket"></i></span>
                Logout
            </button>
        </form>
    </div>
</aside>
