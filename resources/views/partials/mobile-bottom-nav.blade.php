<div class="bottom-nav lg:hidden bg-white/95 backdrop-blur-md border-t border-gray-100 fixed bottom-0 left-0 right-0 z-[1000] flex justify-between items-center h-[60px] pb-safe px-2">
    <!-- 1. Home -->
    <a href="{{ route('rooms.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ Route::is('rooms.index') && !request('city') ? 'text-red-600' : 'text-gray-400' }}">
        <i class="{{ Route::is('rooms.index') && !request('city') ? 'fas' : 'fas' }} fa-home text-lg"></i>
        <span class="text-[10px] font-medium">Home</span>
    </a>

    <!-- 2. Earn -->
    @if(\App\Models\Setting::isEnabled('referral_enabled', true))
        <a href="{{ route('referral.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ Route::is('referral.index') ? 'text-red-600' : 'text-gray-400' }}">
            <i class="{{ Route::is('referral.index') ? 'fas' : 'fas' }} fa-gift text-lg"></i>
            <span class="text-[10px] font-medium">Earn</span>
        </a>
    @endif

    <!-- 3. Saved -->
    <a href="{{ route('wishlist.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ Route::is('wishlist.index') ? 'text-red-600' : 'text-gray-400' }}">
        <i class="{{ Route::is('wishlist.index') ? 'fas' : 'far' }} fa-heart text-lg"></i>
        <span class="text-[10px] font-medium">Saved</span>
    </a>
    
    <!-- 4. Blog/Feeds (Added as 5th item request) -->
    <a href="{{ route('blogs.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ Route::is('blogs.*') ? 'text-red-600' : 'text-gray-400' }}">
        <i class="{{ Route::is('blogs.*') ? 'fas' : 'far' }} fa-newspaper text-lg"></i>
        <span class="text-[10px] font-medium">Reads</span>
    </a>
    
    <!-- 5. Account -->
    @auth
        @php
            $accountRoute = Auth::user()->role === 'owner' 
                ? route('owner.dashboard') 
                : (Auth::user()->role === 'admin' ? route('admin.dashboard') : route('profile.edit'));
            $isAccountActive = Route::is('dashboard') || (Auth::user()->role === 'user' && Route::is('profile.edit'));
        @endphp
        <a href="{{ $accountRoute }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ $isAccountActive ? 'text-red-600' : 'text-gray-400' }}">
            <i class="{{ $isAccountActive ? 'fas' : 'far' }} fa-user-circle text-lg"></i>
            <span class="text-[10px] font-medium">Account</span>
        </a>
    @else
        <a href="{{ route('login') }}" class="flex flex-col items-center justify-center w-full h-full space-y-1 {{ Route::is('login') ? 'text-red-600' : 'text-gray-400' }}">
            <i class="far fa-user-circle text-lg"></i>
            <span class="text-[10px] font-medium">Login</span>
        </a>
    @endauth
</div>
