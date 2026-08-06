@php($relatedRooms = $relatedRooms ?? collect())

@if(!request()->routeIs('admin.*') && $relatedRooms->isNotEmpty())
<section class="related-room-section">
    <div class="related-room-container">
        <div class="related-room-head"><div><span>More in {{ $room->city }}</span><h2>Similar rooms you may like</h2><p>Compare other active and approved properties in the same city.</p></div><a href="{{ route('rooms.index', ['city' => $room->city]) }}">View all rooms <i class="fas fa-arrow-right"></i></a></div>
        <div class="related-room-grid">
            @foreach($relatedRooms as $relatedRoom)
            <a href="{{ route('rooms.show', $relatedRoom) }}" class="related-room-card">
                <div class="related-room-image"><img src="{{ $relatedRoom->photo_url }}" alt="{{ $relatedRoom->title }} in {{ $relatedRoom->city }}" loading="lazy" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">@if($relatedRoom->is_featured)<span>Featured</span>@endif</div>
                <div class="related-room-copy"><div><small>{{ $relatedRoom->roomTypeLabel() }}</small><strong>&#8377;{{ number_format((float)$relatedRoom->rent) }}<em>/mo</em></strong></div><h3>{{ $relatedRoom->title }}</h3><p><i class="fas fa-location-dot"></i>{{ $relatedRoom->city }}</p><div class="related-room-meta"><span><i class="fas fa-couch"></i>{{ $relatedRoom->furnishingTypeLabel() }}</span><span><i class="fas fa-user"></i>{{ $relatedRoom->tenantTypeLabel() }}</span>@if($relatedRoom->propertyType?->name)<span><i class="fas fa-building"></i>{{ $relatedRoom->propertyType->name }}@if($relatedRoom->propertyCategory?->name) · {{ $relatedRoom->propertyCategory->name }}@endif</span>@endif@if($relatedRoom->area_sqft)<span><i class="fas fa-ruler-combined"></i>{{ number_format((float)$relatedRoom->area_sqft, 2) }} sqft</span>@endif</div></div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
