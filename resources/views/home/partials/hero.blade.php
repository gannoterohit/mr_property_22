<section class="market-hero">
    <div class="market-hero-box">
        <div class="market-hero-slider" id="marketHeroSlider">
            @foreach($heroImages as $idx => $img)
                <div class="market-hero-slide {{ $idx === 0 ? 'is-active' : '' }}" style="background-image:url('{{ $img }}')"></div>
            @endforeach
        </div>
        <div class="market-hero-copy">
            <span class="market-eyebrow"><i class="fas fa-shield-halved"></i>100% Verified Properties · Direct Owners &amp; Trusted Agents</span>
            <h1>Find your perfect property <span>@if($displayCity)in {{ $displayCity }}@else near you @endif</span></h1>
            <p>{{ $heroDescription }}</p>
            <div class="market-benefits">
                <span><i class="fas fa-shield-halved"></i>Verified Listings</span>
                <span><i class="fas fa-house-chimney-user"></i>Direct Owners &amp; Agents</span>
                <span><i class="fas fa-unlock-keyhole"></i>Instant Unlock</span>
            </div>
        </div>
        <div class="market-city-card"><small><i class="fas fa-location-arrow"></i> Currently available in</small><strong>{{ $displayCity ?: 'Your city' }}</strong><span>More cities coming soon!</span></div>
        <div class="market-search-wrap">
            <form action="{{ route('rooms.index') }}" method="GET" class="market-search">
                <div class="market-search-grid">
                     <div class="market-field market-field-location"><i class="market-field-icon fas fa-location-dot"></i><label for="city">Location</label><input id="city" name="city" value="{{ $displayCity }}" placeholder="City or locality"></div>
                     <div class="market-field"><i class="market-field-icon fas fa-house"></i><label for="property_type_id">Property Type</label><select id="property_type_id" name="property_type_id"><option value="">Any type</option>@foreach($propertyTypes as $type)<option value="{{ $type->id }}" @selected(request('property_type_id') == $type->id)>{{ $type->name }}</option>@endforeach</select></div>
                     <div class="market-field"><i class="market-field-icon fas fa-indian-rupee-sign"></i><label for="max_rent">Budget</label><input id="max_rent" type="number" min="0" name="max_rent" placeholder="Any budget"></div>
                     <div class="market-field"><i class="market-field-icon fas fa-user"></i><label for="tenant_type">Preferred For</label><select id="tenant_type" name="tenant_type[]"><option value="">Anyone</option>@foreach(\App\Models\RoomOption::optionsFor('tenant_type') as $option)<option value="{{ $option->id }}">{{ $option->label }}</option>@endforeach</select></div>
                    <button type="submit"><i class="fas fa-magnifying-glass"></i>{{ $text('home_search_button','Search Properties') }}</button>
                </div>
            </form>
        </div>
    </div>
    <div class="market-wrap">
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
