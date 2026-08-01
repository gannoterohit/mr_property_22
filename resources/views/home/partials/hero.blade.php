<section class="market-hero">
    <div class="market-wrap">
        <div class="market-hero-box">
            <div class="market-hero-image" style="background-image:url('{{ $heroImage }}')"></div>
            <div class="market-hero-copy">
                <span class="market-eyebrow"><i class="fas fa-shield-halved"></i>100% verified rooms · zero brokerage</span>
                <h1>Find your perfect room <span>@if($displayCity)in {{ $displayCity }}@else near you @endif</span></h1>
                <p>{{ $heroDescription }}</p>
                <div class="market-benefits">@foreach([1,2,3] as $benefit)<span><i class="fas {{ $text('home_why_'.$benefit.'_icon',['fa-shield-halved','fa-ban','fa-user-check'][$benefit-1]) }}"></i>{{ $text('home_why_'.$benefit.'_title',['Verified Listings','No Brokerage','Direct Owner Contact'][$benefit-1]) }}</span>@endforeach</div>
            </div>
            <div class="market-city-card"><small><i class="fas fa-location-arrow"></i> Currently available in</small><strong>{{ $displayCity ?: 'Your city' }}</strong><span>More cities coming soon!</span></div>
            <form action="{{ route('rooms.index') }}" method="GET" class="market-search">
                <div class="market-search-grid">
                    <div class="market-field market-field-location"><i class="market-field-icon fas fa-location-dot"></i><label>Location</label><input name="city" value="{{ $displayCity }}" placeholder="City or locality"></div>
                    <div class="market-field"><i class="market-field-icon fas fa-house"></i><label>Property Type</label><select name="room_type[]"><option value="">Any type</option>@foreach(\App\Models\RoomOption::optionsFor('room_type') as $option)<option value="{{ $option->id }}">{{ $option->label }}</option>@endforeach</select></div>
                    <div class="market-field"><i class="market-field-icon fas fa-indian-rupee-sign"></i><label>Budget</label><input type="number" min="0" name="max_rent" placeholder="Any budget"></div>
                    <div class="market-field"><i class="market-field-icon fas fa-user"></i><label>Preferred For</label><select name="tenant_type[]"><option value="">Anyone</option>@foreach(\App\Models\RoomOption::optionsFor('tenant_type') as $option)<option value="{{ $option->id }}">{{ $option->label }}</option>@endforeach</select></div>
                    <button type="submit"><i class="fas fa-magnifying-glass"></i>{{ $text('home_search_button','Search Rooms') }}</button>
                </div>
            </form>
        </div>
        @include('partials.offer-banner', ['placement' => 'home_hero'])
        @include('partials.adsense-slot', ['placement' => 'home_top'])
        <div class="market-stats">
            @foreach([['fa-house-circle-check',number_format($totalRooms).'+','Verified rooms'],['fa-user-check',number_format($totalOwners).'+','Verified owners'],['fa-location-dot',number_format($totalAreas).'+','Popular areas'],['fa-clock','24/7','Customer support']] as $stat)
                <div class="market-stat"><span><i class="fas {{ $stat[0] }}"></i></span><div><strong>{{ $stat[1] }}</strong><small>{{ $stat[2] }}</small></div></div>
            @endforeach
        </div>
        @if($cityContext['isFallback'])
            <div class="launch-banner">
                <div><strong>Launching soon in {{ $cityContext['launchingSoonCityName'] }}</strong><span>We're currently active in {{ $cityContext['activeCityName'] }}. Showing verified {{ $cityContext['activeCityName'] }} properties for now.</span></div>
                <a href="{{ route('rooms.index', ['city' => $cityContext['activeCityName']]) }}">View {{ $cityContext['activeCityName'] }}</a>
            </div>
        @endif
    </div>
</section>
