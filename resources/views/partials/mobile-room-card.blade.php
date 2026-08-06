<!-- Mobile App Room Card (Refined Article Style) -->
<div class="app-card lg:hidden mb-6 bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100">
    <!-- Image Section (Fixed Height) -->
    <a href="{{ route('rooms.show', $room) }}" class="block relative h-48 w-full bg-gray-100" aria-label="View details for {{ $room->title }}"> <!-- Fixed Height h-48 -->
        @if($room->photo_url)
            @php
                $photoUrl = $room->photo_url;
                // Balanced compression for mobile
                if (str_contains($photoUrl, 'unsplash.com')) {
                    $baseUrl = strtok($photoUrl, '?');
                    $tinyUrl = $baseUrl . '?w=150&fm=webp&q=70';
                    $smallUrl = $baseUrl . '?w=250&fm=webp&q=75';
                } else {
                    $tinyUrl = $smallUrl = $photoUrl;
                }
            @endphp
            <img src="{{ $tinyUrl }}" 
                 srcset="{{ $tinyUrl }} 150w, {{ $smallUrl }} 250w"
                 sizes="(max-width: 400px) 150px, 250px"
                 class="w-full h-full object-cover" 
                 alt="Photo of {{ $room->title }} in {{ $room->city }}"
                 width="400" height="192"
                 loading="lazy"
                 decoding="async"
                 onerror="this.onerror=null; this.src='https://placehold.co/400x192?text=Room+Image';">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center bg-indigo-50/50">
                <i class="fas fa-house-chimney text-3xl text-indigo-200 mb-1"></i>
                <span class="text-[10px] font-bold text-indigo-300 uppercase tracking-widest">No Image</span>
            </div>
        @endif
        
        <!-- Tags Overlay -->
        <div class="absolute top-3 left-3 flex gap-2">
             <span class="bg-white/95 dark:bg-slate-800/95 backdrop-blur-md px-2.5 py-1 rounded-full text-[10px] font-bold text-gray-800 dark:text-gray-200 shadow-sm uppercase tracking-wide">
                {{ $room->roomTypeLabel() }}
            </span>
            @if($room->listing_type === 'broker')
                <span style="background-color: #f97316 !important;" class="text-white px-2.5 py-1 rounded-lg text-[10px] font-black shadow-sm uppercase tracking-widest border border-white/20">
                    Broker Fee: ₹{{ $room->broker_fee }}
                </span>
            @else
                <span class="bg-emerald-600 text-white px-2.5 py-1 rounded-full text-[10px] font-black shadow-sm uppercase tracking-widest border border-emerald-400">
                    No Broker Fee
                </span>
            @endif
             @if($room->is_featured)
                <span class="bg-amber-400 text-amber-900 px-2.5 py-1 rounded-full text-[10px] font-black shadow-sm uppercase tracking-wide">
                    Featured
                </span>
            @endif
        </div>
    </a>
    
    <!-- Content Section -->
    <div class="p-5">
        <div class="flex justify-between items-start mb-2">
            <h2 class="font-black text-xl text-slate-900 leading-tight font-heading line-clamp-1">
                <a href="{{ route('rooms.show', $room->id) }}">{{ $room->title }}</a>
            </h2>
        </div>
        
        <div class="flex flex-col mb-4">
            <div class="flex items-center text-slate-500 text-xs font-medium">
                <i class="fas fa-location-dot mr-2 text-indigo-500"></i>
                {{ $room->city }}
            </div>
            <!-- Distance Tag -->
            <div class="distance-tag hidden mt-1 flex items-center gap-1.5" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest"><span class="distance-km">0</span> km away</span>
            </div>
        </div>

        <!-- Facilities -->
        <div class="flex gap-3 mb-5 overflow-x-auto hide-scrollbar">
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-couch text-indigo-400 text-[10px]"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->furnishingTypeLabel() }}</span>
            </div>
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-users text-indigo-400 text-[10px]"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->tenantTypeLabel() }}</span>
            </div>
            @if($room->propertyType?->name)
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-building text-indigo-400 text-[10px]"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $room->propertyType->name }}@if($room->propertyCategory?->name) · {{ $room->propertyCategory->name }}@endif</span>
            </div>
            @endif
            @if($room->area_sqft)
            <div class="flex items-center gap-1.5 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100 whitespace-nowrap">
                <i class="fas fa-ruler-combined text-indigo-400 text-[10px]"></i>
                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ number_format((float)$room->area_sqft, 2) }} sqft</span>
            </div>
            @endif
        </div>
        
        <!-- Price & Action -->
        <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-50">
            <div class="flex flex-col">
                <span class="text-2xl font-black text-indigo-600">₹{{ number_format($room->rent) }}</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Per Month</span>
            </div>
            
            <a href="{{ route('rooms.show', $room->id) }}" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-lg active:scale-95 transition-all flex items-center gap-2">
                Details
                <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
    </div>
</div>