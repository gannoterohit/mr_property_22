<section class="market-section soft">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">Verified property types</span><h2>{{ $text('home_latest_title','Explore available verified rooms by type') }}</h2><p>{{ $text('home_latest_description','Browse curated verified listings grouped by property type so you can find relevant rooms faster.') }}</p></div></div>
        @if(!empty($otherRoomGroups) && $otherRoomGroups->count())
            @foreach($otherRoomGroups as $group)
                <div class="market-section-head mt-8"><div><span class="market-kicker">Also available</span><h2>More {{ $group->label }}</h2><p>Explore additional available {{ strtolower($group->label) }} listings below.</p></div><a href="{{ route('rooms.index', $group->params) }}">View every listing <i class="fas fa-arrow-right"></i></a></div>
                <div class="market-room-grid">
                    @foreach($group->rooms as $room)
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
                            @if($room->deposit)
                                <span class="market-room-price" style="font-size:12px;color:#64748b;margin-top:2px;display:block;">₹{{ number_format($room->deposit) }} deposit</span>
                            @endif
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
                @endforeach
            </div>
            @endforeach
        @endif
    </div>
</section>

