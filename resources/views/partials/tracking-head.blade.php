    <!-- Google Ads & Analytics (Production Only) -->
    @if(app()->environment('production'))
        @php
            $gaEnabled = \App\Models\Setting::get('google_ads_enabled') == '1';
            $ga4Id = \App\Models\Setting::get('ga4_measurement_id');
            $adsId = \App\Models\Setting::get('google_ads_tag_id');
            
            $adsenseEnabled = \App\Models\Setting::get('adsense_enabled') == '1';
            $adsenseId = \App\Models\Setting::get('adsense_client_id');
        @endphp

        {{-- Google Analytics & Ads --}}
        @if($ga4Id || ($gaEnabled && $adsId))
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id ?: $adsId }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                // GA4 Config (Always load if ID exists)
                @if($ga4Id)
                gtag('config', '{{ $ga4Id }}');
                @endif

                // Google Ads Config (Load only if enabled)
                @if($gaEnabled && $adsId)
                gtag('config', '{{ $adsId }}');
                @endif

                // Helper function for conversion tracking
                window.trackAdsConversion = function(label, value, currency = 'INR') {
                    @if($gaEnabled && $adsId)
                    if (!label) return;
                    gtag('event', 'conversion', {
                        'send_to': '{{ $adsId }}/' + label,
                        'value': value || 1.0,
                        'currency': currency
                     });
                    @endif
                };
            </script>
        @endif

        {{-- Google AdSense --}}
        @if($adsenseEnabled && $adsenseId)
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseId }}" crossorigin="anonymous"></script>
        @endif
    @endif

    @php
        $metaPixelEnabled = \App\Models\Setting::get('meta_pixel_enabled', '0') == '1';
        $metaPixelId = trim((string) \App\Models\Setting::get('meta_pixel_id', ''));
    @endphp
    @if(app()->environment('production') && $metaPixelEnabled && $metaPixelId !== '' && !request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*'))
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($metaPixelId));
            fbq('track', 'PageView');
            window.trackMetaPixel = function(eventName, params = {}) {
                if (typeof fbq !== 'function') return;
                fbq('track', eventName, params);
            };
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"></noscript>
    @else
        <script>
            window.trackMetaPixel = function() {};
        </script>
    @endif
    <script>
        window.trackRoomNestAnalytics = function(eventName, params = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!token || !eventName) return;

            const payload = {
                event_name: eventName,
                room_id: params.room_id || (Array.isArray(params.content_ids) ? params.content_ids[0] : null),
                payment_id: params.payment_id || null,
                city: params.city || null,
                amount: params.amount || params.value || null,
                currency: params.currency || 'INR',
                url: window.location.href,
                referrer: document.referrer || null,
                payload: params
            };

            fetch(@json(route('analytics.events.store')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            }).catch(() => {});
        };

        window.trackRoomNestEvent = function(eventName, params = {}) {
            window.trackMetaPixel?.(eventName, params);
            window.trackRoomNestAnalytics?.(eventName, params);
        };

        @unless(request()->routeIs('admin.*', 'owner.*', 'dashboard', 'profile.*', 'wallet', 'referral.*', 'wishlist.*', 'complaints.*'))
            document.addEventListener('DOMContentLoaded', function() {
                window.trackRoomNestAnalytics('PageView', {
                    city: new URLSearchParams(window.location.search).get('city')
                });
            });
        @endunless
    </script>
