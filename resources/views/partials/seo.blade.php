@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
    $defaultDescription = \App\Models\Setting::get('seo_meta_description', 'Find your perfect room in your city. Browse verified room listings.');
    $defaultKeywords = \App\Models\Setting::get('seo_meta_keywords', 'room rental, apartment, house, property');
<<<<<<< HEAD
    $defaultImage = \App\Models\Setting::mediaUrl(\App\Models\Setting::get('website_logo'));
=======
    $defaultImage = asset('storage/' . (\App\Models\Setting::get('website_logo') ?? 'default-room.jpg'));
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    $siteUrl = trim((string) \App\Models\Setting::get('website_url', ''));
    $siteBase = $siteUrl !== '' ? rtrim($siteUrl, '/') : null;
    $toPublicUrl = function ($url) use ($siteBase) {
        if (!$siteBase || !$url) {
            return $url;
        }

        $parts = parse_url($url);
        if (!$parts || empty($parts['path'])) {
            return $url;
        }

        $host = $parts['host'] ?? null;
        $currentHost = parse_url(url('/'), PHP_URL_HOST);
        $configuredHost = parse_url($siteBase, PHP_URL_HOST);
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);
        if ($host && !$isLocalHost && $host !== $currentHost && $host !== $configuredHost) {
            return $url;
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        return $siteBase . $parts['path'] . $query;
    };
    $title = trim($__env->yieldContent('title')) ?: $siteName;
    $description = trim($__env->yieldContent('description')) ?: $defaultDescription;
    $keywords = trim($__env->yieldContent('keywords')) ?: $defaultKeywords;
    $canonical = $toPublicUrl(trim($__env->yieldContent('canonical')) ?: url()->current());
    $ogTitle = trim($__env->yieldContent('og_title')) ?: $title;
    $ogDescription = trim($__env->yieldContent('og_description')) ?: $description;
    $ogUrl = $toPublicUrl(trim($__env->yieldContent('og_url')) ?: $canonical);
    $ogImage = $toPublicUrl(trim($__env->yieldContent('og_image')) ?: $defaultImage);
@endphp

<!-- Canonical -->
<link rel="canonical" href="{{ $canonical }}">

<!-- Open Graph -->
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $ogTitle }}">
<meta property="og:description" content="{{ $ogDescription }}">
<meta property="og:url" content="{{ $ogUrl }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:type" content="website">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $ogTitle }}">
<meta name="twitter:description" content="{{ $ogDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">

<!-- Sitemap link -->
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ route('sitemap') }}">

@stack('head')

@php
    $gsc = \App\Models\Setting::get('google_search_console_code');
@endphp

@if($gsc)
    <meta name="google-site-verification" content="{{ $gsc }}">
@endif
