<div class="bottom-nav lg:hidden fixed bottom-0 left-0 right-0 z-[1000] flex justify-between items-center" style="background:rgba(255,255,255,0.92);backdrop-filter:blur(20px) saturate(180%);-webkit-backdrop-filter:blur(20px) saturate(180%);border-top:1px solid rgba(0,0,0,0.06);box-shadow:0 -4px 24px rgba(0,0,0,0.06),0 -1px 2px rgba(0,0,0,0.04);padding-bottom:env(safe-area-inset-bottom,14px);height:64px;padding-top:6px;">
    <!-- 1. Home -->
    <a href="{{ route('rooms.index') }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ (Route::is('rooms.index') && !request('city')) ? 'active' : '' }}" data-nav="home">
        <i class="fas fa-home nav-icon"></i>
        <span class="nav-label">Home</span>
    </a>

    <!-- 2. Earn -->
    @if(\App\Models\Setting::isEnabled('referral_enabled', true))
        <a href="{{ route('referral.index') }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ Route::is('referral.index') ? 'active' : '' }}" data-nav="earn">
            <i class="fas fa-gift nav-icon"></i>
            <span class="nav-label">Earn</span>
        </a>
    @endif

    <!-- 3. Saved -->
    <a href="{{ route('wishlist.index') }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ Route::is('wishlist.index') ? 'active' : '' }}" data-nav="saved">
        <i class="{{ Route::is('wishlist.index') ? 'fas' : 'far' }} fa-heart nav-icon"></i>
        <span class="nav-label">Saved</span>
    </a>
    
    <!-- 4. Reads -->
    <a href="{{ route('blogs.index') }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ Route::is('blogs.*') ? 'active' : '' }}" data-nav="reads">
        <i class="{{ Route::is('blogs.*') ? 'fas' : 'far' }} fa-newspaper nav-icon"></i>
        <span class="nav-label">Reads</span>
    </a>
    
    <!-- 5. Account -->
    @auth
        @php
            $accountRoute = Auth::user()->role === 'owner' 
                ? route('owner.dashboard') 
                : (Auth::user()->role === 'admin' ? route('admin.dashboard') : route('profile.edit'));
            $isAccountActive = Route::is('dashboard') || (Auth::user()->role === 'user' && Route::is('profile.edit'));
        @endphp
        <a href="{{ $accountRoute }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ $isAccountActive ? 'active' : '' }}" data-nav="account">
            <i class="{{ $isAccountActive ? 'fas' : 'far' }} fa-user-circle nav-icon"></i>
            <span class="nav-label">Account</span>
        </a>
    @else
        <a href="{{ route('login') }}" class="bottom-nav-item flex flex-col items-center justify-center w-full h-full {{ Route::is('login') ? 'active' : '' }}" data-nav="account">
            <i class="far fa-user-circle nav-icon"></i>
            <span class="nav-label">Login</span>
        </a>
    @endauth
</div>

<script>
(function() {
    if (window.innerWidth >= 1024) return;
    
    const nav = document.querySelector('.bottom-nav');
    if (!nav) return;
    
    const currentPath = window.location.pathname;
    const items = nav.querySelectorAll('.bottom-nav-item');
    
    items.forEach(function(item) {
        const href = item.getAttribute('href');
        if (!href) return;
        
        const url = new URL(href, window.location.origin);
        const itemPath = url.pathname;
        
        if (currentPath === itemPath || (itemPath !== '/' && currentPath.startsWith(itemPath))) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
})();
</script>
