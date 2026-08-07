<section class="market-section soft">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">Freshly added</span><h2>{{ $text('home_latest_title','Latest verified rooms') }}</h2><p>{{ $text('home_latest_description','Genuine listings with clear rent, photos and property details.') }}</p></div><a href="{{ route('rooms.index') }}">View every listing <i class="fas fa-arrow-right"></i></a></div>
        <div class="market-room-grid">
            @forelse($rooms->take(4) as $room)
                <a href="{{ route('rooms.show',$room) }}" class="market-room">
                    <div class="market-room-photo relative">
                        @if($room->photo_url)
                            <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" loading="lazy">
                        @endif
                        <div class="absolute top-3 left-3 flex flex-col gap-1 z-10">
                            @if($room->propertyType?->name)
                                <span class="bg-slate-900 text-white text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-full">{{ $room->propertyType->name }}</span>
                            @endif
                            @if($room->propertyCategory?->name)
                                <span class="bg-indigo-600 text-white text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded-full">{{ $room->propertyCategory->name }}</span>
                            @endif
                        </div>
                        @if($room->is_featured)
                            <span class="market-room-badge">Featured</span>
                        @endif
                    </div>
                    <div class="market-room-copy">
                        <h3>{{ $room->title }}</h3>
                        <p><i class="fas fa-location-dot"></i>{{ $room->city }}</p>
                        <span class="market-room-price">₹{{ number_format($room->rent) }} <small>/month</small></span>
                        <div class="market-room-meta">
                            <span>{{ $room->roomTypeLabel() }}</span>
                            @if($room->propertyType?->name)
                                <span class="font-bold uppercase tracking-widest">{{ $room->propertyType->name }}</span>
                            @endif
                            @if($room->propertyCategory?->name)
                                <span class="font-bold uppercase tracking-widest">{{ $room->propertyCategory->name }}</span>
                            @endif
                            <span>{{ $room->furnishingTypeLabel() }}</span>
                            <span>{{ $room->tenantTypeLabel() }}</span>
                            @if($room->area_sqft)
                                <span>{{ number_format((float)$room->area_sqft, 2) }} sqft</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="market-empty"><i class="fas fa-house"></i><p>No verified listings are available yet.</p><a href="{{ route('rooms.index') }}">Browse rooms</a></div>
            @endforelse
        </div>
    </div>
</section>
