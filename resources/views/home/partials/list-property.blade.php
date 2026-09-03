<section class="market-section owner-cta-section">
    <div class="market-wrap">
        <div class="owner-cta-grid">
            <div class="owner-cta-content">
                <span class="owner-cta-kicker">{{ \App\Models\Setting::get('owner_cta_eyebrow', 'For property owners') }}</span>
                <h2 class="owner-cta-title">{{ \App\Models\Setting::get('owner_cta_title', 'Have a property to rent?') }}</h2>
                <p class="owner-cta-desc">{{ \App\Models\Setting::get('owner_cta_description', 'List your room, flat, PG, shop or office on ' . $siteName . ' and connect with genuine tenants or buyers directly. No middlemen, no delays.') }}</p>
                <ul class="owner-cta-list">
                    @forelse($ownerCtaItems as $item)
                        <li><i class="fas {{ $item->icon ?: 'fa-check-circle' }}"></i> {{ $item->title }}</li>
                    @empty
                        <li><i class="fas fa-check-circle"></i> Easy listing in under 5 minutes</li>
                        <li><i class="fas fa-check-circle"></i> Verified enquiries from real seekers</li>
                        <li><i class="fas fa-check-circle"></i> Direct tenant contact — no broker needed</li>
                        <li><i class="fas fa-check-circle"></i> Simple plans starting at affordable rates</li>
                    @endforelse
                </ul>
                <a href="{{ route('register', ['role' => 'owner']) }}" class="owner-cta-button">
                    <i class="fas fa-plus"></i> {{ \App\Models\Setting::get('owner_cta_button_label', 'List Your Property') }}
                </a>
            </div>
            <div class="owner-cta-image">
                @php $ownerCtaImage = \App\Models\Setting::mediaUrl(\App\Models\Setting::get('owner_cta_image')); @endphp
                @if($ownerCtaImage)
                    <img src="{{ $ownerCtaImage }}" alt="Property" loading="lazy" onerror="this.style.display='none'">
                @endif
                <i class="fas fa-building" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:48px;color:rgba(255,255,255,.25);z-index:1;pointer-events:none;"></i>
            </div>
        </div>
    </div>
</section>
