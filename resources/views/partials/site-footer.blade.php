    <!-- Stay Updated Banner Section -->
    @if(!Route::is('home') && !Route::is('pages.*') && !Route::is('cms-pages.show') && !Route::is('admin.*') && !Route::is('owner.*') && !Route::is('complaints.*') && !Route::is('rooms.create', 'rooms.edit') && !Route::is('dashboard', 'profile.edit', 'wallet', 'referral.index', 'plans', 'unlocks.index', 'login', 'register'))
    <div class="theme-newsletter hidden lg:block border-t py-8">
        <div class="container mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="theme-newsletter-icon w-12 h-12 rounded-xl flex items-center justify-center shadow-md">
                    <i class="far fa-envelope-open text-xl"></i>
                </div>
                <div>
                    <h4 class="text-slate-900 font-bold text-lg leading-tight">Stay Updated</h4>
                    <p class="text-slate-600 text-sm">Subscribe to get updates on new rooms and offers.</p>
                </div>
            </div>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="theme-newsletter-form flex w-full max-w-md bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all">
                @csrf
                <input type="email" name="email" required placeholder="Enter your email" 
                       class="w-full bg-transparent text-slate-800 px-4 py-3 text-sm focus:outline-outline placeholder-slate-400 border-0 outline-none">
                <button type="submit" class="theme-primary-button px-6 font-bold text-sm transition-colors whitespace-nowrap">
                    Subscribe
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Redesigned Footer Section -->
    <footer class="site-footer relative text-slate-400 pt-12 pb-6 hidden lg:block overflow-hidden border-t" @if(Route::is('admin.*') || Route::is('owner.*') || Route::is('complaints.*') || Route::is('rooms.create', 'rooms.edit') || Route::is('dashboard', 'profile.edit', 'wallet', 'referral.index', 'plans', 'unlocks.index', 'login', 'register')) style="display:none !important" @endif>
        <div class="container mx-auto px-0 relative z-10" style="max-width:1280px;width:calc(100% - 32px);">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
                <!-- Brand Info (Col span 3) -->
                <div class="lg:col-span-3 space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group" aria-label="RoomRental Home">
                        @php $footerLogo = \App\Models\Setting::get('footer_logo'); @endphp
                        @if($footerLogo)
                            <img src="{{ asset('storage/' . $footerLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="footer-brand-logo">
                        @else
                            <div class="flex items-center gap-2">
                                <div class="theme-brand-mark w-10 h-10 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-home text-lg"></i>
                                </div>
                                <span class="text-xl font-black text-white tracking-tight">Apna<span class="theme-brand-text">Nest</span></span>
                            </div>
                        @endif
                    </a>
                    <p class="text-slate-400 text-xs leading-relaxed font-medium">
                        India's most trusted platform for room rentals. Connect directly with verified owners. Find your stay with zero brokerage.
                    </p>
                    <div class="flex gap-3">
@php
    if (!isset($cmsPageLive)) {
        try {
            $publishedCmsSlugs = \App\Models\CmsPage::published()->pluck('slug')->flip();
        } catch (\Throwable $exception) {
            $publishedCmsSlugs = collect();
        }
        $cmsPageLive = fn (string $slug): bool => $publishedCmsSlugs->has($slug);
    }
                            $socialLinks = [
                                'facebook-f' => \App\Models\Setting::get('facebook_url', '#'),
                                'twitter' => \App\Models\Setting::get('twitter_url', '#'),
                                'instagram' => \App\Models\Setting::get('instagram_url', '#'),
                                'linkedin-in' => \App\Models\Setting::get('linkedin_url', '#')
                            ];
                        @endphp
                        @foreach($socialLinks as $icon => $url)
                            @continue(!$url || $url === '#')
                            <a href="{{ $url }}" aria-label="Visit us on {{ ucfirst(str_replace(['-f', '-in'], '', $icon)) }}" class="theme-social-link w-8 h-8 rounded-lg bg-white/5 text-slate-400 flex items-center justify-center transition-all duration-300 border border-white/5" {{ $url != '#' ? 'target="_blank"' : '' }}>
                                <i class="fa-brands fa-{{ $icon }} text-xs" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Columns -->
                <!-- Discover -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Discover</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        <li><a href="{{ route('rooms.index') }}" class="text-slate-400 hover:text-white transition-all">Browse Rooms</a></li>
                        @php
                            $pgTypeId = \App\Models\PropertyType::where('slug', 'pg')->value('id');
                            $flatTypeId = \App\Models\PropertyType::where('slug', 'flat')->value('id');
                        @endphp
                        <li><a href="{{ $pgTypeId ? route('rooms.index', ['property_type_id' => $pgTypeId]) : route('rooms.index') }}" class="text-slate-400 hover:text-white transition-all">PG</a></li>
                        <li><a href="{{ $flatTypeId ? route('rooms.index', ['property_type_id' => $flatTypeId]) : route('rooms.index') }}" class="text-slate-400 hover:text-white transition-all">Apartments</a></li>
                        @if($cmsPageLive('how-it-works'))<li><a href="{{ route('pages.how-it-works') }}" class="text-slate-400 hover:text-white transition-all">How It Works</a></li>@endif
                        <li><a href="{{ route('plans') }}" class="text-slate-400 hover:text-white transition-all">Pricing</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Company</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        @if($cmsPageLive('about-us'))<li><a href="{{ route('pages.about') }}" class="text-slate-400 hover:text-white transition-all">About Us</a></li>@endif
                        @if($cmsPageLive('careers'))<li><a href="{{ route('pages.careers') }}" class="text-slate-400 hover:text-white transition-all">Careers</a></li>@endif
                        @if($cmsPageLive('terms-and-conditions'))<li><a href="{{ route('pages.terms') }}" class="text-slate-400 hover:text-white transition-all">Terms of Service</a></li>@endif
                        @if($cmsPageLive('privacy-policy'))<li><a href="{{ route('pages.privacy') }}" class="text-slate-400 hover:text-white transition-all">Privacy Policy</a></li>@endif
                        @if($cmsPageLive('owner-guidelines'))<li><a href="{{ route('pages.owner-guidelines') }}" class="text-slate-400 hover:text-white transition-all">Owner Guidelines</a></li>@endif
                        @if($cmsPageLive('user-guidelines'))<li><a href="{{ route('pages.user-guidelines') }}" class="text-slate-400 hover:text-white transition-all">User Guidelines</a></li>@endif
                        @if($cmsPageLive('contact-us'))<li><a href="{{ route('pages.contact') }}" class="text-slate-400 hover:text-white transition-all">Contact Us</a></li>@endif
                    </ul>
                </div>

                <!-- Support -->
                <div class="lg:col-span-2">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Support</h4>
                    <ul class="space-y-2.5 text-xs font-semibold">
                        @if($cmsPageLive('faq'))<li><a href="{{ route('pages.faq') }}" class="text-slate-400 hover:text-white transition-all">Help Center</a></li>@endif
                        @if($cmsPageLive('how-it-works'))<li><a href="{{ route('pages.how-it-works') }}" class="text-slate-400 hover:text-white transition-all">How It Works</a></li>@endif
                        @if($cmsPageLive('safety-tips'))<li><a href="{{ route('pages.safety-tips') }}" class="text-slate-400 hover:text-white transition-all">Safety Tips</a></li>@endif
                        <li><a href="{{ Auth::check() ? route('complaints.create') : route('login') }}" class="text-slate-400 hover:text-white transition-all">Report an Issue</a></li>
                    </ul>
                </div>

                @php
                    $playStoreUrl = trim((string) \App\Models\Setting::get('play_store_url'));
                    $appStoreUrl = trim((string) \App\Models\Setting::get('app_store_url'));
                @endphp
                @if($playStoreUrl || $appStoreUrl)
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Download Our App</h4>
                    <p class="mb-4 text-xs font-medium leading-relaxed text-slate-400">Get the ApnaNest app for a better room-search experience.</p>
                    <div class="flex flex-wrap gap-2">
                        @if($playStoreUrl)
                            <a href="{{ $playStoreUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/10">
                                <i class="fab fa-google-play text-base"></i><span>Google Play</span>
                            </a>
                        @endif
                        @if($appStoreUrl)
                            <a href="{{ $appStoreUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-white/15 bg-white/5 px-3 py-2 text-xs font-bold text-white transition hover:bg-white/10">
                                <i class="fab fa-apple text-lg"></i><span>App Store</span>
                            </a>
                        @endif
                    </div>
                </div>
                @else
                <!-- Contact -->
                <div class="lg:col-span-3">
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Contact</h4>
                    <ul class="space-y-3 text-xs font-semibold">
                        <li class="flex items-center gap-2 text-white">
                            <i class="theme-footer-accent fas fa-phone-alt"></i>
                            <a href="tel:{{ \App\Models\Setting::get('contact_phone', '+911234567890') }}" class="theme-footer-contact font-bold">{{ \App\Models\Setting::get('contact_phone', '+91 12345 67890') }}</a>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="theme-footer-accent far fa-envelope"></i>
                            <a href="mailto:{{ \App\Models\Setting::get('contact_email', 'support@apnanest.com') }}" class="hover:text-white transition-all">{{ \App\Models\Setting::get('contact_email', 'support@apnanest.com') }}</a>
                        </li>
                        <li class="flex items-center gap-2 text-slate-500">
                            <i class="theme-footer-accent far fa-clock"></i>
                            <span>{{ \App\Models\Setting::get('business_hours', 'Mon - Sun: 9AM - 8PM') }}</span>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

            <!-- Footer Bottom -->
            <div class="pt-6 border-t border-slate-900 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-slate-500 font-semibold">
                    &copy; {{ date('Y') }} {{ \App\Models\Setting::get('website_name', 'ApnaNest') }}. All rights reserved.
                </p>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                        <i class="fas fa-shield-alt text-emerald-500" aria-hidden="true"></i> Secure Payments
                    </span>
                    <span class="flex items-center gap-1.5 text-[11px] font-bold text-slate-500">
                        <i class="theme-footer-accent fas fa-check-circle" aria-hidden="true"></i> Verified Listings
                    </span>
                </div>
            </div>
        </div>
    </footer>
