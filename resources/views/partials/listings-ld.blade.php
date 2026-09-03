@php
    $items = [];
    $roomCollection = $rooms ?? ($featuredRooms ?? collect());
    foreach($roomCollection->take(10) as $idx => $r) {
        $nameParts = array_filter([
            $r->title ?? '',
            optional($r->propertyType)->name,
            optional($r->propertyCategory)->name,
        ]);
        $detail = array_filter([
            '@type' => 'Accommodation',
            'name' => implode(' - ', $nameParts),
            'url' => route('rooms.show', $r),
            'floorSize' => $r->area_sqft ? [
                '@type' => 'QuantitativeValue',
                'value' => (float) $r->area_sqft,
                'unitText' => 'sq ft',
            ] : null,
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $r->address,
                'addressLocality' => $r->city,
                'addressRegion' => $r->state,
                'addressCountry' => $r->country,
            ]),
        ]);
        $items[] = [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'url' => route('rooms.show', $r),
            'name' => implode(' - ', $nameParts),
            'item' => $detail,
        ];
    }
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'itemListElement' => $items
    ];
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
