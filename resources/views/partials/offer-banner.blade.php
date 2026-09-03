@php
    /**
     * Minimal offer-banner — only renders when placement === 'top_nav'.
     * All other old placements (sidebar, dashboard, popup, home_hero, mobile_feed)
     * are removed. This file is kept for the top announcement bar only.
     */
    $placement = $placement ?? 'top_nav';
    if ($placement !== 'top_nav') return;

    $bannerOffer = \App\Models\Offer::publiclyVisible()
        ->where('is_active', true)
        ->latest()
        ->first();

    if (!$bannerOffer) return;
@endphp

<div id="siteAnnouncementBar" class="relative z-[60] w-full" style="background: linear-gradient(90deg,#1e3a8a,#2563eb);">
    <div class="py-2 text-center text-white text-xs sm:text-sm font-semibold px-10 relative">
        <div class="flex items-center justify-center gap-2 flex-wrap">
            <span class="text-yellow-300 text-sm">🎉</span>
            <span class="tracking-wide">{{ $bannerOffer->title }}</span>
            {{-- Discount label --}}
            <span class="bg-yellow-400 text-yellow-900 px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-widest shadow-sm whitespace-nowrap">
                {{ $bannerOffer->discount_label }}
            </span>
            {{-- Code pill with copy --}}
            @if($bannerOffer->code)
                <span class="inline-flex items-center gap-1.5 bg-white/15 border border-white/30 rounded-full pl-2.5 pr-1.5 py-0.5">
                    <span class="font-black tracking-widest text-[11px]">{{ $bannerOffer->code }}</span>
                    <button onclick="copyCode('{{ $bannerOffer->code }}')" id="copyBtn"
                        class="flex items-center justify-center h-5 w-5 rounded-full bg-white/20 hover:bg-white/40 transition text-[9px] text-white"
                        title="Copy code">
                        <i class="fas fa-copy" id="copyIcon"></i>
                    </button>
                </span>
            @endif
        </div>

        {{-- Close button --}}
        <button onclick="document.getElementById('siteAnnouncementBar').remove()"
            class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center bg-white/15 hover:bg-white/30 rounded-full text-[10px] text-white transition"
            aria-label="Dismiss">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        const icon = document.getElementById('copyIcon');
        icon.className = 'fas fa-check';
        setTimeout(() => { icon.className = 'fas fa-copy'; }, 2000);
    });
}
</script>
