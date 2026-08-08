@if(($testimonials ?? collect())->count())
<section class="market-section market-testimonial-section">
    <div class="market-wrap">
        <div class="market-section-head">
            <div>
                <span class="market-kicker">Testimonials</span>
                <h2>What renters and owners say</h2>
                <p>Real feedback that can be managed from the admin panel.</p>
            </div>
        </div>
        <div class="market-testimonial-grid" data-testimonial-slider>
            @foreach($testimonials as $testimonial)
                <article class="market-testimonial">
                    <div class="market-testimonial-person">
                        @if($testimonial->avatar_url)
                            <img src="{{ $testimonial->avatar_url }}" alt="{{ $testimonial->name }}" loading="lazy">
                        @else
                            <span>{{ strtoupper(substr($testimonial->name, 0, 1)) }}</span>
                        @endif
                        <div>
                            <strong>{{ $testimonial->name }}</strong>
                            <small>{{ collect([$testimonial->role, $testimonial->city])->filter()->join(' - ') }}</small>
                        </div>
                    </div>
                    <div class="market-testimonial-rating">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= (int) $testimonial->rating ? 'is-filled' : '' }}"></i>
                        @endfor
                    </div>
                    <p>{{ $testimonial->message }}</p>
                </article>
            @endforeach
        </div>
        @if($testimonials->count() > 1)
            <div class="market-testimonial-dots" data-testimonial-dots aria-label="Testimonials pagination"></div>
        @endif
    </div>
</section>
@endif
