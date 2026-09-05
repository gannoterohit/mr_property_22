@php
    $isEnabled = filter_var(\App\Models\Setting::get('promo_modal_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
    if (!$isEnabled) return;

    $audience = \App\Models\Setting::get('promo_modal_audience', 'guests_only');
    $isLoggedIn = Auth::check();

    // Check audience condition
    if ($audience === 'guests_only' && $isLoggedIn) return;
    if ($audience === 'logged_in' && !$isLoggedIn) return;

    // Do not show on auth pages or admin pages
    if (request()->routeIs('login', 'register', 'password.*', 'verification.*', 'admin.*')) return;
    if (request()->routeIs('admin.login-access')) return;
    if (request()->is('portal/access-check')) return;

    $type = \App\Models\Setting::get('promo_modal_type', 'text_card'); // text_card, banner_image, both
    $badge = \App\Models\Setting::get('promo_modal_badge', 'FarmStayGo Perks');
    $title = \App\Models\Setting::get('promo_modal_title', 'Sign in, save money');
    $description = \App\Models\Setting::get('promo_modal_description', 'Sign in or create a free account to unlock exclusive member discounts & free room contacts.');
    $btnText = \App\Models\Setting::get('promo_modal_btn_text', 'Sign in or register');
    $btnUrl = \App\Models\Setting::get('promo_modal_btn_url', '/login');
    $image = \App\Models\Setting::get('promo_modal_image');
    $delaySeconds = (float) \App\Models\Setting::get('promo_modal_delay', '2.5');
    $cooldownHours = (int) \App\Models\Setting::get('promo_modal_cooldown_hours', '24');
@endphp

<div id="dynamicPromoModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" role="dialog" aria-modal="true">
    <div id="dynamicPromoCard" class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl transform scale-95 transition-all duration-300 border border-slate-100 overflow-hidden text-center">
        
        {{-- Close (X) button --}}
        <button onclick="closeDynamicPromoModal()" class="absolute top-3.5 right-3.5 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-black/20 text-white hover:bg-black/40 backdrop-blur-md transition" aria-label="Close">
            <i class="fas fa-xmark text-sm"></i>
        </button>

        {{-- Graphic Banner Image (if type === 'banner_image' or 'both') --}}
        @if(in_array($type, ['banner_image', 'both']) && $image)
            <div class="relative w-full overflow-hidden {{ $type === 'banner_image' ? 'aspect-[16/10]' : 'h-44 sm:h-48' }}">
                @if($btnUrl)
                    <a href="{{ url($btnUrl) }}" class="block w-full h-full">
                @endif
                <img src="{{ asset('storage/' . $image) }}" alt="Promotion Banner" class="w-full h-full object-cover">
                @if($btnUrl)
                    </a>
                @endif
            </div>
        @endif

        {{-- Text Content (if type === 'text_card' or 'both') --}}
        @if($type !== 'banner_image')
            <div class="p-6 sm:p-8 {{ in_array($type, ['banner_image', 'both']) && $image ? 'pt-5' : '' }}">
                @if($badge)
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-100 px-3.5 py-1 text-xs font-black text-blue-700 uppercase tracking-widest mb-3">
                        <i class="fas fa-crown text-amber-500 text-[11px]"></i>
                        <span>{{ $badge }}</span>
                    </div>
                @endif

                @if($title)
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight leading-tight">
                        {{ $title }}
                    </h2>
                @endif

                @if($description)
                    <p class="mt-2.5 text-sm text-slate-600 leading-relaxed px-2">
                        {{ $description }}
                    </p>
                @endif

                @if($btnText && $btnUrl)
                    <div class="mt-6 space-y-2.5">
                        <a href="{{ url($btnUrl) }}" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition active:scale-[0.98]">
                            <span>{{ $btnText }}</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </a>

                        <button type="button" onclick="closeDynamicPromoModal()" class="w-full py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
                            Maybe later
                        </button>
                    </div>
                @endif
            </div>
        @else
            {{-- For banner_image only: clickable banner button footer --}}
            @if($btnText && $btnUrl)
                <div class="p-4 bg-white border-t border-slate-100">
                    <a href="{{ url($btnUrl) }}" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-md transition">
                        <span>{{ $btnText }}</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cooldownHours = {{ $cooldownHours }};
    const delayMs = {{ max(100, $delaySeconds * 1000) }};
    const dismissedUntil = localStorage.getItem('dynamic_promo_modal_until');
    const isCooldownActive = cooldownHours > 0 && dismissedUntil && Date.now() < Number(dismissedUntil);

    if (!isCooldownActive) {
        setTimeout(function() {
            const modal = document.getElementById('dynamicPromoModal');
            const card = document.getElementById('dynamicPromoCard');
            if (modal && card) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }, 30);
            }
        }, delayMs);
    }
});

function closeDynamicPromoModal() {
    const modal = document.getElementById('dynamicPromoModal');
    const card = document.getElementById('dynamicPromoCard');
    if (modal && card) {
        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0');
        card.classList.remove('scale-100');
        card.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }, 300);
    }

    const cooldownHours = {{ $cooldownHours }};
    if (cooldownHours > 0) {
        const expireAt = Date.now() + (cooldownHours * 60 * 60 * 1000);
        localStorage.setItem('dynamic_promo_modal_until', expireAt.toString());
    } else {
        sessionStorage.setItem('dynamic_promo_modal_until', '1');
    }
}

document.getElementById('dynamicPromoModal')?.addEventListener('click', function(e) {
    if (e.target.id === 'dynamicPromoModal') {
        closeDynamicPromoModal();
    }
});
</script>
