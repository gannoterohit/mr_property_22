
<!-- Mobile Layout (Split: Slider + List) -->
<div class="md:hidden">
    <!-- Section Title -->
    <div class="px-4 pt-4 pb-2">
        <h2 class="text-lg font-bold text-gray-800">Featured Stays</h2>
    </div>

    <!-- 1. Horizontal Scroll Section (Top 5 Rooms) -->
    <div class="flex overflow-x-auto gap-4 px-4 pb-4 snap-x hide-scrollbar mb-2">
        @foreach($rooms->take(5) as $room)
            <div class="min-w-[200px] w-[200px] bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100 snap-center relative">
                <a href="{{ route('rooms.show', $room) }}" class="block">
                    <div class="h-32 relative bg-gray-100">
                        @php
                            $photoUrl = $room->photo_url ?? asset('assets/images/placeholder.jpg');
                            if (str_contains($photoUrl, 'unsplash.com')) {
                                $baseUrl = strtok($photoUrl, '?');
                                $tinyUrl = $baseUrl . '?w=150&h=100&fm=webp&q=70&fit=crop';
                                $smallUrl = $baseUrl . '?w=200&h=128&fm=webp&q=75&fit=crop';
                            } else {
                                $tinyUrl = $smallUrl = $photoUrl;
                            }
                        @endphp
                        <img src="{{ $tinyUrl }}" 
                             srcset="{{ $tinyUrl }} 150w, {{ $smallUrl }} 200w"
                             sizes="150px"
                             class="w-full h-full object-cover"
                             alt="{{ $room->title }}"
                             width="200" height="128"
                             loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                             fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                             decoding="async"
                             onerror="this.onerror=null; this.src='https://placehold.co/200x128?text=Room';">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute bottom-2 left-2 text-white">
                            <p class="text-sm font-bold">₹{{ number_format($room->rent) }}</p>
                        </div>
                        @if($room->is_featured)
                            <span class="absolute top-2 right-2 bg-yellow-400 text-[10px] font-bold px-1.5 py-0.5 rounded text-yellow-900 z-10">Featured</span>
                        @endif
                        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
                            @if($room->listing_type === 'broker')
                                <span style="background-color: var(--secondary);" class="text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-lg uppercase tracking-wider border border-white/20">Broker</span>
                            @else
                                <span style="background-color: var(--secondary); border-color: rgba(var(--secondary-rgb), 0.55);" class="text-white text-[8px] font-black px-1.5 py-0.5 rounded shadow-lg uppercase tracking-wider border">Owner</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-3">
                        <h2 class="font-bold text-gray-800 text-sm truncate">{{ $room->title }}</h2>
                        <p class="text-xs text-gray-500 truncate mb-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $room->city }}</p>
                        <div class="flex flex-wrap gap-1 mt-2">
                            <span style="background-color: rgba(var(--primary-rgb), 0.08); color: var(--primary);" class="text-[9px] dark:bg-slate-800 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">{{ $room->roomTypeLabel() }}</span>
                            @if($room->propertyType?->name)
                                <span style="background-color: rgba(var(--primary-rgb), 0.08); color: var(--primary);" class="text-[9px] dark:bg-slate-800 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">{{ $room->propertyType->name }}@if($room->propertyCategory?->name) · {{ $room->propertyCategory->name }}@endif</span>
                            @endif
                            @if($room->listing_type === 'broker')
                                <span style="background-color: var(--secondary);" class="text-[9px] text-white px-2 py-0.5 rounded-lg font-black uppercase tracking-tighter">B: ₹{{ $room->broker_fee }}</span>
                            @else
                                <span style="background-color: var(--secondary);" class="text-[9px] text-white px-2 py-0.5 rounded-full font-black uppercase tracking-tighter">Owner</span>
                            @endif
                            @if($room->area_sqft)
                                <span style="background-color: rgba(var(--primary-rgb), 0.08); color: var(--primary);" class="text-[9px] dark:bg-slate-800 px-2 py-0.5 rounded-full font-bold uppercase tracking-tighter">{{ number_format((float)$room->area_sqft, 2) }} sqft</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

        <!-- Section Title -->
        <div class="px-4 pt-2 pb-2">
            <h2 class="text-lg font-bold text-gray-800">All Listings</h2>
        </div>

        <!-- Mobile Offer Banner -->
        <div class="px-4 mb-4">
            @include('partials.offer-banner', ['placement' => 'mobile_feed'])
        </div>

        <!-- 2. Vertical List (Remaining Rooms) -->
        <div id="mobile-room-list" class="px-3 pb-20">
            @foreach($rooms->skip(5) as $room)
                @include('partials.mobile-room-card', ['room' => $room])
            @endforeach
        </div>

        <!-- Infinite Scroll Loader -->
        <div id="infinite-loader" class="px-3 pb-24 {{ $rooms->hasMorePages() ? '' : 'hidden' }}">
            @include('rooms.partials.skeleton')
        </div>
    </div>

