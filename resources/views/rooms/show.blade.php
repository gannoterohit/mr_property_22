@extends('layouts.public')

@section('title', ($room->title ?? 'Room') . ' in ' . $room->city . ' | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@section('description', 'Looking for ' . ($room->title ?? 'a room') . ' in ' . $room->city . ($room->landmarks ? ' near ' . implode(', ', $room->landmarks) : '') . '? Rent starts at ₹' . number_format($room->rent) . '. Verified listings with photos, amenities, and owner contact.')
@section('keywords', 'pg in ' . $room->city . ', room on rent in ' . $room->city . ', paying guest for ' . $room->tenantTypeLabel() . ' in ' . $room->city . ', ' . ($room->roomTypeLabel() !== 'N/A' ? $room->roomTypeLabel() : 'room') . ' in ' . $room->city . ($room->landmarks ? ', ' . implode(', ', $room->landmarks) : ''))
@section('og_title', ($room->title ?? 'Room') . ' in ' . $room->city . ' - ₹' . number_format($room->rent))
@section('og_description', Str::limit(($room->description ?? 'Find your perfect room in ' . $room->city) . ($room->landmarks ? '. Nearby: ' . implode(', ', $room->landmarks) : ''), 155))
@section('og_url', route('rooms.show', $room->id))
@section('og_image', $room->photo_url)
@section('canonical', route('rooms.show', $room->id))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@php
    $ld = [
        "@context" => "https://schema.org",
        "@type" => "Accommodation",
        "name" => ($room->title ?? 'Room') . ' in ' . $room->city,
        "description" => Str::limit($room->description ?? '', 200),
        "image" => $room->photo_url ?: asset('storage/default-room.jpg'),
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

    if (!empty($room->amenities)) {
        $ld['amenityFeature'] = array_map(function($a) {
            return ["@type" => "LocationFeatureSpecification", "name" => $a, "value" => true];
        }, $room->amenities);
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
<style>
    /* Custom animations */
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }
    
    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
    
    /* Glassmorphism effect */
    .glass {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Leaflet Map Styling */
        #roomMap {
            height: 350px;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        .leaflet-container {
            font-family: inherit;
        }
    </style>
</push>

@section('content')
<div class="room-detail-page bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen py-4">
    <div class="container mx-auto px-4 max-w-7xl">
        
        {{-- Compact Breadcrumb --}}
        <nav class="mb-4">
            <ol class="flex items-center space-x-2 text-xs text-gray-500">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600"><i class="fas fa-home mr-1"></i>Home</a></li>
                <li>›</li>
                <li><a href="{{ route('rooms.index') }}" class="hover:text-blue-600">Rooms</a></li>
                <li>›</li>
                <li class="text-gray-900 font-semibold truncate max-w-xs">{{ $room->title }}</li>
            </ol>
        </nav>

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
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                                 loading="eager"
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
                            <div class="text-center p-3 bg-orange-50 rounded-lg">
                                <div class="text-xs text-slate-700 mb-1">For</div>
                                <div class="text-sm font-bold text-orange-700 capitalize">{{ $room->tenantTypeLabel() }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 1st Ad Slot: Above Description/Amenities --}}
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
                     
                    @if(!empty($room->amenities) && is_array($room->amenities))
                        <div class="{{ $room->description ? 'border-t pt-4' : '' }}">
                            <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-600"></i>
                                Amenities
                            </h2>
                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach($room->amenities as $amenity)
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
                            <div id="roomMap" class="rounded-lg overflow-hidden shadow-lg" style="height: 250px;"></div>
                        @endif
                    </div>
                    @endif
                @else
                    <div class="bg-indigo-900 rounded-2xl overflow-hidden shadow-2xl relative min-h-[280px] flex items-center justify-center group border border-indigo-800">
                        {{-- Blurred Map Background --}}
                        <div class="absolute inset-0 z-0">
                            <div id="lockedMap" class="w-full h-full opacity-40 blur-md grayscale scale-110"></div>
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/80 to-purple-950/80"></div>
                        </div>
                        
                        <div class="relative z-10 p-6 text-center">
                            <div class="w-20 h-20 bg-white/10 backdrop-blur-xl rounded-full flex items-center justify-center mx-auto mb-5 shadow-2xl ring-4 ring-white/5 group-hover:scale-110 transition-all duration-500">
                                <i class="fas fa-lock text-4xl text-white"></i>
                            </div>
                            <h3 class="font-black text-2xl text-white mb-2 uppercase tracking-tight">Location Locked</h3>
                            <p class="text-indigo-200 text-sm mb-8 max-w-xs mx-auto leading-relaxed">
                                Unlock to see house number, street name, and get precise navigation to this property.
                            </p>
                            
<button onclick="unlockContact({{ $room->id }})"
                                class="bg-indigo-600 text-white font-black py-4 px-10 rounded-2xl hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-3 mx-auto uppercase text-sm tracking-widest shadow-xl ring-2 ring-indigo-400/50">
                                <i class="fas fa-unlock-alt"></i> Unlock Full Address
                            </button>
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
                                        <div class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-xl">
                                            <p class="text-[10px] font-black uppercase text-orange-700 tracking-widest mb-1">Brokerage Policy</p>
                                            <p class="text-xs text-orange-800 leading-tight">
                                                A professional service fee of <span class="font-bold text-lg">₹{{ $room->broker_fee }}</span> is payable to the broker only if you finalize this deal.
                                            </p>
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

@php($relatedRooms = $relatedRooms ?? collect())
@if(!request()->routeIs('admin.*') && $relatedRooms->isNotEmpty())
<section class="related-room-section">
    <div class="related-room-container">
        <div class="related-room-head"><div><span>More in {{ $room->city }}</span><h2>Similar rooms you may like</h2><p>Compare other active and approved properties in the same city.</p></div><a href="{{ route('rooms.index', ['city' => $room->city]) }}">View all rooms <i class="fas fa-arrow-right"></i></a></div>
        <div class="related-room-grid">
            @foreach($relatedRooms as $relatedRoom)
            <a href="{{ route('rooms.show', $relatedRoom) }}" class="related-room-card">
                <div class="related-room-image"><img src="{{ $relatedRoom->photo_url }}" alt="{{ $relatedRoom->title }} in {{ $relatedRoom->city }}" loading="lazy" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">@if($relatedRoom->is_featured)<span>Featured</span>@endif</div>
                <div class="related-room-copy"><div><small>{{ $relatedRoom->roomTypeLabel() }}</small><strong>&#8377;{{ number_format((float)$relatedRoom->rent) }}<em>/mo</em></strong></div><h3>{{ $relatedRoom->title }}</h3><p><i class="fas fa-location-dot"></i>{{ $relatedRoom->city }}</p><div class="related-room-meta"><span><i class="fas fa-couch"></i>{{ $relatedRoom->furnishingTypeLabel() }}</span><span><i class="fas fa-user"></i>{{ $relatedRoom->tenantTypeLabel() }}</span></div></div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<style>
.room-detail-page{padding:22px 0 42px!important;background:#f5f7fb!important}.room-detail-page>.container{max-width:1180px!important}.room-detail-page nav{margin-bottom:14px!important}.room-detail-page nav ol{color:#64748b!important}.room-detail-grid{grid-template-columns:minmax(0,1.72fr) minmax(300px,.72fr)!important;gap:18px!important}.room-detail-page .lg\:col-span-2{min-width:0}.room-detail-page .space-y-4>:not([hidden])~:not([hidden]){margin-top:14px!important}.room-detail-page .bg-white.rounded-xl{border:1px solid #e2e8f0!important;border-radius:16px!important;box-shadow:0 4px 16px rgba(15,23,42,.045)!important}.room-detail-page #mainImage{max-height:470px}.room-detail-page .h-\[300px\].lg\:h-\[450px\]{height:440px!important}.room-detail-page .bg-white.rounded-xl>.p-4{padding:18px!important}.room-detail-page .grid.grid-cols-2.lg\:grid-cols-4>div{border:1px solid #e2e8f0!important;background:#f8fafc!important;border-radius:11px!important;text-align:left!important}.room-detail-page .grid.grid-cols-2.lg\:grid-cols-4>div>div:first-child{color:#64748b!important;font-size:10px!important;font-weight:700}.room-detail-page .grid.grid-cols-2.lg\:grid-cols-4>div>div:last-child{color:#0f172a!important;font-size:14px!important}.room-detail-page h2{color:#0f172a}.room-detail-page .sticky{top:82px!important}.room-detail-page #unlock-card-mobile{border:1px solid #dbe4f0!important;border-radius:16px!important;box-shadow:0 12px 32px rgba(15,23,42,.09)!important}.room-detail-page #unlock-card-mobile>.p-4:first-child{padding:16px 18px!important;background:#0f172a!important}.room-detail-page #unlock-card-mobile>.p-4:first-child h2{color:#fff!important;font-size:13px!important}.room-detail-page #unlock-card-mobile>.p-4:last-child{padding:18px!important}.room-detail-page [class*="shadow-xl"],.room-detail-page [class*="shadow-2xl"]{box-shadow:0 4px 16px rgba(15,23,42,.05)!important}.room-detail-page .rounded-2xl{border-radius:14px!important}.room-detail-page #roomMap{border-radius:12px!important;box-shadow:none!important;border:1px solid #e2e8f0}.related-room-section{padding:0 20px 58px;background:#f5f7fb}.related-room-container{width:min(1180px,100%);margin:auto;padding-top:28px;border-top:1px solid #dbe4f0}.related-room-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:17px}.related-room-head span{color:#2563eb;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.12em}.related-room-head h2{margin:5px 0 4px;color:#0f172a;font-size:22px;font-weight:850;letter-spacing:-.5px}.related-room-head p{margin:0;color:#64748b;font-size:10px}.related-room-head>a{color:#2563eb;text-decoration:none;font-size:10px;font-weight:800}.related-room-head>a i{margin-left:5px}.related-room-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.related-room-card{overflow:hidden;border:1px solid #e2e8f0;border-radius:15px;background:#fff;text-decoration:none;box-shadow:0 3px 12px rgba(15,23,42,.035);transition:.2s}.related-room-card:hover{transform:translateY(-3px);border-color:#bfdbfe;box-shadow:0 12px 26px rgba(15,23,42,.08)}.related-room-image{position:relative;height:150px;background:#f1f5f9}.related-room-image img{width:100%;height:100%;object-fit:cover}.related-room-image span{position:absolute;top:9px;left:9px;padding:4px 6px;border-radius:6px;background:#2563eb;color:#fff;font-size:7px;font-weight:900;text-transform:uppercase}.related-room-copy{padding:13px}.related-room-copy>div:first-child{display:flex;align-items:center;justify-content:space-between;gap:8px}.related-room-copy small{color:#64748b;font-size:8px;font-weight:700}.related-room-copy strong{color:#0f172a;font-size:14px}.related-room-copy em{color:#94a3b8;font-size:8px;font-style:normal;font-weight:500}.related-room-copy h3{margin:8px 0 5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#0f172a;font-size:11px;font-weight:850}.related-room-copy>p{margin:0;color:#64748b;font-size:9px}.related-room-copy>p i{margin-right:4px;color:#f43f5e}.related-room-meta{display:flex!important;justify-content:flex-start!important;gap:6px!important;margin-top:10px}.related-room-meta span{padding:5px 6px;border-radius:6px;background:#f8fafc;color:#64748b;font-size:7px;font-weight:700}.related-room-meta i{margin-right:3px;color:#2563eb}
.room-detail-page .lg\:col-span-1>.sticky{display:flex!important;flex-direction:column!important;gap:14px!important}.room-detail-page .lg\:col-span-1>.sticky>*{order:3;margin-top:0!important}.room-detail-page .lg\:col-span-1>.sticky>#unlock-card-mobile{order:1}.room-detail-page .lg\:col-span-1>.sticky>div:first-child{order:2}
.room-detail-grid>.lg\:col-span-2,.room-detail-grid>.lg\:col-span-1{grid-column:span 1/span 1!important;min-width:0}
@media(max-width:1023px){.related-room-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:767px){.room-detail-grid{grid-template-columns:1fr!important}.room-detail-page .sticky{position:static!important}}@media(max-width:640px){.room-detail-page{padding-top:10px!important}.room-detail-page>.container{padding-left:12px!important;padding-right:12px!important}.room-detail-page .h-\[300px\].lg\:h-\[450px\]{height:300px!important}.room-detail-page #mainImage{height:300px}.related-room-section{padding:0 12px 42px}.related-room-head{align-items:flex-start;flex-direction:column}.related-room-grid{grid-template-columns:1fr 1fr;gap:9px}.related-room-image{height:118px}.related-room-copy{padding:10px}.related-room-meta{display:none!important}}
</style>

{{-- Payment Selection Modal --}}
<div id="paymentSelectionModal" class="hidden fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-[2rem] p-6 max-w-sm w-full shadow-2xl transform transition-all scale-100">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-4 text-indigo-600">
                <i class="fas fa-coins text-2xl"></i>
            </div>
            <h3 class="text-xl font-black text-gray-900">Select Payment Method</h3>
            <p class="text-gray-500 text-sm mt-1">Amount to Pay: <span id="payAmount" class="font-bold text-gray-900">₹0</span></p>
        </div>

        <div class="space-y-3">
            <button onclick="confirmPaymentSelection('wallet')" class="w-full group relative flex items-center p-4 border-2 border-gray-100 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600 mr-3 text-lg group-hover:scale-110 transition-transform">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="text-left">
                    <p class="font-bold text-gray-900 group-hover:text-indigo-700">Wallet Balance</p>
                    <p class="text-xs text-gray-500">Available: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-arrow-right ml-auto text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </button>

            <button onclick="confirmPaymentSelection('online')" class="w-full group relative flex items-center p-4 border-2 border-gray-100 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition-all">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mr-3 text-lg group-hover:scale-110 transition-transform">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="text-left">
                    <p class="font-bold text-gray-900 group-hover:text-indigo-700">Pay Online</p>
                    <p class="text-xs text-gray-500">UPI, Cards, Netbanking</p>
                </div>
                <i class="fas fa-arrow-right ml-auto text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </button>
        </div>

        <button onclick="closePaymentSelectionModal()" class="mt-6 w-full text-gray-400 font-bold text-sm hover:text-gray-600 transition">
            Cancel
        </button>
    </div>
</div>

{{-- Image Lightbox Modal --}}
<div id="lightboxModal" class="hidden fixed inset-0 bg-black/95 z-[100] flex items-center justify-center p-4 backdrop-blur-sm cursor-zoom-out" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white text-3xl hover:text-gray-300 transition-colors" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <div class="relative max-w-5xl w-full h-full flex items-center justify-center" onclick="event.stopPropagation()">
        <img id="lightboxImage" src="" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl transition-all duration-300 transform scale-95" alt="Room Photo">
        
        <button class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors" onclick="navigateLightbox(-1)">
            <i class="fas fa-chevron-left text-xl"></i>
        </button>
        <button class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/10 hover:bg-white/20 text-white rounded-full flex items-center justify-center transition-colors" onclick="navigateLightbox(1)">
            <i class="fas fa-chevron-right text-xl"></i>
        </button>
    </div>
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-black mb-4 flex items-center gap-2">
            <i class="fas fa-shield-alt text-indigo-600"></i>
            Complete Payment
        </h3>
        <div id="razorpay-container" class="mb-4 min-h-[100px] flex items-center justify-center bg-slate-50 rounded-xl border-2 border-dashed border-slate-200">
            <p class="text-slate-400 text-sm italic">Loading secure payment gateway...</p>
        </div>
        <button onclick="closePaymentModal()" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition-colors">
            Cancel Transaction
        </button>
    </div>
</div>

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
<style>
    .video-container {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        height: 0;
        overflow: hidden;
    }
    .youtube-lite-embed {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
        background-color: #000;
    }
    .youtube-thumbnail {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.2s;
    }
    .youtube-lite-embed:hover .youtube-thumbnail {
        opacity: 0.8;
    }
    .youtube-play-button {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 68px;
        height: 48px;
        background-color: rgba(255, 0, 0, 0.8);
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        transition: background-color 0.2s;
    }
    .youtube-lite-embed:hover .youtube-play-button {
        background-color: rgba(255, 0, 0, 1);
    }
</style>

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
    // Check if free or logged in
    @if(Auth::check())
        if (!feeEnabled) {
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
        
        const response = await fetch(`{{ route('rooms.featured', ':id') }}`.replace(':id', roomId), {
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
