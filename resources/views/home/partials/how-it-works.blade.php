<section class="market-section">
    <div class="market-wrap">
        <div class="market-section-head" style="text-align:center;max-width:720px;margin:0 auto 40px;">
            <div>
                <span class="market-kicker">{{ \App\Models\Setting::get('hiw_seeker_eyebrow', 'How it works') }}</span>
                <h2>{{ \App\Models\Setting::get('hiw_seeker_title', 'Find your property in 3 simple steps') }}</h2>
                <p>{{ \App\Models\Setting::get('hiw_seeker_description', 'Renting or listing a property on ' . $siteName . ' is simple, transparent and designed to save you time.') }}</p>
            </div>
        </div>
        <div class="market-how">
            <div class="market-process">
                <div class="market-process-list">
                    @foreach(($hiwItems['seeker_step'] ?? collect())->take(3) as $item)
                        <div class="market-step">
                            <b>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</b>
                            <div>
                                <strong>{{ $item->title }}</strong>
                                <small>{{ $item->description }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="market-trust">
                <p style="font-size:15px;font-weight:900;margin:0 0 16px;">{{ \App\Models\Setting::get('hiw_hero_eyebrow', 'Why seekers trust') }} {{ $siteName }}</p>
                <div class="market-checks">
                    @foreach(($hiwItems['hero_feature'] ?? collect())->take(4) as $item)
                        <div class="market-check"><i class="fas {{ $item->icon ?: 'fa-circle-check' }}"></i> {{ $item->title }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
