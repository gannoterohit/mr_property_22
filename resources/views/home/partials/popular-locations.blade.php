<section class="market-section soft">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">Popular locations</span><h2>Explore rooms by location</h2><p>Choose your preferred city and find verified properties near you.</p></div><a href="{{ route('rooms.index') }}">View all locations <i class="fas fa-arrow-right"></i></a></div>
        <div class="market-areas">
            @forelse($popularLocations as $location)
                <a href="{{ route('rooms.index', ['city' => $location->name]) }}" class="market-area">
                    <strong>{{ $location->name }}</strong>
                    <small>{{ $location->total }} {{ $location->total == 1 ? 'property' : 'properties' }}</small>
                    <i class="fas fa-arrow-right"></i>
                </a>
            @empty
                <div class="market-empty"><i class="fas fa-location-dot"></i><p>Popular locations will appear here as listings grow.</p></div>
            @endforelse
        </div>
    </div>
</section>
