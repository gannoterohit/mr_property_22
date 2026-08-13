@php
    $placement = $placement ?? 'dashboard';
    $mode = $mode ?? 'standard';
    $userRole = Auth::check() ? Auth::user()->role : null;
    
    // Fetch active offers from database
    $offers = \App\Models\Offer::active()
        ->where('placement', $placement)
        ->when($userRole, function($query) use ($userRole) {
            return $query->forAudience($userRole);
        }, function($query) {
            // Guests (non-logged in) should see 'user' offers too (as potential new users)
            return $query->whereIn('target_audience', ['both', 'user']);
        })
        ->get();

    // Fallback Mock Data if no offers exist (For Testing/Demo)
    // Disabled as per user feedback - only show real DB offers
    // if ($offers->count() === 0) {
    //     $mockOffer = new \stdClass();
    //     $mockOffer->id = 999;
    //     $mockOffer->title = 'Special Festive Offer';
    //     $mockOffer->description = 'Get 20% off on your first month rent. Limited time deal!';
    //     $mockOffer->discount_text = 'FLAT 20% OFF';
    //     $mockOffer->banner_color = '#4f46e5'; // Indigo
    //     $mockOffer->image_path = null;
    //     $mockOffer->image_url = asset('assets/images/offer-placeholder.jpg');
    //     
    //     $offers = collect([$mockOffer]);
    // }
@endphp

@if($offers->count() > 0)
    @if($placement === 'top_nav')
        @php($topOffer = $offers->first())
        <!-- Top Navigation Strip -->
        <div id="topOfferStrip" class="relative z-[60]">
                <div class="py-1.5 md:py-2.5 text-center text-white text-[10px] md:text-sm font-semibold relative overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md" 
                     style="background: {{ $topOffer->banner_color }};">
                    <div class="container mx-auto px-4 flex items-center justify-center gap-1.5 md:gap-3 flex-wrap">
                        @if($topOffer->link_url)<a href="{{ $topOffer->link_url }}" class="flex items-center gap-1.5 md:gap-2 hover:opacity-90 transition group">@endif
                        
                        <span class="tracking-wide">{{ $topOffer->title }}</span>
                        @if($topOffer->discount_text)
                            <span class="bg-white/95 text-slate-900 px-1.5 py-0.5 rounded-full text-[8px] md:text-[10px] font-black uppercase tracking-widest shadow-sm whitespace-nowrap">{{ $topOffer->discount_text }}</span>
                        @endif
                        
                        @if($topOffer->link_url)
                            <i class="fas fa-arrow-right text-[8px] md:text-[10px] opacity-70 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        @endif
                    </div>
                     <button onclick="document.getElementById('topOfferStrip').remove()" 
                            class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 w-5 h-5 md:w-6 md:h-6 flex items-center justify-center bg-black/10 hover:bg-black/20 rounded-full text-[8px] md:text-[10px] transition text-white/80 hover:text-white"
                            aria-label="Close offer strip">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
        </div>

    @elseif($placement === 'home_hero')
        <!-- Premium Home Hero Banner -->
        <div class="relative group my-6 md:my-10">
            <div class="overflow-hidden rounded-2xl md:rounded-[2rem] shadow-[0_20px_60px_-15px_rgba(79,70,229,0.15)] border border-indigo-50/50">
                <div id="carousel-{{ $placement }}" class="flex transition-transform duration-700 cubic-bezier(0.4, 0, 0.2, 1)">
                    @foreach($offers as $offer)
                    <div class="w-full flex-shrink-0 relative">
                        @if($offer->link_url)<a href="{{ url($offer->link_url) }}" class="block relative overflow-hidden group/banner">@endif
                        
                        <div class="relative min-h-[160px] md:min-h-[240px] flex items-center">
                            <!-- Background Layer -->
                            @if($offer->image_path)
                                <img src="{{ $offer->image_url }}" alt="{{ $offer->title }}" width="1200" height="630" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[2s] group-hover/banner:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/40 to-transparent"></div>
                            @else
                                <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $offer->banner_color }}, var(--secondary));"></div>
                                <!-- Decorative Circles -->
                                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
                                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-60 h-60 rounded-full bg-black/10 blur-2xl"></div>
                                <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 24px 24px;"></div>
                            @endif

                            <!-- Content Overlay -->
                            <div class="relative z-10 w-full px-6 md:px-12 text-white py-6 md:py-8">
                                <div class="max-w-xl">
                                    @if($offer->discount_text)
                                        <div class="inline-block bg-white/20 backdrop-blur-md border border-white/30 px-3 py-1 rounded-lg text-[10px] md:text-[11px] font-black uppercase tracking-[0.15em] mb-3 shadow-lg">
                                            {{ $offer->discount_text }}
                                        </div>
                                    @endif
                                    
                                    <h2 class="text-xl md:text-3xl font-black mb-2 leading-tight tracking-tight drop-shadow-lg">
                                        {{ $offer->title }}
                                    </h2>
                                    
                                    <p class="text-xs md:text-sm text-white/90 mb-4 md:mb-6 font-medium leading-relaxed drop-shadow-md max-w-lg line-clamp-2 md:line-clamp-none">
                                        {{ $offer->description }}
                                    </p>
                                    
                                    <div class="flex items-center gap-4">
                                        <div class="bg-white text-indigo-600 px-5 md:px-7 py-2 md:py-2.5 rounded-lg md:rounded-xl font-black text-[10px] md:text-xs uppercase tracking-widest shadow-xl hover:bg-indigo-50 transition active:scale-95 flex items-center gap-2">
                                            Grab Deal <i class="fas fa-arrow-right text-[10px]"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($offer->link_url)</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            @if($offers->count() > 1)
                <!-- Navigation -->
                <div class="absolute bottom-4 right-4 md:bottom-8 md:right-12 z-20 flex items-center gap-2">
                    @foreach($offers as $index => $offer)
                    <button onclick="goToOffer('{{ $placement }}', {{ $index }})" 
                            class="dot-{{ $placement }} w-1.5 h-1.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-white w-4 md:w-6' : 'bg-white/40' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

    @elseif($placement === 'mobile_feed')
        <!-- Mobile Feed Embedded Banner -->
        <div class="mb-6 relative group">
            <div class="overflow-hidden rounded-2xl shadow-md border border-indigo-50">
                <div id="carousel-{{ $placement }}" class="flex transition-transform duration-500">
                    @foreach($offers as $offer)
                    <div class="w-full flex-shrink-0">
                        @if($offer->link_url)<a href="{{ url($offer->link_url) }}" class="block">@endif
                        
                        <div class="relative p-5 min-h-[140px] flex items-center overflow-hidden" 
                             style="background: linear-gradient(135deg, {{ $offer->banner_color }}, var(--secondary));">
                            
                            <!-- Decorative Elements -->
                            <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                            
                            @if($offer->image_path)
                                <img src="{{ $offer->image_url }}" width="1200" height="630" class="absolute inset-0 w-full h-full object-cover opacity-30 mix-blend-overlay" loading="lazy" decoding="async" alt="Offer Background">
                            @endif

                            <div class="relative z-10 flex flex-row items-center justify-between w-full gap-4">
                                <div class="text-white flex-1">
                                    @if($offer->discount_text)
                                        <span class="inline-block bg-white/20 backdrop-blur-sm px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider mb-1.5 border border-white/20">
                                            {{ $offer->discount_text }}
                                        </span>
                                    @endif
                                    <h3 class="text-lg font-black leading-tight mb-1">{{ $offer->title }}</h3>
                                    <p class="text-white/80 text-[10px] line-clamp-2 leading-relaxed">{{ $offer->description }}</p>
                                </div>
                                
                                <div class="flex-shrink-0">
                                    <div class="bg-white text-indigo-600 w-10 h-10 rounded-full flex items-center justify-center shadow-lg active:scale-90 transition-transform">
                                        <i class="fas fa-chevron-right text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($offer->link_url)</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            
            @if($offers->count() > 1)
                <div class="flex justify-center gap-1.5 mt-3">
                    @foreach($offers as $index => $offer)
                    <button onclick="goToOffer('{{ $placement }}', {{ $index }})" class="dot-{{ $placement }} w-1.5 h-1.5 rounded-full transition-all duration-300 {{ $index === 0 ? 'bg-indigo-600 w-4' : 'bg-gray-200' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

    @elseif($placement === 'dashboard')
        <!-- Dashboard Style -->
        <div class="mb-8 relative group">
            <div class="overflow-hidden rounded-3xl shadow-lg border border-gray-100">
                <div id="carousel-{{ $placement }}" class="flex transition-transform duration-500">
                    @foreach($offers as $offer)
                    <div class="w-full flex-shrink-0">
                        @if($offer->link_url)<a href="{{ url($offer->link_url) }}" class="block">@endif
                        
                        <div class="relative p-8 min-h-[220px] flex items-center overflow-hidden" 
                             style="background: linear-gradient(135deg, {{ $offer->banner_color }}, {{ $offer->banner_color }}dd);">
                            
                            @if($offer->image_path)
                                <img src="{{ $offer->image_url }}" width="1200" height="630" class="absolute inset-0 w-full h-full object-cover opacity-20">
                            @endif

                            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between w-full gap-6">
                                <div class="text-white text-center md:text-left flex-1">
                                    <h3 class="text-2xl font-black mb-2">{{ $offer->title }}</h3>
                                    <p class="text-white/90 text-sm max-w-md">{{ $offer->description }}</p>
                                </div>
                                @if($offer->discount_text)
                                <div class="bg-white/20 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/30">
                                    <div class="text-2xl font-black text-white">{{ $offer->discount_text }}</div>
                                </div>
                                @endif
                                <div class="bg-white text-gray-900 px-6 py-3 rounded-2xl font-bold text-sm shadow-xl active:scale-95 transition">
                                    Action Required <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                            </div>
                        </div>

                        @if($offer->link_url)</a>@endif
                    </div>
                    @endforeach
                </div>
            </div>
            
            @if($offers->count() > 1)
                <div class="flex justify-center gap-2 mt-4">
                    @foreach($offers as $index => $offer)
                    <button onclick="goToOffer('{{ $placement }}', {{ $index }})" class="dot-{{ $placement }} w-1.5 h-1.5 rounded-full transition {{ $index === 0 ? 'bg-indigo-600 w-6' : 'bg-gray-300' }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

    @elseif($placement === 'sidebar')
        <!-- Sidebar Style -->
        <div class="space-y-4">
            @foreach($offers as $offer)
                <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100 group bg-white hover:shadow-md transition">
                    @if($offer->link_url)<a href="{{ $offer->link_url }}">@endif
                    @if($offer->image_path || isset($offer->image_url))
                        <img src="{{ $offer->image_url }}" alt="{{ $offer->title }}" width="800" height="600" class="w-full h-auto object-cover aspect-[4/3] group-hover:scale-105 transition duration-500">
                    @endif
                    <div class="p-4">
                        @if($offer->discount_text)
                            <span class="inline-block bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-lg text-[10px] font-black tracking-widest uppercase mb-2">{{ $offer->discount_text }}</span>
                        @endif
                        <h4 class="font-bold text-gray-900 leading-tight group-hover:text-indigo-600 transition">{{ $offer->title }}</h4>
                        <p class="text-[11px] text-gray-500 mt-1">{{ Str::limit($offer->description, 70) }}</p>
                    </div>
                    @if($offer->link_url)</a>@endif
                </div>
            @endforeach
        </div>
    @elseif($placement === 'popup')
        <!-- Special Timed Popup Offer -->
        @php
            // Only show the first active popup to avoid stacking multiple modals
            $popupOffer = $offers->first();
        @endphp

        @if($popupOffer)
        <div id="offerPopup-{{ $popupOffer->id }}" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 md:p-6" style="display: none;">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeOfferPopup({{ $popupOffer->id }})"></div>
            
            <!-- Modal Content -->
            <div class="relative bg-white w-full max-w-sm md:max-w-lg rounded-[2rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.3)] overflow-hidden transform transition-all scale-95 opacity-0 duration-500 popup-container">
                <!-- Close Button -->
                 <button onclick="closeOfferPopup({{ $popupOffer->id }})" 
                        class="absolute top-4 right-4 z-30 w-8 h-8 flex items-center justify-center rounded-full bg-black/10 hover:bg-black/20 text-gray-800 transition-all hover:rotate-90 cursor-pointer"
                        aria-label="Close offer popup">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>

                <div class="relative group">
                    @if($popupOffer->link_url)
                        <a href="{{ url($popupOffer->link_url) }}" class="block relative h-full w-full cursor-pointer">
                    @endif

                    @if($popupOffer->image_path)
                        <div class="h-40 md:h-56 relative overflow-hidden">
                            <img src="{{ $popupOffer->image_url }}" alt="{{ $popupOffer->title }}" width="800" height="600" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-white via-transparent to-transparent"></div>
                            
                            @if($popupOffer->discount_text)
                                <div class="absolute top-6 left-6 bg-indigo-600 text-white px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg border border-white/20 z-20">
                                    {{ $popupOffer->discount_text }}
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="h-28 md:h-32 relative overflow-hidden flex items-center justify-center" style="background: linear-gradient(135deg, {{ $popupOffer->banner_color }}, var(--secondary));">
                            <div class="text-white text-5xl opacity-20 transform -rotate-12"><i class="fas fa-gift"></i></div>
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        </div>
                    @endif

                    @if($popupOffer->link_url)
                        </a>
                    @endif

                    <div class="p-6 md:p-8 text-center relative z-10">
                        <h2 class="text-2xl md:text-3xl font-black text-slate-900 mb-2 leading-tight">
                            {{ $popupOffer->title }}
                        </h2>
                        <p class="text-slate-600 text-xs md:text-sm leading-relaxed mb-6 px-4">
                            {{ $popupOffer->description }}
                        </p>

                        <div class="space-y-3">
                            @if($popupOffer->link_url)
                                <a href="{{ url($popupOffer->link_url) }}" class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-xl shadow-indigo-500/20 transition-all active:scale-95 text-center uppercase tracking-wider text-xs">
                                    Grab Offer Now
                                </a>
                            @endif
                            <button onclick="closeOfferPopup({{ $popupOffer->id }})" class="block w-full py-2 text-slate-400 hover:text-slate-600 font-bold text-[10px] uppercase tracking-wider transition">
                                No thanks, I'll pay full price
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Check if already closed in this session
                const popupId = 'offer_popup_{{ $popupOffer->id }}';
                const isClosed = sessionStorage.getItem(popupId);
                
                if (!isClosed) {
                    // Show after delay
                    setTimeout(() => {
                        const popup = document.getElementById('offerPopup-{{ $popupOffer->id }}');
                        if (popup) {
                            popup.style.display = 'flex';
                            popup.classList.remove('hidden');
                            
                            // Trigger animation
                            setTimeout(() => {
                                const container = popup.querySelector('.popup-container');
                                if(container) {
                                    container.classList.remove('scale-95', 'opacity-0');
                                    container.classList.add('scale-100', 'opacity-100');
                                }
                            }, 50);
                        }
                    }, {{ \App\Models\Setting::get('popup_delay', 3) * 1000 }});
                }
            });

            function closeOfferPopup(id) {
                const popup = document.getElementById('offerPopup-' + id);
                if (popup) {
                    const container = popup.querySelector('.popup-container');
                    if(container) {
                        container.classList.remove('scale-100', 'opacity-100');
                        container.classList.add('scale-95', 'opacity-0');
                    }
                    
                    setTimeout(() => {
                        popup.classList.add('hidden');
                        popup.style.display = 'none';
                        // Keep it dismissed for this browser session only.
                        sessionStorage.setItem('offer_popup_' + id, 'true');
                    }, 300);
                }
            }
        </script>
        @endif
    @endif
@endif

@pushOnce('scripts')
<script>
    const carouselStates = {};

    function updateCarouselState(id, count) {
        if (!carouselStates[id]) {
            carouselStates[id] = { current: 0, total: count };
        }
    }

    function renderCarousel(id) {
        const state = carouselStates[id];
        const carousel = document.getElementById(`carousel-${id}`);
        if (!carousel) return;
        
        carousel.style.transform = `translateX(-${state.current * 100}%)`;
        
        // Update dots
        const activeClass = id === 'home_hero' ? 'bg-white' : 'bg-indigo-600';
        const widthClass = id === 'home_hero' ? 'w-6' : 'w-4';
        
        document.querySelectorAll(`.dot-${id}`).forEach((dot, index) => {
            // Reset all
            dot.className = `dot-${id} w-1.5 h-1.5 rounded-full transition-all duration-300 bg-gray-300`;
             if (id === 'home_hero') dot.className = `dot-${id} w-1.5 h-1.5 rounded-full transition-all duration-300 bg-white/40`;

            if (index === state.current) {
                 dot.className = `dot-${id} h-1.5 rounded-full transition-all duration-300 ${activeClass} ${widthClass}`;
            }
        });
    }

    function nextOffer(id) {
        if(!carouselStates[id]) return;
        const state = carouselStates[id];
        state.current = (state.current + 1) % state.total;
        renderCarousel(id);
    }

    function prevOffer(id) {
        if(!carouselStates[id]) return;
        const state = carouselStates[id];
        state.current = (state.current - 1 + state.total) % state.total;
        renderCarousel(id);
    }

    function goToOffer(id, index) {
        if(!carouselStates[id]) return;
        carouselStates[id].current = index;
        renderCarousel(id);
    }

    // Initialize carousels
    document.addEventListener('DOMContentLoaded', () => {
        const carousels = document.querySelectorAll('[id^="carousel-"]');
        carousels.forEach(c => {
            const id = c.id.replace('carousel-', '');
            const count = c.children.length;
            if(count > 0) {
                updateCarouselState(id, count);
                // Auto-advance
                setInterval(() => nextOffer(id), 6000);
            }
        });
    });
</script>
@endPushOnce
