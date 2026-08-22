@php
    $broker = Auth::user();
    $brokerItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'fa-chart-pie', 'href' => route('agent.dashboard'), 'match' => 'agent.dashboard'],
        ['key' => 'properties', 'label' => 'My Properties', 'icon' => 'fa-building', 'href' => route('agent.properties'), 'match' => 'agent.properties'],
        ['key' => 'subscription', 'label' => 'Subscription', 'icon' => 'fa-tags', 'href' => route('agent.subscription'), 'match' => 'agent.subscription'],
        ['key' => 'payments', 'label' => 'Payments', 'icon' => 'fa-credit-card', 'href' => route('agent.payments'), 'match' => 'agent.payments'],
        ['key' => 'transactions', 'label' => 'Transactions', 'icon' => 'fa-receipt', 'href' => route('agent.transactions'), 'match' => 'agent.transactions'],
        ['key' => 'profile', 'label' => 'Profile Settings', 'icon' => 'fa-user-gear', 'href' => route('agent.profile'), 'match' => 'agent.profile'],
    ];
    $brokerLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
@endphp

@once
<link rel="stylesheet" href="{{ asset('css/owner-sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}">
@endonce

<aside class="owner-sidebar hidden lg:flex bg-white border-r border-slate-200 flex-col sticky top-0 h-screen">
    <div class="px-5 py-5 border-b border-slate-100">
        <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-3">
            @if($brokerLogo)
                <img src="{{ asset('storage/' . $brokerLogo) }}" alt="Agent panel" class="w-10 h-10 rounded-xl object-contain border border-slate-200 bg-white p-1">
            @else
                <span class="w-10 h-10 rounded-xl owner-theme-bg flex items-center justify-center text-white">
                    <i class="fas fa-user-tie"></i>
                </span>
            @endif
            <span class="min-w-0">
                <strong class="block text-sm text-slate-900 truncate">Agent Workspace</strong>
                <small class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Manage listings</small>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto p-3 space-y-1.5" aria-label="Agent navigation">
        @foreach($brokerItems as $item)
            @php $isActive = request()->routeIs($item['match']); @endphp
            <a href="{{ $item['href'] }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $isActive ? 'owner-sidebar-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
               @if($isActive) aria-current="page" @endif>
                <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isActive ? 'owner-sidebar-icon-active' : 'bg-slate-100 text-slate-500' }}">
                    <i class="fas {{ $item['icon'] }} text-xs"></i>
                </span>
                <span>{{ $item['label'] }}</span>
                @if($isActive)<i class="fas fa-chevron-right text-[9px] ml-auto"></i>@endif
            </a>
        @endforeach
    </nav>

    <div class="p-3 border-t border-slate-100">
        <div class="flex items-center gap-3 px-3 py-2 mb-1">
            <img src="{{ $broker?->avatar ? asset('storage/'.$broker->avatar) : asset('assets/images/default-avatar.svg') }}" width="200" height="200" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'" alt="Agent profile" class="w-9 h-9 rounded-full border border-slate-200 owner-theme-soft object-cover">
            <span class="min-w-0">
                <strong class="block text-xs text-slate-800 truncate">{{ $broker?->name }}</strong>
                <small class="block text-[10px] text-slate-400 truncate">Agent</small>
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
