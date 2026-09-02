@extends('layouts.public')

@section('title', ($room->title ?? 'Room') . ' in ' . $room->city . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', 'Looking for ' . ($room->title ?? 'a property') . ' in ' . $room->city . ($room->landmarks ? ' near ' . implode(', ', $room->landmarks) : '') . '? Rent starts at ₹' . number_format($room->rent) . '. Verified listings with photos, facilities, and owner contact.')
@section('keywords', 'pg in ' . $room->city . ', room on rent in ' . $room->city . ', paying guest for ' . $room->tenantTypeLabel() . ' in ' . $room->city . ', ' . ($room->roomTypeLabel() !== 'N/A' ? $room->roomTypeLabel() : 'room') . ' in ' . $room->city . ($room->landmarks ? ', ' . implode(', ', $room->landmarks) : ''))
@section('og_title', ($room->title ?? 'Room') . ' in ' . $room->city . ' - ₹' . number_format($room->rent))
@section('og_description', Str::limit(($room->description ?? 'Find your perfect room in ' . $room->city) . ($room->landmarks ? '. Nearby: ' . implode(', ', $room->landmarks) : ''), 155))
@section('og_url', route('rooms.show', $room->id))
@section('og_image', $room->photo_url)
@section('canonical', route('rooms.show', $room->id))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@php
    $publicAmenities = $room->publicAmenities();
    $ld = [
        "@context" => "https://schema.org",
        "@type" => "Accommodation",
        "name" => ($room->title ?? 'Room') . ' in ' . $room->city,
        "description" => Str::limit($room->description ?? '', 200),
        "image" => $room->photo_url ?: asset('assets/images/default-room.svg'),
        "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => $room->city ?? '',
            "addressRegion" => $room->state ?? '',
            "addressCountry" => "IN"
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => (string) ($room->rent ?? '0'),
            "priceCurrency" => "INR",
            "availability" => ($room->status === 'active') ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        ]
    ];

    if (!empty($publicAmenities)) {
        $ld['amenityFeature'] = array_map(function($a) {
            return ["@type" => "LocationFeatureSpecification", "name" => $a, "value" => true];
        }, $publicAmenities);
    }

    if (!empty($room->latitude) && !empty($room->longitude)) {
        $ld['geo'] = [
            "@type" => "GeoCoordinates",
            "latitude" => (string) $room->latitude,
            "longitude" => (string) $room->longitude,
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}</script>
<link rel="preload" href="{{ asset('css/room-show.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/room-show.css') }}"></noscript>
</push>

@section('content')
<div class="room-detail-page bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-4">
    <div class="container mx-auto px-4 max-w-7xl">
        
        @include('rooms.partials.show.breadcrumb')

        <div class="room-detail-grid grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT COLUMN - Main Content --}}
            <div class="lg:col-span-2 space-y-4">
                
                {{-- COMPACT HERO WITH INFO --}}
                @php
                    $mainPhoto = $room->photo ?? ($room->photos && count($room->photos) > 0 ? $room->photos[0] : null);
                @endphp
                
                <div class="bg-white rounded-xl overflow-hidden shadow-xl">
                    {{-- Image Section - Reduced Height --}}
                    @if($room->photos && count($room->photos) > 0)
                        <div class="relative h-[300px] lg:h-[450px] overflow-hidden group cursor-zoom-in" onclick="openLightbox(0)">
                             <img src="{{ $room->photo_url }}"
                                  alt="{{ $room->title }} in {{ $room->city }} - Property Details"
                                  id="mainImage"
                                  width="800"
                                  height="600"
                                  class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                  loading="eager"
                                  decoding="async"
                                  onerror="this.onerror=null; this.src='https://placehold.co/800x400?text=No+Image';">
                             
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                             
                            @if($room->is_featured)
                                <span class="absolute top-3 right-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg">
                                    <i class="fas fa-star"></i> Featured
                                </span>
                            @endif

                            {{-- Wishlist Toggle --}}
                            <div class="absolute top-3 left-3 flex flex-col gap-2">
                                <button onclick="toggleWishlist({{ $room->id }})"
                                        class="w-10 h-10 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white/50 transition-all shadow-lg active:scale-90"
                                        id="wishlist-btn-{{ $room->id }}"
                                        aria-label="Toggle wishlist for {{ $room->title }}">
                                    <i class="{{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'fas' : 'far' }} fa-heart text-xl {{ (Auth::check() && Auth::user()->hasInWishlist($room->id)) ? 'text-red-500' : '' }}" aria-hidden="true"></i>
                                </button>
                                 
                                <a href="https://api.whatsapp.com/send?text={{ rawurlencode('Check out this room: ' . $room->title . ' at ' . route('rooms.show', $room->id)) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-10 h-10 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-green-500 transition-all shadow-lg active:scale-90"
                                   aria-label="Share {{ $room->title }} on WhatsApp">
                                    <i class="fa-brands fa-whatsapp text-xl" aria-hidden="true"></i>
                                </a>
                                 
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('rooms.show', $room->id)) }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="w-10 h-10 bg-white/30 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition-all shadow-lg active:scale-90"
                                   aria-label="Share {{ $room->title }} on Facebook">
                                    <i class="fa-brands fa-facebook-f text-lg" aria-hidden="true"></i>
                                </a>
                            </div>
                             
                            {{-- Info Overlay --}}
                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                <h1 class="text-2xl lg:text-3xl font-black mb-1">{{ $room->title }}</h1>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-orange-400"></i> {{ $room->city }}</span>
                                    
                                    @if($room->listing_type === 'broker')
                                        <span style="background-color: #f97316 !important;" class="text-white px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border border-white/20 shadow-lg">
                                            Broker Fee: ₹{{ $room->broker_fee }}
                                        </span>
                                    @else
                                        <span class="bg-emerald-600 text-white px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border border-emerald-400 shadow-lg">
                                            No Broker Fee
                                        </span>
                                    @endif

                                    <span class="distance-tag hidden px-2 py-0.5 bg-white/20 backdrop-blur-md rounded-full text-[10px] font-bold" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                        <i class="fas fa-walking mr-1"></i><span class="distance-km">...</span> km away
                                    </span>
                                    <span class="capitalize bg-white/10 px-2 py-0.5 rounded-full text-[10px] font-medium border border-white/20 tracking-wide">{{ $room->roomTypeLabel() }}</span>
                                </div>
                            </div>
                        </div>
                         
                        {{-- Compact Thumbnail Gallery --}}
                        @if(count($room->photo_urls) > 1)
                        <div class="flex gap-3 p-3 bg-slate-50 overflow-x-auto hide-scrollbar">
                            @foreach($room->photo_urls as $index => $photoUrl)
                                <div class="flex-shrink-0 w-24 h-24 rounded-xl overflow-hidden cursor-pointer hover:ring-2 ring-indigo-500 transition-all shadow-sm border border-white"
                                     onclick="openLightbox({{ $index }})">
                                    <img src="{{ $photoUrl }}"
                                         alt="Gallery {{ $index + 1 }}"
                                         class="w-full h-full object-cover"
                                         loading="lazy"
                                         onerror="this.src='https://placehold.co/100?text=No+Image';">
                                </div>
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="h-[300px] bg-gray-200 flex items-center justify-center">
                            <div class="text-center">
                                <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-600">No Images</p>
                            </div>
                        </div>
                    @endif
                     
                    {{-- Compact Info Grid --}}
                    <div class="p-4 border-t">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">Rent</div>
                                <div class="text-xl font-black text-blue-600">₹{{ number_format($room->rent) }}</div>
                            </div>
                            @if($room->deposit)
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">Deposit</div>
                                <div class="text-xl font-black text-green-600">₹{{ number_format($room->deposit) }}</div>
                            </div>
                            @endif
                            <div class="text-center p-3 bg-purple-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">Furnishing</div>
                                <div class="text-sm font-bold text-purple-700 capitalize">{{ $room->furnishingTypeLabel() }}</div>
                            </div>
                            <div class="text-center p-3 bg-slate-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">Type</div>
                                <div class="text-sm font-bold text-slate-700">
                                    {{ $room->propertyType?->name ?? 'N/A' }}
                                    @if($room->propertyCategory?->name) / {{ $room->propertyCategory->name }} @endif
                                    @if($room->roomTypeLabel() !== 'N/A') / {{ $room->roomTypeLabel() }} @endif
                                </div>
                            </div>
                            <div class="text-center p-3 bg-cyan-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">Area</div>
                                <div class="text-xl font-black text-cyan-600">{{ $room->area_sqft ? number_format((float)$room->area_sqft, 2) . ' sqft' : 'N/A' }}</div>
                            </div>
                            <div class="text-center p-3 bg-orange-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">For</div>
                                <div class="text-sm font-bold text-orange-700 capitalize">{{ $room->tenantTypeLabel() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 1st Ad Slot: Above Description/Facilities --}}
                <div class="mb-4">
                     @include('partials.adsense-slot', ['placement' => 'room_content'])
                </div>

                {{-- COMBINED AMENITIES & DESCRIPTION --}}
                <div class="bg-white rounded-xl p-4 shadow-xl">
                    @if($room->description)
                        <div class="mb-4">
                            <h2 class="text-lg font-bold mb-2 flex items-center gap-2">
                                <i class="fas fa-align-left text-blue-600"></i>
                                Description
                            </h2>
                            <p class="text-gray-700 text-sm leading-relaxed">{{ $room->description }}</p>
                        </div>
                    @endif
                     
                    @if(!empty($publicAmenities))
                        <div class="{{ $room->description ? 'border-t pt-4' : '' }}">
                            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                Facilities
                            </h2>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($publicAmenities as $amenity)
                                <div class="flex items-center gap-2 text-sm p-2 bg-gradient-to-r from-blue-50 to-purple-50 rounded">
                                    <i class="fas fa-check text-green-500 text-xs"></i>
                                    <span class="text-gray-700">{{ $amenity }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- NEARBY LANDMARKS Section --}}
                @if($room->landmarks && count($room->landmarks) > 0)
                <div class="bg-white rounded-xl p-5 shadow-xl border-l-4 border-indigo-500">
                    <h2 class="text-xl font-black mb-4 flex items-center gap-2">
                        <i class="fas fa-university text-indigo-600"></i>
                        Nearby Landmarks
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($room->landmarks as $landmark)
                        <div class="bg-indigo-50 text-indigo-700 px-4 py-3 rounded-xl text-sm font-bold flex items-center gap-3 border border-indigo-100 group hover:bg-indigo-100 transition-all duration-300">
                             <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                <i class="fas fa-map-pin text-indigo-500"></i>
                             </div>
                             {{ $landmark }}
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- VIDEO (if exists) --}}
                @if($room->video || $room->video_url)
                <div class="bg-white rounded-xl p-4 shadow-xl">
                    <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                        <i class="fas fa-video text-pink-600"></i>
                        Video Tour
                    </h2>
                    @if($room->video)
                        <video src="{{ asset('storage/'.$room->video) }}" controls class="w-full rounded-lg" style="max-height: 400px;"></video>
                    @elseif($room->video_url)
                        @php
                            $videoId = null;
                            $isYouTube = false;
                            if (str_contains($room->video_url, 'youtube.com/watch?v=')) {
                                $videoId = explode('v=', $room->video_url)[1];
                                $videoId = explode('&', $videoId)[0]; // Handle additional parameters
                                $isYouTube = true;
                            } elseif (str_contains($room->video_url, 'youtu.be/')) {
                                $videoId = explode('youtu.be/', $room->video_url)[1];
                                $isYouTube = true;
                            }
                        @endphp
                        @if($isYouTube && $videoId)
                            <!-- =================================================================
                            <!-- IMPORTANT: Lite YouTube Embed (No Third-Party Cookies)      
                            <!-- =================================================================
                            -->
                            <div class="video-container">
                                <div class="youtube-lite-embed" data-video-id="{{ $videoId }}">
                                    <img class="youtube-thumbnail" src="{{ route('youtube.proxy', ['videoId' => $videoId]) }}" alt="YouTube video thumbnail for {{ $room->title }}">
                                    <div class="youtube-play-button">
                                        <i class="fas fa-play"></i>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Fallback for Vimeo or other video types --}}
                            @php
                                $embedUrl = $room->video_url;
                                if (str_contains($embedUrl, 'vimeo.com/')) {
                                    $embedUrl = str_replace('vimeo.com/', 'player.vimeo.com/video/', $embedUrl);
                                }
                            @endphp
                            <div class="aspect-video w-full rounded-lg overflow-hidden shadow-inner bg-gray-100">
                                <iframe src="{{ $embedUrl }}"
                                        class="w-full h-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen>
                                </iframe>
                            </div>
                        @endif
                    @endif
                </div>
                @endif
            </div>

            {{-- RIGHT COLUMN - Compact Sidebar --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4 space-y-4">

                    {{-- 2nd Ad Slot: Top of Sidebar --}}
                    <div>
                         @include('partials.adsense-slot', ['placement' => 'room_sidebar'])
                    </div>
                    
                    {{-- COMPACT CONTACT CARD --}}
                    <div id="unlock-card-mobile" class="bg-white rounded-xl shadow-2xl overflow-hidden">
                        <div class="p-4 text-white" style="background-color: var(--primary);">
                            <h2 class="font-bold flex items-center gap-2">
                                <i class="fas fa-user-circle text-lg"></i>
                                {{ $room->listing_type === 'broker' ? 'Contact Verified Broker' : 'Contact Direct Owner' }}
                            </h2>
                        </div>
                         
                        <div class="p-4">
                            @php
                                $ownerPhoneRaw = trim((string) ($room->owner?->phone ?? ''));
                                $ownerPhoneDigits = preg_replace('/\D+/', '', $ownerPhoneRaw);
                                $localPhoneDigits = strlen($ownerPhoneDigits) === 12 && str_starts_with($ownerPhoneDigits, '91')
                                    ? substr($ownerPhoneDigits, 2)
                                    : $ownerPhoneDigits;
                                $hasOwnerPhone = strlen($localPhoneDigits) >= 6;
                                $maskedOwnerPhone = $hasOwnerPhone
                                    ? substr($localPhoneDigits, 0, 2) . str_repeat('*', max(4, strlen($localPhoneDigits) - 4)) . substr($localPhoneDigits, -2)
                                    : '98******42';
                            @endphp
                            @if($isUnlocked)
                                <div class="bg-green-50 border-2 border-green-300 rounded-lg p-3 mb-3">
                                    <p class="font-bold text-green-800 mb-2 text-sm"><i class="fas fa-unlock-alt mr-1"></i> {{ $room->listing_type === 'broker' ? 'Free Contact' : 'Unlocked' }}</p>
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-1">
                                                {{ $room->listing_type === 'broker' ? 'Broker Contact' : 'Direct Contact' }}
                                            </p>
                                            <div class="flex flex-col gap-2">
                                                @if($hasOwnerPhone)
                                                    <a href="tel:{{ $ownerPhoneDigits }}" class="flex items-center justify-center bg-blue-600 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-blue-700 transition shadow-md">
                                                        <i class="fas fa-phone-alt mr-2"></i> {{ $room->listing_type === 'broker' ? 'Call Broker' : 'Call Owner' }}
                                                    </a>
                                                    <div class="rounded-xl border border-blue-200 bg-white px-4 py-3 text-center">
                                                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $room->listing_type === 'broker' ? 'Broker phone number' : 'Owner phone number' }}</p>
                                                        <a href="tel:{{ $ownerPhoneDigits }}" class="mt-1 block text-lg font-extrabold tracking-wide text-blue-700">{{ $ownerPhoneRaw }}</a>
                                                    </div>
                                                <a href="https://wa.me/{{ $ownerPhoneDigits }}?text={{ rawurlencode('Hi, I am interested in your room: ' . $room->title . ' (' . route('rooms.show', $room->id) . ')') }}"
                                                   target="_blank"
                                                   class="flex items-center justify-center bg-green-500 text-white font-bold py-2.5 px-4 rounded-xl hover:bg-green-600 transition shadow-md">
                                                    <i class="fa-brands fa-whatsapp mr-2 text-lg"></i> WhatsApp Now
                                                </a>
                                                @else
                                                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-center text-sm font-semibold text-amber-800"><i class="fas fa-circle-exclamation mr-1"></i>Phone number not provided by owner</div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($room->listing_type === 'broker')
                                        <div class="mt-4 p-3.5 bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-200 rounded-2xl shadow-sm">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-orange-600 text-white text-xs"><i class="fas fa-shield-check"></i></span>
                                                <div class="min-w-0">
                                                    <p class="text-xs font-black text-orange-950 truncate">{{ $room->owner?->agency_name ?: ($room->owner?->name . ' (Agent)') }}</p>
                                                    @if($room->owner?->broker_license)
                                                        <p class="text-[10px] text-orange-700 font-semibold">Lic: {{ $room->owner->broker_license }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="pt-2 border-t border-orange-200/60 text-xs text-orange-900 leading-tight">
                                                <span class="font-bold">Brokerage:</span> {{ $room->broker_fee ? '₹' . number_format($room->broker_fee) : 'As per agreement' }} <span class="text-[10px] text-orange-700 block mt-0.5">(Payable only after deal finalization)</span>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="pt-2 border-t border-green-100">
                                            <p class="text-xs text-gray-500">Email Reference</p>
                                            <p class="font-medium text-gray-700 text-xs truncate">{{ $room->owner?->email ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mb-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Owner mobile number</p>
                                    <p class="mt-1 text-xl font-extrabold tracking-[.16em] text-slate-700">{{ $maskedOwnerPhone }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500"><i class="fas fa-lock mr-1"></i>Unlock to view the complete number</p>
                                </div>
                                @auth
                                    @php
                                        $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())->where('status', 'active')->whereDate('end_date', '>=', today())->whereHas('plan', fn ($q) => $q->where('type', 'user')->where('is_active', true))->with('plan')->first();
                                        $subscriptionRemaining = 0;
                                        if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                                            $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                                            $subscriptionRemaining = max(0, ($activeSubscription->plan->contacts_limit ?? 0) - $usedContacts);
                                        }
                                    @endphp
                                     
                                    @if($subscriptionRemaining > 0)
                                        <div class="bg-green-100 p-2 rounded mb-3 text-xs text-green-800">
                                            <i class="fas fa-crown mr-1"></i> {{ $subscriptionRemaining }} contacts left
                                        </div>
                                    @endif

                                    @if(!filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))
                                        <div class="bg-emerald-50 border border-emerald-100 p-2.5 rounded-xl mb-3 text-xs text-emerald-800 flex items-center gap-2">
                                            <i class="fas fa-unlock text-emerald-600"></i>
                                            <span>Contact unlock is currently <strong>free</strong>.</span>
                                        </div>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-bold py-2.5 px-4 rounded-lg hover:shadow-lg hover:opacity-90 transition text-sm"
                                                style="background-color: var(--primary);">
                                            <i class="fas fa-unlock mr-2"></i>Unlock Contact Free
                                        </button>
                                    @elseif(auth()->user()->free_unlocks > 0)
                                        <div class="bg-indigo-50 border border-indigo-100 p-2.5 rounded-xl mb-3 text-xs text-indigo-800 flex items-center gap-2">
                                            <i class="fas fa-gift text-indigo-600"></i>
                                            <span>You have <strong>{{ auth()->user()->free_unlocks }}</strong> free unlock credit{{ auth()->user()->free_unlocks > 1 ? 's' : '' }}!</span>
                                        </div>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-bold py-2.5 px-4 rounded-lg hover:shadow-lg hover:opacity-90 transition text-sm flex items-center justify-center gap-2"
                                                style="background-color: #4f46e5;">
                                            <i class="fas fa-unlock"></i> Unlock with Free Credit
                                        </button>
                                    @else
                                        <p class="text-sm text-gray-600 mb-3">Unlock for <span class="font-bold text-blue-600">₹{{ \App\Models\Setting::get('unlock_fee', 49) }}</span></p>
                                        <button onclick="unlockContact({{ $room->id }})"
                                                class="w-full text-white font-bold py-2.5 px-4 rounded-lg hover:shadow-lg hover:opacity-90 transition text-sm"
                                                style="background-color: var(--primary);">
                                            <i class="fas fa-unlock mr-2"></i>Unlock Contact
                                        </button>
                                    @endif
                                    <a href="{{ route('plans') }}" class="block mt-2 text-center text-xs text-purple-600 hover:underline">
                                        <i class="fas fa-star mr-1"></i>View Plans
                                    </a>
                                @else
                                    <a href="{{ route('login') }}"
                                       class="block w-full text-white text-center font-bold py-2.5 px-4 rounded-lg hover:shadow-lg hover:opacity-90 transition text-sm"
                                       style="background-color: var(--primary);">
                                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Unlock
                                    </a>
                                @endauth
                            @endif

                            <div class="border-t pt-3 mt-3">
                                <p class="text-xs text-gray-500">Listed by</p>
                                <p class="font-bold text-gray-900">{{ $room->owner?->name ?? 'Unknown' }}</p>
                                <a href="{{ Auth::check() ? route('complaints.create', ['room' => $room->id]) : route('login') }}" class="mt-3 inline-flex items-center gap-2 text-xs font-bold text-red-600 hover:text-red-700">
                                    <i class="fas fa-flag"></i> Report this listing
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- COMPACT LOCATION --}}
                    @if($isUnlocked || ($isOwner ?? false))
                        @if($room->address || ($room->latitude && $room->longitude))
                        <div class="bg-white rounded-xl p-4 shadow-xl">
                            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-map-marked-alt text-red-600"></i>
                                Location
                            </h2>
                            @if($room->address)
                                <div class="bg-red-50 rounded-lg p-3 mb-3 relative overflow-hidden">
                                    <div class="distance-tag hidden absolute top-0 right-0 bg-red-600 text-white px-3 py-1 rounded-bl-xl text-[10px] font-black shadow-lg" data-lat="{{ $room->latitude }}" data-lng="{{ $room->longitude }}">
                                        <i class="fas fa-location-arrow mr-1"></i><span class="distance-km">...</span> km away
                                    </div>
                                    @if($room->latitude && $room->longitude)
                                        <a href="https://www.google.com/maps?q={{ $room->latitude }},{{ $room->longitude }}"
                                           target="_blank"
                                           class="text-gray-800 font-semibold flex items-center gap-2 hover:text-red-600 transition group text-sm">
                                            <i class="fas fa-location-dot text-red-500"></i>
                                            <span class="group-hover:underline">{{ $room->address }}, {{ $room->city }}</span>
                                            <i class="fas fa-external-link-alt text-xs"></i>
                                        </a>
                                    @else
                                        <p class="text-sm"><i class="fas fa-map-marker-alt text-red-500 mr-2"></i>{{ $room->address }}, {{ $room->city }}</p>
                                    @endif
                                </div>
                            @endif
                            @if($room->latitude && $room->longitude)
                                <div id="roomMap" class="rounded-lg overflow-hidden shadow-lg" style="height: 220px;"></div>
                            @elseif($room->address || $room->city)
                                <iframe
                                    class="rounded-lg overflow-hidden shadow-lg border border-slate-200"
                                    style="height:220px;width:100%;"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.google.com/maps?q={{ urlencode(trim(($room->address ? $room->address . ', ' : '') . ($room->city ?? '') . ', ' . ($room->state ?? '') . ', ' . ($room->country ?? 'India'))) }}&output=embed">
                                </iframe>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="bg-indigo-900 rounded-2xl overflow-hidden shadow-2xl relative min-h-[260px] flex items-center justify-center group border border-indigo-800">
                            <div class="absolute inset-0 z-0">
                                <div id="lockedMap" class="w-full h-full opacity-40 blur-md grayscale scale-110"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/80 to-purple-950/80"></div>
                            </div>

                            <div class="relative z-10 p-6 text-center">
                                <div class="w-16 h-16 bg-white/10 backdrop-blur-xl rounded-full flex items-center justify-center mx-auto mb-4 shadow-2xl ring-4 ring-white/5 group-hover:scale-110 transition-all duration-500">
                                    <i class="fas fa-lock text-3xl text-white"></i>
                                </div>
                                <h3 class="font-black text-xl text-white mb-2 uppercase tracking-tight">Location Locked</h3>
                                <p class="text-indigo-200 text-xs mb-6 max-w-xs mx-auto leading-relaxed">
                                    Unlock to see house number, street name, and precise navigation.
                                </p>

                                <button onclick="unlockContact({{ $room->id }})"
                                        class="bg-indigo-600 text-white font-black py-3 px-6 rounded-xl hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2 mx-auto uppercase text-xs tracking-widest shadow-xl ring-2 ring-indigo-400/50">
                                    <i class="fas fa-unlock-alt"></i> Unlock Address
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- COMPACT FEATURED CARD --}}
                    @auth
                        @if(Auth::id() === $room->user_id && !$room->is_featured)
                        <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl p-4 text-white shadow-xl">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-rocket text-xl"></i>
                                <h3 class="font-bold">Feature This</h3>
                            </div>
                            <p class="text-xs mb-3 text-white/90">10x visibility for ₹{{ \App\Models\Setting::get('featured_fee', 99) }}</p>
                            <button onclick="makeFeatured({{ $room->id }})"
                                    class="w-full bg-white text-yellow-600 font-bold py-2 px-4 rounded-lg hover:bg-yellow-50 transition text-sm">
                                <i class="fas fa-star mr-2"></i>Make Featured
                            </button>
                        </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

@include('rooms.partials.show.related-rooms')


@include('rooms.partials.show.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    window.trackRoomNestEvent?.('ViewContent', {
        content_type: 'room',
        content_ids: [@json((string) $room->id)],
        content_name: @json($room->title),
        city: @json($room->city),
        value: {{ (float) $room->rent }},
        currency: 'INR'
    });
});
</script>
<script>
    let currentLightboxIndex = 0;
    const lightboxImages = {!! json_encode($room->photo_urls) !!};

    function openLightbox(index) {
        currentLightboxIndex = index;
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImage');
        img.src = lightboxImages[currentLightboxIndex];
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            img.classList.remove('scale-95');
            img.classList.add('scale-100');
        }, 10);
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImage');
        img.classList.add('scale-95');
        img.classList.remove('scale-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 300);
    }

    function navigateLightbox(direction) {
        currentLightboxIndex = (currentLightboxIndex + direction + lightboxImages.length) % lightboxImages.length;
        const img = document.getElementById('lightboxImage');
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = lightboxImages[currentLightboxIndex];
            img.style.opacity = '1';
        }, 200);
    }

    // Close on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
</script>
<!-- =================================================================
<!-- IMPORTANT: CSS and JS for Lite YouTube Embed                        
<!-- =================================================================
-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const liteEmbeds = document.querySelectorAll('.youtube-lite-embed');

    liteEmbeds.forEach(embed => {
        embed.addEventListener('click', function() {
            const videoId = this.dataset.videoId;
            
            // Create the iframe element
            const iframe = document.createElement('iframe');
            iframe.setAttribute('width', '100%');
            iframe.setAttribute('height', '100%');
            iframe.setAttribute('src', `https://www.youtube.com/embed/${videoId}?autoplay=1`);
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');

            // Replace the placeholder div with the iframe
            this.parentNode.replaceChild(iframe, this);
        });
    });
});
</script>

<script>
const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';

let currentActionDetails = null;

function openPaymentSelectionModal(amount, actionType, roomId) {
    document.getElementById('payAmount').textContent = '₹' + amount;
    currentActionDetails = { amount, actionType, roomId };
    document.getElementById('paymentSelectionModal').classList.remove('hidden');
}

function closePaymentSelectionModal() {
    document.getElementById('paymentSelectionModal').classList.add('hidden');
    currentActionDetails = null;
}

async function confirmPaymentSelection(method) {
    if (!currentActionDetails) return;
    
    const { amount, actionType, roomId } = currentActionDetails;
    closePaymentSelectionModal();

    if (actionType === 'unlock') {
        await executeUnlock(roomId, method);
    } else if (actionType === 'feature') {
        await executeFeature(roomId, method);
    }
}

async function toggleWishlist(roomId) {
    @guest
        window.location.href = '{{ route("login") }}';
        return;
    @endguest

    try {
        const response = await fetch(`{{ url('/wishlist/toggle') }}/${roomId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to toggle wishlist');
        const data = await response.json();

        if (data.success) {
            const btn = document.getElementById(`wishlist-btn-${roomId}`);
            const icon = btn.querySelector('i');
            if (data.status === 'added') {
                icon.classList.remove('far');
                icon.classList.add('fas', 'text-red-500');
            } else {
                icon.classList.remove('fas', 'text-red-500');
                icon.classList.add('far');
            }
        }
    } catch (error) {
        console.error(error);
    }
}

function unlockContact(roomId) {
    const feeEnabled = @json(filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN));
    const fee = feeEnabled ? {{ \App\Models\Setting::get('unlock_fee', 49) }} : 0;
    
    @if(Auth::check())
        @php
            $hasFreeOption = !filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN)
                || (Auth::user()->free_unlocks ?? 0) > 0
                || ($subscriptionRemaining ?? 0) > 0;
        @endphp
        const hasFreeOption = @json($hasFreeOption);
        if (!feeEnabled || hasFreeOption) {
            executeUnlock(roomId, 'free');
        } else {
            openPaymentSelectionModal(fee, 'unlock', roomId);
        }
    @else
        window.location.href = '{{ route("login") }}';
    @endif
}

async function executeUnlock(roomId, paymentMethod) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        // Add payment_method to URL params or Body? Body is better for POST
        const response = await fetch(`{{ route('unlock.contact', ':id') }}`.replace(':id', roomId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_method: paymentMethod }),
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('CSRF token mismatch. Please refresh the page and try again.');
            }
            const errorData = await response.json().catch(() => ({ message: 'Failed to unlock contact' }));
            throw new Error(errorData.message || 'Failed to unlock contact');
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (data.already_unlocked || data.wallet_used || data.free_credit_used) {
                if (data.subscription_used) {
                    toastr.success(`Contact unlocked using subscription! ${data.remaining_contacts} contacts remaining.`, 'Success');
                } else if (data.free_credit_used) {
                    toastr.success(`Contact unlocked using free credit! ${data.remaining_credits} credits remaining.`, 'Success');
                } else if (data.wallet_used) {
                    toastr.success(`Contact unlocked using wallet! New Balance: ₹${data.new_balance}`, 'Success');
                } else if (data.is_owner) {
                    toastr.info('You are the owner of this room', 'Info');
                } else {
                    toastr.success('Contact details unlocked', 'Success');
                }
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                await initiatePayment(data.payment_id, data.amount, 'unlock', roomId);
            }
        } else {
            toastr.error(data.message || 'Failed to unlock', 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        toastr.error(error.message || 'Something went wrong', 'Error');
    }
}

function makeFeatured(roomId) {
    const fee = {{ \App\Models\Setting::get('featured_fee', 99) }};
    openPaymentSelectionModal(fee, 'feature', roomId);
}

async function executeFeature(roomId, paymentMethod) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        const userRole = '{{ Auth::check() ? Auth::user()->role : "" }}';
        const featuredUrl = userRole === 'broker' ? '{{ route("agent.rooms.featured", ":id") }}' : '{{ route("owner.rooms.featured", ":id") }}';
        
        const response = await fetch(featuredUrl.replace(':id', roomId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_method: paymentMethod }),
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            if (response.status === 419) {
                throw new Error('CSRF token mismatch. Please refresh the page and try again.');
            }
            const errorData = await response.json().catch(() => ({ message: 'Failed to make featured' }));
            throw new Error(errorData.message || 'Failed to make featured');
        }
        
        const data = await response.json();
        
        if (data.success) {
            if (data.free_feature || data.wallet_used) {
                toastr.success(data.message || 'Room featured successfully!', 'Success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                await initiatePayment(data.payment_id, data.amount, 'featured', roomId);
            }
        } else {
            toastr.error(data.message || 'Failed', 'Error');
        }
    } catch (error) {
        console.error('Error:', error);
        toastr.error(error.message || 'Something went wrong', 'Error');
    }
}

async function initiatePayment(paymentId, amount, type, referenceId) {
    try {
        if (type === 'unlock') {
            window.trackRoomNestEvent?.('InitiateCheckout', {
                content_type: 'room',
                content_ids: [String(referenceId)],
                value: Number(amount || 0),
                currency: 'INR'
            });
        }
        // Lazy load Razorpay SDK
        await loadRazorpaySDK();
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
        
        // Check if Razorpay key is set
        if (!razorpayKey || razorpayKey === '' || razorpayKey === 'null') {
            toastr.error('Razorpay key not configured. Please add it in Business Settings.', 'Error');
            return;
        }
        
        // Create order
        const orderResponse = await fetch('{{ route("razorpay.createOrder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ payment_id: paymentId }),
            credentials: 'same-origin'
        });
        
        if (!orderResponse.ok) {
            const errorData = await orderResponse.json().catch(() => ({ message: 'Failed to create order' }));
            throw new Error(errorData.message || 'Failed to create order');
        }
        
        const orderData = await orderResponse.json();
        
        if (!orderData.success || !orderData.order_id) {
            throw new Error(orderData.message || 'Failed to create order');
        }
        
        const options = {
            key: razorpayKey,
            amount: orderData.amount * 100,
            currency: 'INR',
            name: '{{ \App\Models\Setting::get('website_name', 'RoomRental') }}',
            description: type === 'unlock' ? 'Unlock Contact Details' : 'Feature Room',
            order_id: orderData.order_id,
            handler: async function(response) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                    
                    // Verify payment
                    const verifyResponse = await fetch('{{ route("razorpay.verify") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            ...response,
                            payment_id: paymentId,
                            type: type,
                            reference_id: referenceId
                        }),
                        credentials: 'same-origin'
                    });
                    
                    if (!verifyResponse.ok) {
                        const errorData = await verifyResponse.json().catch(() => ({ message: 'Payment verification failed' }));
                        throw new Error(errorData.message || 'Payment verification failed');
                    }
                    
                    const verifyData = await verifyResponse.json();
                    
                    if (verifyData.status === 'success') {
                        if (type === 'unlock') {
                            const conversion = verifyData.conversion_data || {};
                            window.trackRoomNestEvent?.('Purchase', {
                                content_type: 'room',
                                content_ids: [String(referenceId)],
                                value: Number(conversion.amount || amount || 0),
                                currency: 'INR'
                            });
                        }
                        toastr.success('Payment successful! Contact details unlocked.', 'Success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        toastr.error(verifyData.message || 'Payment verification failed', 'Error');
                    }
                } catch (error) {
                    console.error('Verification error:', error);
                    toastr.error(error.message || 'Payment verification failed', 'Error');
                }
            },
            prefill: {
                name: '{{ Auth::user()->name ?? "" }}',
                email: '{{ Auth::user()->email ?? "" }}'
            },
            theme: {
                color: '#2563eb'
            }
        };
        
        const razorpay = new Razorpay(options);
        
        razorpay.on('payment.failed', function(response) {
            toastr.error('Payment failed: ' + (response.error.description || 'Unknown error'), 'Payment Failed');
        });
        
        razorpay.open();
        
    } catch (error) {
        console.error('Payment error:', error);
        toastr.error('Payment initialization failed: ' + error.message, 'Error');
    }
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}

// Change Main Image
function changeMainImage(imageSrc, imageId = 'mainImage') {
    const imgElement = document.getElementById(imageId);
    if (imgElement) {
        imgElement.src = imageSrc;
    }
}
</script>

<!-- Google Maps Integration -->
<script>
const googleMapsKey = '{{ trim(\App\Models\Setting::get("google_maps_api_key", "")) }}';
const roomLat = {{ $room->latitude ?? 'null' }};
const roomLng = {{ $room->longitude ?? 'null' }};
</script>
@if($room->latitude && $room->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function initLeafletMap() {
    const coords = [{{ $room->latitude }}, {{ $room->longitude }}];
    const map = L.map('roomMap').setView(coords, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const markerIcon = L.divIcon({
        html: '<div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center border-4 border-white shadow-xl transform -translate-x-1/2 -translate-y-1/2 pulse-glow"><i class="fas fa-house-chimney text-white text-sm"></i></div>',
        className: 'custom-div-icon',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    L.marker(coords, { icon: markerIcon }).addTo(map)
     .bindPopup(`
        <div class="p-2">
            <h3 class="font-black text-indigo-700 mb-1">{{ $room->title }}</h3>
            <p class="text-xs text-slate-600 mb-2">{{ $room->city }}</p>
            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $room->latitude }},{{ $room->longitude }}" target="_blank" class="inline-block bg-indigo-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-lg no-underline hover:bg-indigo-700 transition">
                <i class="fas fa-directions mr-1"></i> Get Directions
            </a>
        </div>
     `).openPopup();
}

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('roomMap')) {
            initLeafletMap();
        }
        if (document.getElementById('lockedMap')) {
             const coords = [{{ $room->latitude ?? 22.75 }}, {{ $room->longitude ?? 75.86 }}];
             const lMap = L.map('lockedMap', {
                zoomControl: false,
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: false
            }).setView(coords, 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(lMap);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        detectUserLocation((coords) => {
            const tags = document.querySelectorAll('.distance-tag');
            tags.forEach(tag => {
                const rLat = parseFloat(tag.dataset.lat);
                const rLng = parseFloat(tag.dataset.lng);
                if (rLat && rLng) {
                    const d = calculateDistance(coords.lat, coords.lng, rLat, rLng);
                    if (d) {
                        const s = tag.querySelector('.distance-km');
                        if (s) s.textContent = d;
                        tag.classList.remove('hidden');
                    }
                }
            });
        });
    });
</script>
@endif
@if(app()->environment('production') && \App\Models\Setting::get('google_ads_enabled') == '1')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomViewLabel = '{{ \App\Models\Setting::get("google_ads_room_view_label") }}';
            if (roomViewLabel && typeof trackAdsConversion === 'function') {
                trackAdsConversion(roomViewLabel, 0, 'INR');
            }
        });
    </script>
@endif

@endpush
@endsection
