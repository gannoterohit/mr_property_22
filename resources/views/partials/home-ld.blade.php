@php
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
    $siteUrl = rtrim(config('app.url') ?: url('/'), '/');
    $ld = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => $siteName,
        "url" => $siteUrl,
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => $siteUrl . '/rooms?q={search_term_string}',
            "query-input" => 'required name=search_term_string'
        ]
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
