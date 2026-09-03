@if(($homeFeatures ?? collect())->count())
<section class="market-section market-why-section">
    <div class="market-wrap">
        <div class="market-section-head">
            <div>
                <span class="market-kicker">Why choose us</span>
                <h2>Built for real room-search decisions</h2>
                <p>Helpful checks, clear details and owner-first workflows make renting simpler.</p>
            </div>
        </div>
        <div class="market-feature-grid">
            @foreach($homeFeatures as $feature)
                <article class="market-feature">
                    <span><i class="fas {{ $feature->icon ?: 'fa-circle-check' }}"></i></span>
                    <div>
                        <h3>{{ $feature->title }}</h3>
                        @if($feature->description)
                            <p>{{ $feature->description }}</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
