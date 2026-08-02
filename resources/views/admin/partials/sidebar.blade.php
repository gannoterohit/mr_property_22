@php
    $adminSiteName = \App\Models\Setting::get('website_name', config('app.name', 'ApnaNest'));
    $adminLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
    $navGroups = [
        'property' => ['label' => 'Property Management', 'icon' => 'fa-building', 'items' => [
            ['route' => 'admin.all-rooms', 'match' => 'admin.all-rooms', 'icon' => 'fa-building', 'label' => 'All Rooms'],
            ['route' => 'admin.room-options.index', 'match' => 'admin.room-options*', 'icon' => 'fa-sliders-h', 'label' => 'Room Options'],
            ['route' => 'admin.rejection-reasons.index', 'match' => 'admin.rejection-reasons*', 'icon' => 'fa-clipboard-list', 'label' => 'Rejection Reasons'],
        ]],
        'people' => ['label' => 'People Management', 'icon' => 'fa-users', 'items' => [
            ['route' => 'admin.members.index', 'match' => 'admin.members.index', 'icon' => 'fa-magnifying-glass-chart', 'label' => 'Member 360'],
            ['route' => 'admin.owners', 'match' => 'admin.owners*', 'icon' => 'fa-user-tie', 'label' => 'Owners'],
            ['route' => 'admin.users', 'match' => 'admin.users*', 'icon' => 'fa-users', 'label' => 'Users'],
        ]],
        'support' => ['label' => 'Support', 'icon' => 'fa-headset', 'items' => [
            ['route' => 'admin.complaints.index', 'match' => 'admin.complaints*', 'icon' => 'fa-shield-halved', 'label' => 'Complaints'],
            ['route' => 'admin.contact-messages.index', 'match' => 'admin.contact-messages*', 'icon' => 'fa-inbox', 'label' => 'Contact Enquiries'],
            ['route' => 'admin.city-alerts.index', 'match' => 'admin.city-alerts*', 'icon' => 'fa-bell', 'label' => 'City Alerts'],
            ['route' => 'admin.subscribers.index', 'match' => 'admin.subscribers*', 'icon' => 'fa-envelope', 'label' => 'Subscribers'],
        ]],
        'finance' => ['label' => 'Finance & Plans', 'icon' => 'fa-wallet', 'items' => [
            ['route' => 'admin.payments.index', 'match' => 'admin.payments*', 'icon' => 'fa-credit-card', 'label' => 'Payments'],
            ['route' => 'admin.payouts', 'match' => 'admin.payouts*', 'icon' => 'fa-wallet', 'label' => 'Payouts'],
            ['route' => 'admin.plans.index', 'match' => 'admin.plans*', 'icon' => 'fa-tags', 'label' => 'Subscription Plans'],
            ['route' => 'admin.offers.index', 'match' => 'admin.offers*', 'icon' => 'fa-bullhorn', 'label' => 'Offers'],
        ]],
        'content' => ['label' => 'Content Management', 'icon' => 'fa-pen-to-square', 'items' => [
            ['route' => 'admin.home-page.index', 'match' => 'admin.home-page*', 'icon' => 'fa-home', 'label' => 'Home Page'],
            ['route' => 'admin.blogs.index', 'match' => 'admin.blogs*', 'icon' => 'fa-newspaper', 'label' => 'Blogs'],
            ['route' => 'admin.cms-pages.index', 'match' => 'admin.cms-pages*', 'icon' => 'fa-file-lines', 'label' => 'CMS Pages'],
        ]],
        'reports' => ['label' => 'Reports & Analytics', 'icon' => 'fa-chart-line', 'items' => [
            ['route' => 'admin.reports', 'match' => 'admin.reports', 'icon' => 'fa-chart-pie', 'label' => 'Reports'],
            ['route' => 'admin.analytics', 'match' => 'admin.analytics', 'icon' => 'fa-chart-simple', 'label' => 'Analytics'],
        ]],
        'settings' => ['label' => 'Settings', 'icon' => 'fa-gear', 'items' => [
            ['route' => 'admin.settings', 'match' => 'admin.settings*', 'icon' => 'fa-cog', 'label' => 'Business Settings'],
            ['route' => 'admin.cities.index', 'match' => 'admin.cities*', 'icon' => 'fa-map-location-dot', 'label' => 'Operational Cities'],
            ['route' => 'admin.maintenance', 'match' => 'admin.maintenance*', 'icon' => 'fa-screwdriver-wrench', 'label' => 'Maintenance'],
            ['route' => 'admin.data-maintenance.index', 'match' => 'admin.data-maintenance*', 'icon' => 'fa-database', 'label' => 'Data Maintenance'],
        ]],
        'administration' => ['label' => 'Administration', 'icon' => 'fa-user-shield', 'items' => [
            ['route' => 'admin.staff.index', 'match' => 'admin.staff*', 'icon' => 'fa-user-gear', 'label' => 'Admin Staff'],
            ['route' => 'admin.roles.index', 'match' => 'admin.roles*', 'icon' => 'fa-key', 'label' => 'Roles & Permissions'],
            ['route' => 'admin.activity.index', 'match' => 'admin.activity*', 'icon' => 'fa-clock-rotate-left', 'label' => 'Activity Logs'],
        ]],
    ];
    $pagesOpen = false;

    $unreadContactCount = \App\Models\AdminNotification::where('type', 'contact_inquiry')->where('is_read', false)->count();
    $unreadRoomsCount = \App\Models\AdminNotification::where('type', 'room_posted')->where('is_read', false)->count();
    $unreadComplaintsCount = \App\Models\AdminNotification::where('type', 'complaint_submitted')->where('is_read', false)->count();
@endphp

<button id="adminSidebarOpen" class="admin-theme-avatar lg:hidden fixed top-3 left-3 z-[70] w-10 h-10 rounded-xl shadow-lg" aria-label="Open admin menu"><i class="fas fa-bars"></i></button>
<div id="adminSidebarBackdrop" class="hidden lg:hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[55]"></div>
<aside id="adminSidebar" class="fixed lg:sticky top-0 left-0 z-[60] h-screen w-[280px] bg-white text-slate-700 border-r border-slate-200 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 shrink-0 shadow-[6px_0_24px_rgba(15,23,42,0.06)]">
    <div class="h-16 px-4 flex items-center justify-between border-b border-slate-200 bg-slate-50/70">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
            @if($adminLogo)
                <span class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shadow-sm p-1">
                    <img src="{{ asset('storage/' . $adminLogo) }}" alt="{{ $adminSiteName }}" class="w-full h-full object-contain">
                </span>
            @else
                <span class="admin-theme-avatar w-9 h-9 rounded-lg flex items-center justify-center shadow-sm"><i class="fas fa-home text-xs"></i></span>
            @endif
            <span class="min-w-0"><strong class="block text-slate-900 text-[15px] leading-tight truncate max-w-[175px]">{{ $adminSiteName }}</strong><small class="block text-[9px] text-slate-500 tracking-[.14em] uppercase mt-1 font-bold">Admin Workspace</small></span>
        </a>
        <button id="adminSidebarClose" class="lg:hidden w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100"><i class="fas fa-times"></i></button>
    </div>
    <div class="shrink-0 border-b border-slate-100 bg-white px-3 py-3">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-10 items-center justify-center text-slate-400"><i class="fas fa-magnifying-glass text-[11px]"></i></span>
            <input id="adminMenuSearch" type="search" autocomplete="off" placeholder="Search admin menu..." class="h-10 w-full rounded-xl border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 placeholder:text-slate-400 focus:bg-white" style="padding-left:2.5rem!important;padding-right:2.5rem!important;-webkit-appearance:none;appearance:none">
            <button id="adminMenuSearchClear" type="button" class="hidden absolute right-2 top-1/2 h-6 w-6 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-200 hover:text-slate-700" aria-label="Clear menu search"><i class="fas fa-xmark text-[10px]"></i></button>
        </div>
        <p id="adminMenuSearchEmpty" class="hidden px-2 pt-3 text-center text-[10px] font-semibold text-slate-400">No menu found</p>
    </div>
    <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-3 space-y-2 overscroll-contain">
        @if(!Auth::user()?->admin_role_id || Auth::user()?->hasAdminPermission('dashboard.view'))
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-idle-icon-hover group relative flex items-center gap-3 rounded-xl px-3 py-3 text-[13px] transition {{ request()->routeIs('admin.dashboard') ? 'admin-sidebar-active font-bold' : 'text-slate-700 font-semibold hover:bg-slate-100 hover:text-slate-950' }}">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500' }}"><i class="fas fa-th-large text-[12px]"></i></span>
                <span>Dashboard</span>
            </a>
        @endif

        <div class="my-2 border-t border-slate-100"></div>

        @foreach($navGroups as $groupKey => $group)
            @php
                $adminUser = Auth::user();
                $groupItems = collect($group['items'])->filter(function ($item) use ($groupKey, $adminUser) {
                    if (!$adminUser || !$adminUser->admin_role_id) return true;
                    return match ($groupKey) {
                        'property' => $adminUser->hasAdminPermission('listings.view') || $adminUser->hasAdminPermission('listings.manage'),
                        'people' => $adminUser->hasAdminPermission('people.view') || $adminUser->hasAdminPermission('people.manage'),
                        'support' => $adminUser->hasAdminPermission('support.view') || $adminUser->hasAdminPermission('support.manage'),
                        'finance' => str_starts_with($item['route'], 'admin.offers')
                            ? ($adminUser->hasAdminPermission('content.view') || $adminUser->hasAdminPermission('content.manage'))
                            : ($adminUser->hasAdminPermission('finance.view') || $adminUser->hasAdminPermission('finance.manage')),
                        'content' => $adminUser->hasAdminPermission('content.view') || $adminUser->hasAdminPermission('content.manage'),
                        'reports' => $adminUser->hasAdminPermission('reports.view'),
                        'settings' => $adminUser->hasAdminPermission('settings.manage'),
                        'administration' => str_starts_with($item['route'], 'admin.activity')
                            ? $adminUser->hasAdminPermission('activity.view')
                            : $adminUser->hasAdminPermission('staff.manage'),
                        default => true,
                    };
                });
                $groupActive = $groupItems->contains(fn ($item) => request()->routeIs($item['match'])) || (!empty($group['has_pages']) && $pagesOpen);
                $groupOpen = $groupActive;
            @endphp
            @continue($groupItems->isEmpty())
            @php
                $groupBadgeCount = collect($group['items'])->sum(function($item) use ($unreadContactCount, $unreadRoomsCount, $unreadComplaintsCount) {
                    return match($item['route']) {
                        'admin.contact-messages.index' => $unreadContactCount,
                        'admin.all-rooms' => $unreadRoomsCount,
                        'admin.complaints.index' => $unreadComplaintsCount,
                        default => 0,
                    };
                });
            @endphp
            <section class="admin-nav-group" data-group="{{ $groupKey }}">
                <button type="button" class="admin-nav-group-toggle group flex w-full items-center justify-between rounded-xl border px-3 py-2.5 text-[13px] font-bold transition {{ $groupActive ? 'admin-sidebar-group-active' : 'border-transparent bg-white text-slate-700 hover:bg-slate-50' }}" aria-expanded="{{ $groupOpen ? 'true' : 'false' }}">
                    <span class="flex min-w-0 items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg shadow-sm {{ $groupActive ? 'admin-sidebar-icon-active' : 'admin-sidebar-icon-idle ring-1 ring-slate-200' }}"><i class="fas {{ $group['icon'] }} text-[12px]"></i></span>
                        <span class="whitespace-nowrap text-[12px]">{{ $group['label'] }}</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        @if($groupBadgeCount > 0)
                            <span class="sidebar-unread-badge rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-extrabold text-white">
                                {{ $groupBadgeCount > 99 ? '99+' : $groupBadgeCount }}
                            </span>
                        @endif
                        <i class="admin-nav-chevron fas fa-chevron-down text-[9px] text-slate-400 transition-transform {{ $groupOpen ? 'rotate-180' : '' }}"></i>
                    </span>
                </button>
                <div class="admin-sidebar-submenu admin-nav-group-menu {{ $groupOpen ? '' : 'hidden' }} ml-6 mt-2 space-y-1.5 border-l-2 pl-3">
                    @foreach($groupItems as $item)
                        @php 
                            $itemActive = request()->routeIs($item['match']);
                            $itemBadge = match($item['route']) {
                                'admin.contact-messages.index' => $unreadContactCount,
                                'admin.all-rooms' => $unreadRoomsCount,
                                'admin.complaints.index' => $unreadComplaintsCount,
                                default => 0,
                            };
                        @endphp
                        <a href="{{ route($item['route']) }}" class="relative flex items-center justify-between gap-2.5 rounded-lg px-3 py-2.5 text-[12px] transition {{ $itemActive ? 'admin-sidebar-subitem-active font-extrabold' : 'text-slate-600 font-semibold hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="flex items-center gap-2.5 min-w-0">
                                <i class="fas {{ $item['icon'] }} w-4 text-center text-[11px] {{ $itemActive ? 'admin-sidebar-active-icon' : 'text-slate-400' }}"></i>
                                <span class="truncate">{{ $item['label'] }}</span>
                            </span>
                            @if($itemBadge > 0)
                                <span class="sidebar-unread-badge shrink-0 rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-extrabold text-white">
                                    {{ $itemBadge > 99 ? '99+' : $itemBadge }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>
    <div id="adminSidebarFooter" class="shrink-0 p-2.5 border-t border-slate-200 bg-white shadow-[0_-6px_18px_rgba(15,23,42,0.04)]">
        <div class="flex items-center gap-2.5 px-2 py-1.5 mb-1.5 rounded-lg bg-slate-50 border border-slate-100">
            @if(Auth::user()->avatar)
                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-white shadow-sm">
            @else
                <div class="admin-theme-avatar w-8 h-8 shrink-0 rounded-lg flex items-center justify-center font-bold text-xs ring-2 ring-white shadow-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            @endif
            <div class="min-w-0 flex-1"><p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p><p class="text-[8px] text-slate-400 font-semibold uppercase tracking-wider">Administrator</p></div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <input type="hidden" name="admin_login" value="1">
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-red-50 border border-red-100 text-red-600 hover:bg-red-600 hover:border-red-600 hover:text-white text-[11px] font-bold transition">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
<script>
(() => {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');
    const toggle = (open) => { sidebar?.classList.toggle('-translate-x-full', !open); backdrop?.classList.toggle('hidden', !open); };
    document.getElementById('adminSidebarOpen')?.addEventListener('click', () => toggle(true));
    document.getElementById('adminSidebarClose')?.addEventListener('click', () => toggle(false));
    backdrop?.addEventListener('click', () => toggle(false));
    document.querySelectorAll('.admin-nav-group-toggle').forEach((button) => {
        button.addEventListener('click', () => {
            const section = button.closest('.admin-nav-group');
            const willOpen = button.getAttribute('aria-expanded') !== 'true';

            document.querySelectorAll('.admin-nav-group').forEach((otherSection) => {
                if (otherSection === section) return;
                otherSection.querySelector('.admin-nav-group-menu')?.classList.add('hidden');
                otherSection.querySelector('.admin-nav-chevron')?.classList.remove('rotate-180');
                otherSection.querySelector('.admin-nav-group-toggle')?.setAttribute('aria-expanded', 'false');
            });

            section?.querySelector('.admin-nav-group-menu')?.classList.toggle('hidden', !willOpen);
            section?.querySelector('.admin-nav-chevron')?.classList.toggle('rotate-180', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
    const menuSearch = document.getElementById('adminMenuSearch');
    const menuSearchClear = document.getElementById('adminMenuSearchClear');
    const menuSearchEmpty = document.getElementById('adminMenuSearchEmpty');
    const nav = sidebar?.querySelector('nav');
    const dashboardLink = nav?.querySelector(':scope > a');
    const separators = nav?.querySelectorAll(':scope > .border-t') || [];
    const normalize = value => (value || '').toLowerCase().trim();
    const filterAdminMenu = () => {
        const term = normalize(menuSearch?.value);
        menuSearchClear?.classList.toggle('hidden', !term);
        menuSearchClear?.classList.toggle('flex', !!term);
        let visibleCount = 0;
        if (dashboardLink) {
            const match = !term || normalize(dashboardLink.textContent).includes(term);
            dashboardLink.classList.toggle('hidden', !match);
            if (match) visibleCount++;
        }
        document.querySelectorAll('#adminSidebar .admin-nav-group').forEach(section => {
            const toggleButton = section.querySelector('.admin-nav-group-toggle');
            const menu = section.querySelector('.admin-nav-group-menu');
            const groupMatches = normalize(toggleButton?.textContent).includes(term);
            let itemMatches = 0;
            section.querySelectorAll('.admin-nav-group-menu a, #websitePagesToggle').forEach(item => {
                const match = !term || groupMatches || normalize(item.textContent).includes(term);
                item.classList.toggle('hidden', !match);
                if (match) itemMatches++;
            });
            const showGroup = !term || groupMatches || itemMatches > 0;
            section.classList.toggle('hidden', !showGroup);
            if (showGroup) visibleCount++;
            if (term && showGroup) {
                menu?.classList.remove('hidden');
                toggleButton?.setAttribute('aria-expanded', 'true');
                section.querySelector('.admin-nav-chevron')?.classList.add('rotate-180');
            }
        });
        separators.forEach(separator => separator.classList.toggle('hidden', !!term));
        menuSearchEmpty?.classList.toggle('hidden', visibleCount > 0);
    };
    menuSearch?.addEventListener('input', filterAdminMenu);
    menuSearch?.addEventListener('keydown', event => {
        if (event.key === 'Escape') { menuSearch.value = ''; filterAdminMenu(); menuSearch.blur(); }
        if (event.key === 'Enter') { event.preventDefault(); nav?.querySelector('a:not(.hidden)')?.click(); }
    });
    menuSearchClear?.addEventListener('click', () => { menuSearch.value = ''; filterAdminMenu(); menuSearch.focus(); });
})();
</script>
