@if($popularAreas->count())
<section class="market-section">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">{{ $text('home_areas_eyebrow','Popular neighbourhoods') }}</span><h2>{{ $text('home_areas_title','Explore places renters search most') }}</h2><p>{{ $text('home_areas_description','Compare local options before choosing your next area.') }}</p></div></div>
        <div class="market-areas">@foreach($popularAreas->take(8) as $area)<a href="{{ route('rooms.index',['city'=>$homeCity,'area'=>$area->area_name]) }}" class="market-area"><div><strong>{{ $area->area_name }}</strong><small>{{ $area->total }} rooms · from ₹{{ number_format($area->min_rent) }}</small></div><i class="fas fa-arrow-right"></i></a>@endforeach</div>
    </div>
</section>
@endif

<section class="market-section soft">
    <div class="market-wrap">
        <div class="market-section-head"><div><span class="market-kicker">Why choose {{ $siteName }}?</span><h2>Rent with confidence, every time</h2></div></div>
        <div class="market-trust-row">@foreach([['fa-shield-halved','Verified owners','Every owner is verified'],['fa-ban','No brokerage','Deal directly with owners'],['fa-phone','Direct contact','Connect instantly'],['fa-calendar-check','Secure process','Safe and transparent'],['fa-users','Trusted platform','Growing renter community'],['fa-headset','Customer support','We are here to help']] as $trust)<div><span><i class="fas {{ $trust[0] }}"></i></span><p><strong>{{ $trust[1] }}</strong><small>{{ $trust[2] }}</small></p></div>@endforeach</div>
        <div class="market-how">
            <div class="market-process"><span class="market-kicker">How it works</span><h2>Simple steps to find your next home</h2><div class="market-process-list">@foreach([['Search','Filter rooms by location, type and budget.'],['Connect','Contact owners directly.'],['Visit & Decide','Visit the place, verify and decide.']] as $i=>$step)<div class="market-step"><b>{{ $i+1 }}</b><div><strong>{{ $step[0] }}</strong><small>{{ $step[1] }}</small></div></div>@endforeach</div></div>
            <div class="market-owner"><div><span class="market-kicker">For property owners</span><h2>Have a room{{ $displayCity ? ' in '.$displayCity : '' }}?</h2><p>List your property and connect with thousands of genuine seekers.</p><ul><li><i class="fas fa-check-circle"></i> Reach genuine tenants</li><li><i class="fas fa-check-circle"></i> Simple listing process</li><li><i class="fas fa-check-circle"></i> No hidden charges</li></ul><a href="{{ route('register',['role'=>'owner']) }}">{{ $text('home_owner_button','List your property') }}</a></div><i class="fas fa-couch market-owner-art"></i></div>
        </div>
    </div>
</section>
