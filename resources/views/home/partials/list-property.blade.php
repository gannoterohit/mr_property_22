<section class="market-section">
    <div class="market-wrap">
        <div class="market-owner">
            <div>
                <span class="market-kicker">{{ \App\Models\Setting::get('owner_cta_eyebrow', 'For property owners') }}</span>
                <h2>{{ \App\Models\Setting::get('owner_cta_title', 'Have a property to rent?') }}</h2>
                <p>{{ \App\Models\Setting::get('owner_cta_description', 'List your room, flat, PG, shop or office on ' . $siteName . ' and connect with genuine tenants or buyers directly. No middlemen, no delays.') }}</p>
                <ul>
                    @forelse($ownerCtaItems as $item)
                        <li><i class="fas {{ $item->icon ?: 'fa-check' }}"></i> {{ $item->title }}</li>
                    @empty
                        <li><i class="fas fa-check"></i> Easy listing in under 5 minutes</li>
                        <li><i class="fas fa-check"></i> Verified enquiries from real seekers</li>
                        <li><i class="fas fa-check"></i> Direct tenant contact — no broker needed</li>
                        <li><i class="fas fa-check"></i> Simple plans starting at affordable rates</li>
                    @endforelse
                </ul>
                <a href="{{ route('register', ['role' => 'owner']) }}"><i class="fas fa-plus"></i> {{ \App\Models\Setting::get('owner_cta_button_label', 'List Your Property') }}</a>
            </div>
            <div class="market-owner-art"><i class="fas fa-building"></i></div>
        </div>
    </div>
</section>
