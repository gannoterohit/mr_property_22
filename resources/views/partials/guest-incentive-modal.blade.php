@guest
@unless(request()->routeIs('login', 'register', 'password.*', 'verification.*', 'admin.*'))
<div id="guestIncentiveModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" role="dialog" aria-modal="true">
    <div id="guestIncentiveCard" class="relative w-full max-w-md bg-white rounded-3xl p-6 sm:p-8 shadow-2xl transform scale-95 transition-all duration-300 border border-slate-100 text-center">
        
        {{-- Close (X) button --}}
        <button onclick="closeGuestModal()" class="absolute top-4 right-4 flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition" aria-label="Close">
            <i class="fas fa-xmark text-sm"></i>
        </button>

        {{-- Top Badge / Logo --}}
        <div class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 border border-blue-100 px-3.5 py-1 text-xs font-black text-blue-700 uppercase tracking-widest mb-4">
            <i class="fas fa-crown text-amber-500 text-[11px]"></i>
            <span>{{ \App\Models\Setting::get('website_name', 'FarmStayGo') }} Perks</span>
        </div>

        {{-- Headline (Exact Booking.com inspired) --}}
        <h2 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight leading-tight">
            Sign in, save money
        </h2>

        {{-- Subtext --}}
        <p class="mt-2.5 text-sm text-slate-600 leading-relaxed px-2">
            Sign in or create a free account to unlock exclusive member discounts & free room contacts.
        </p>

        {{-- CTA Button --}}
        <div class="mt-6 space-y-2.5">
            <a href="{{ route('login') }}" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/25 transition active:scale-[0.98]">
                <span>Sign in or register</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>

            <button type="button" onclick="closeGuestModal()" class="w-full py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 transition">
                Maybe later
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only show if not already dismissed in this browser session
    if (!sessionStorage.getItem('guest_modal_dismissed')) {
        setTimeout(function() {
            const modal = document.getElementById('guestIncentiveModal');
            const card = document.getElementById('guestIncentiveCard');
            if (modal && card) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                // Smooth fade-in
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.classList.add('opacity-100');
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }, 30);
            }
        }, 2500); // 2.5 seconds delay (like Booking.com)
    }
});

function closeGuestModal() {
    const modal = document.getElementById('guestIncentiveModal');
    const card = document.getElementById('guestIncentiveCard');
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
    // Remember dismissal for this session
    sessionStorage.setItem('guest_modal_dismissed', '1');
}

// Close when clicking outside the card
document.getElementById('guestIncentiveModal')?.addEventListener('click', function(e) {
    if (e.target.id === 'guestIncentiveModal') {
        closeGuestModal();
    }
});
</script>
@endunless
@endguest
