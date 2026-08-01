    <!-- Mobile App Header - Enhanced App Style -->
    <div class="mobile-app-header lg:hidden">
        <div class="header-left">
                    @php $mobileLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo'); @endphp
                    @if($mobileLogo)
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('storage/' . $mobileLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="h-9 w-9 object-contain rounded-lg border border-slate-200 p-1 bg-white">
                        </a>
                    @else
                        <div class="app-icon">
                            <i class="fas fa-home text-white text-xl"></i>
                        </div>
                    @endif
                <div class="header-content">
                <h1 class="text-lg font-bold text-gray-900 leading-none">{{ \App\Models\Setting::get('website_name', 'RoomRental') }}</h1>
                <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider">Find your perfect stay</p>
                </div>
        </div>
        <div class="header-right">
            <button id="mobile-menu-toggle-app"
                    class="menu-toggle"
                    aria-label="Open navigation menu">
                <i class="fas fa-bars text-xl" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile App Menu - Include the new app-style menu -->
    @include('partials.mobile-app-menu')
    
    
    <!-- Compact Desktop Navigation -->
    <!-- Desktop Navigation (Redesigned) -->
    <nav class="hidden md:block bg-white border-b border-slate-100 shadow-sm sticky top-0 z-40">
        <div class="desktop-navbar-inner">
            <div class="desktop-navbar-row">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="desktop-navbar-logo flex items-center gap-2 overflow-visible">
                    @php
                        $navbarLogo = \App\Models\Setting::get('navbar_logo');
                    @endphp
                    @if($navbarLogo)
                        <img src="{{ asset('storage/' . $navbarLogo) }}"
                             alt="ApnaNest Logo"
                             class="navbar-brand-logo">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="theme-brand-mark w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-home text-lg"></i>
                            </div>
                            <span class="text-xl font-black text-slate-900 tracking-tight">Apna<span class="theme-brand-text">Nest</span></span>
                        </div>
                    @endif
                </a>
                
                <!-- Center Links -->
                <div class="desktop-navbar-menu hidden lg:flex items-center gap-1 bg-slate-50 border border-slate-100 rounded-xl p-1">
                    <a href="{{ route('home') }}" class="theme-nav-link {{ request()->routeIs('home') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Home</a>
                    <a href="{{ route('rooms.index') }}" class="theme-nav-link {{ request()->routeIs('rooms.index', 'rooms.show') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Browse Rooms</a>
                    @if($cmsPageLive('how-it-works'))
                        <a href="{{ route('pages.how-it-works') }}" class="theme-nav-link {{ request()->routeIs('pages.how-it-works') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">How It Works</a>
                    @endif
                    <a href="{{ Auth::check() ? (Auth::user()->role === 'owner' ? route('owner.dashboard') : route('dashboard')) : route('register', ['role' => 'owner']) }}" class="theme-nav-link {{ request()->routeIs('owner.*') || (request()->routeIs('register') && request('role') === 'owner') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">For Owners</a>
                    <a href="{{ route('blogs.index') }}" class="theme-nav-link {{ request()->routeIs('blogs.*') ? 'theme-nav-link-active' : '' }} px-3 py-2 rounded-lg text-slate-600 hover:bg-white text-xs font-bold transition">Blog</a>
                </div>
                
                <!-- Right Side Actions -->
                <div class="desktop-navbar-actions flex items-center justify-end gap-3" style="overflow: visible;">
                    <!-- Wishlist Icon (Heart) -->
                    <a href="{{ route('wishlist.index') }}" class="h-10 w-10 shrink-0 inline-flex items-center justify-center text-slate-600 hover:text-red-500 transition-colors relative" title="My Wishlist">
                        <i class="far fa-heart text-lg"></i>
                    </a>
                    
                    @auth
                        <!-- Account Dropdown - Fixed position via JS to avoid clipping -->
                        @php
                            $isRenter = Auth::user()->role === 'user';
                            $accountHome = Auth::user()->role === 'owner'
                                ? route('owner.dashboard')
                                : (Auth::user()->role === 'admin' ? route('admin.dashboard') : route('home'));
                        @endphp

                        <div x-data="{
                                open: false,
                                top: 0,
                                right: 0,
                                toggle() {
                                    if (!this.open) {
                                        const r = this.$refs.trigger.getBoundingClientRect();
                                        this.top = r.bottom + 8;
                                        this.right = window.innerWidth - r.right;
                                    }
                                    this.open = !this.open;
                                }
                             }"
                             @click.outside="open = false">
                            <!-- Trigger button -->
                            <button x-ref="trigger"
                                    @click="toggle()"
                                    class="theme-nav-link h-10 flex items-center gap-2 text-slate-700 transition-colors duration-200 bg-slate-50 hover:bg-slate-100 px-3 rounded-xl border border-slate-200/60 whitespace-nowrap">
                                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/images/default-avatar.svg') }}"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-7 h-7 rounded-full object-cover border border-slate-200">
                                <span class="hidden xl:inline text-xs font-semibold">{{ Str::limit(Auth::user()->name, 12) }}</span>
                                <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <!-- Dropdown menu — position: fixed, calculated from button rect -->
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 :style="'position:fixed; top:' + top + 'px; right:' + right + 'px; z-index:9999;'"
                                 class="w-56 rounded-xl bg-white border border-slate-100 shadow-xl py-2"
                                 style="display:none;">
                                @if($isRenter)
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> My Profile
                                    </a>
                                    <a href="{{ route('unlocks.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-address-book text-emerald-500 w-4 text-sm"></i> My Unlocked Contacts
                                    </a>
                                    @if(\App\Models\Setting::get('wallet_enabled', '1') === '1')
                                        <a href="{{ route('wallet') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="theme-account-icon fas fa-wallet w-4 text-sm"></i> My Wallet
                                        </a>
                                    @endif
                                    <a href="{{ route('plans') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-crown text-amber-400 w-4 text-sm"></i> View Plans
                                    </a>
                                    @if(\App\Models\Setting::get('referral_enabled', '1') === '1')
                                        <a href="{{ route('referral.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                            <i class="fas fa-gift text-emerald-400 w-4 text-sm"></i> Refer & Earn
                                        </a>
                                    @endif
                                    <a href="{{ route('complaints.index') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="fas fa-headset text-blue-400 w-4 text-sm"></i> Support Tickets
                                    </a>
                                @else
                                    <a href="{{ $accountHome }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-tachometer-alt w-4 text-sm"></i> Dashboard
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="theme-account-link flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                        <i class="theme-account-icon fas fa-user-circle w-4 text-sm"></i> Profile
                                    </a>
                                @endif

                                <div class="h-px bg-slate-100 my-1 mx-3"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt text-red-400 w-4 text-sm"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Post Property Button for Logged In -->
                        @if(Auth::user()->role === 'owner')
                            <a href="{{ route('rooms.create') }}"
                               class="theme-primary-button h-10 px-4 rounded-xl text-sm font-bold transition-all duration-200 shadow-md flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fas fa-plus text-xs"></i> Post Property
                            </a>
                        @endif
                    @else
                        <!-- Guest Actions -->
                        <a href="{{ route('login') }}" 
                           class="theme-nav-link h-10 inline-flex items-center text-slate-700 font-bold transition-colors duration-200 text-sm px-3 whitespace-nowrap">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="theme-primary-button h-10 inline-flex items-center px-4 rounded-xl text-sm font-bold transition-all duration-200 shadow-md whitespace-nowrap">
                            Sign Up
                        </a>
                        <a href="{{ route('register') }}?role=owner"
                           class="theme-secondary-button h-10 inline-flex items-center border px-4 rounded-xl text-sm font-bold transition-all duration-200 whitespace-nowrap">
                            Post Property
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
