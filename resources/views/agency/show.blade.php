@extends('layouts.public')

@php
    $agencyName = $user->agency_name ?: ($user->name . ' Real Estate');
    $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
@endphp

@section('title', $agencyName . ' - Verified Real Estate Agency | ' . $siteName)
@section('description', 'Explore verified rental properties, flats and rooms listed by ' . $agencyName . ' in ' . ($activeCities->first() ?? $user->city ?? 'India') . '.')

@section('content')
<div class="agency-profile-page bg-slate-50 min-h-screen pb-16">
    {{-- Top Agency Hero --}}
    <div class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 text-white pt-10 pb-16 px-4">
        <div class="container mx-auto max-w-6xl">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6">
                <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                <span>/</span>
                <a href="{{ route('rooms.index') }}" class="hover:text-white transition">Properties</a>
                <span>/</span>
                <span class="text-slate-200 font-bold truncate">{{ $agencyName }}</span>
            </nav>

            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    {{-- Agency Avatar / Badge --}}
                    <div class="relative w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl md:text-4xl font-black shadow-xl shrink-0 ring-4 ring-white/10">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $agencyName }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($agencyName, 0, 1)) }}
                        @endif
                        <span class="absolute -bottom-1 -right-1 w-7 h-7 bg-emerald-500 text-white rounded-full flex items-center justify-center text-xs shadow-md ring-2 ring-slate-900 z-10" title="Verified Agent">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>

                    {{-- Agency Info --}}
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white">{{ $agencyName }}</h1>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-extrabold border border-emerald-500/30">
                                <i class="fas fa-certificate text-[10px]"></i> Verified Agent
                            </span>
                        </div>

                        <p class="text-slate-300 text-xs md:text-sm font-medium flex flex-wrap items-center gap-x-4 gap-y-1">
                            <span><i class="fas fa-user-tie text-indigo-400 mr-1.5"></i>{{ $user->name }} (Primary Agent)</span>
                            @if($user->broker_license)
                                <span><i class="fas fa-id-card text-amber-400 mr-1.5"></i>Lic / RERA: {{ $user->broker_license }}</span>
                            @endif
                            @if($user->city)
                                <span><i class="fas fa-location-dot text-rose-400 mr-1.5"></i>{{ $user->city }}</span>
                            @endif
                            @if($user->agency_gst)
                                <span><i class="fas fa-file-invoice text-slate-300 mr-1.5"></i>GST: {{ $user->agency_gst }}</span>
                            @endif
                        </p>
                        @if($user->agency_address)
                            <p class="text-slate-400 text-xs mt-1.5 flex items-start gap-1.5">
                                <i class="fas fa-building text-indigo-400 mt-0.5 shrink-0"></i>
                                <span>{{ $user->agency_address }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Action CTAs --}}
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    @if(!empty($rawPhone))
                        <a href="{{ $waLink }}"
                           target="_blank"
                           rel="noopener"
                           class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-xl font-black text-sm text-white shadow-lg transition-all transform hover:-translate-y-0.5"
                           style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                            <span>Chat on WhatsApp</span>
                        </a>

                        <a href="tel:{{ $rawPhone }}"
                           class="flex-1 md:flex-initial inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-bold text-sm bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all shadow-sm">
                            <i class="fas fa-phone-alt text-xs"></i>
                            <span>Call Agency</span>
                        </a>
                    @endif

                    <button type="button" onclick="shareAgencyProfile()"
                            class="w-11 h-11 rounded-xl bg-white/10 hover:bg-white/20 active:scale-95 text-white flex items-center justify-center text-sm border border-white/20 transition cursor-pointer shadow-sm"
                            title="Share Profile Link">
                        <i class="fas fa-share-nodes"></i>
                    </button>
                </div>
            </div>

            {{-- Stat Chips --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-8 pt-6 border-t border-slate-700/60">
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Active Listings</span>
                    <span class="text-xl font-black text-white mt-0.5 block">{{ $totalListingsCount }} Properties</span>
                </div>
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Areas Covered</span>
                    <span class="text-sm font-extrabold text-white mt-0.5 block truncate">{{ $activeCities->implode(', ') ?: ($user->city ?? 'All Localities') }}</span>
                </div>
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Verified Partner</span>
                    <span class="text-sm font-extrabold text-emerald-400 mt-0.5 block"><i class="fas fa-shield-halved mr-1"></i>KYC Approved</span>
                </div>
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Brokerage Model</span>
                    <span class="text-sm font-extrabold text-amber-400 mt-0.5 block">Transparent / Regulated</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Properties Section --}}
    <div class="container mx-auto max-w-6xl px-4 mt-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl md:text-2xl font-black text-slate-900">Properties Managed by {{ $agencyName }}</h2>
                <p class="text-xs md:text-sm text-slate-500">Contact this agent directly for site visits, photos, and deal finalization.</p>
            </div>

            {{-- Filter Pills --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('agency.show', ['user' => $user->id]) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition {{ !request('sort_by') ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200' }}">
                    Featured
                </a>
                <a href="{{ route('agency.show', ['user' => $user->id, 'sort_by' => 'rent_asc']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition {{ request('sort_by') === 'rent_asc' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200' }}">
                    Price: Low to High
                </a>
                <a href="{{ route('agency.show', ['user' => $user->id, 'sort_by' => 'rent_desc']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition {{ request('sort_by') === 'rent_desc' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-200' }}">
                    Price: High to Low
                </a>
            </div>
        </div>

        @if($properties->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($properties as $room)
                    <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group">
                        {{-- Image & Badges --}}
                        <div class="relative h-48 bg-slate-100 overflow-hidden">
                            <img src="{{ $room->photo_url }}"
                                 alt="{{ $room->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy"
                                 onerror="this.src='{{ asset('assets/images/default-room.svg') }}'">
                            
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5 z-10">
                                @if($room->is_featured)
                                    <span class="px-2.5 py-1 rounded-md bg-amber-500 text-white font-black text-[10px] uppercase tracking-wider shadow-sm">
                                        <i class="fas fa-star text-[9px] mr-1"></i>Featured
                                    </span>
                                @endif
                                <span class="px-2.5 py-1 rounded-md bg-slate-900/80 backdrop-blur-md text-white font-bold text-[10px] uppercase">
                                    {{ $room->roomTypeLabel() }}
                                </span>
                            </div>

                            <div class="absolute bottom-3 right-3 px-3 py-1 rounded-lg bg-slate-900/85 backdrop-blur-md text-white shadow-md">
                                <span class="text-base font-black">₹{{ number_format($room->rent) }}</span>
                                <span class="text-[11px] text-slate-300">/mo</span>
                            </div>
                        </div>

                        {{-- Body Copy --}}
                        <div class="p-4 flex flex-col flex-1">
                            <h3 class="font-extrabold text-slate-900 text-base leading-snug line-clamp-1 mb-1 group-hover:text-indigo-600 transition-colors">
                                <a href="{{ route('rooms.show', $room) }}">{{ $room->title }}</a>
                            </h3>

                            <p class="text-xs text-slate-500 flex items-center gap-1.5 mb-3">
                                <i class="fas fa-location-dot text-rose-500"></i>
                                <span class="truncate">{{ $room->address ? $room->address . ', ' : '' }}{{ $room->city }}</span>
                            </p>

                            {{-- Specs Grid --}}
                            <div class="grid grid-cols-3 gap-2 py-2.5 border-t border-slate-100 text-center text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-semibold">Furnishing</span>
                                    <span class="font-bold text-slate-700 capitalize truncate block">{{ $room->furnishingTypeLabel() }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-semibold">For</span>
                                    <span class="font-bold text-slate-700 capitalize truncate block">{{ $room->tenantTypeLabel() }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-semibold">Area</span>
                                    <span class="font-bold text-slate-700 truncate block">{{ $room->area_sqft ? number_format((float)$room->area_sqft) . ' sqft' : 'Std' }}</span>
                                </div>
                            </div>

                            @if($room->broker_fee)
                                <div class="mt-2.5 px-2.5 py-1.5 rounded-lg bg-amber-50 border border-amber-200/80 text-[11px] text-amber-900 font-semibold flex items-center justify-between">
                                    <span>Brokerage Fee:</span>
                                    <span class="font-black">₹{{ number_format($room->broker_fee) }}</span>
                                </div>
                            @endif

                            {{-- CTA --}}
                            <div class="mt-auto pt-3 border-t border-slate-100 flex items-center gap-2">
                                <a href="{{ route('rooms.show', $room) }}"
                                   class="flex-1 text-center py-2 px-3 rounded-xl bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs transition shadow-xs">
                                    View Room
                                </a>
                                @if(!empty($rawPhone))
                                    @php
                                        $roomDigits = $digits ?? preg_replace('/\D+/', '', $rawPhone);
                                        $roomWaMsg = "Hello {$agencyName}! Maine aapka room '{$room->title}' RoomRental par dekha. Kya ye abhi available hai? " . route('rooms.show', $room);
                                        $roomWaLink = "https://wa.me/{$roomDigits}?text=" . rawurlencode($roomWaMsg);
                                    @endphp
                                    <a href="{{ $roomWaLink }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-base transition shrink-0"
                                       style="background: #25D366;"
                                       title="Chat on WhatsApp about this room">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($properties->hasPages())
                <div class="mt-8">
                    {{ $properties->links() }}
                </div>
            @endif
        @else
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm max-w-md mx-auto">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fas fa-building-circle-xmark"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-800 mb-1">No Active Properties Available</h3>
                <p class="text-xs text-slate-500 mb-4">All properties currently managed by this agency are either rented out or under lease.</p>
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-indigo-600 transition">
                    Browse Other City Rooms
                </a>
            </div>
        @endif
    </div>

    {{-- Floating Toast Notification --}}
    <div id="agency-toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 transform transition-all duration-300 opacity-0 pointer-events-none translate-y-4">
        <div class="flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-slate-950/95 text-white shadow-2xl backdrop-blur-md border border-white/10 text-xs font-bold">
            <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]"><i class="fas fa-check"></i></span>
            <span id="agency-toast-msg">Link copied to clipboard!</span>
        </div>
    </div>
</div>

<script>
function shareAgencyProfile() {
    var shareUrl = window.location.href;
    var shareTitle = @json($agencyName . ' - Verified Agency Profile');
    var shareText = @json('Explore verified rental properties managed by ' . $agencyName . ' on RoomRental: ');

    if (navigator.share) {
        navigator.share({
            title: shareTitle,
            text: shareText,
            url: shareUrl
        }).catch(function(e) {
            if (e.name !== 'AbortError') {
                copyAgencyLink(shareUrl);
            }
        });
    } else {
        copyAgencyLink(shareUrl);
    }
}

function copyAgencyLink(url) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            showAgencyToast('Agency profile link copied to clipboard!');
        });
    } else {
        var tempInput = document.createElement('input');
        tempInput.value = url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showAgencyToast('Agency profile link copied to clipboard!');
    }
}

function showAgencyToast(msg) {
    var toast = document.getElementById('agency-toast');
    var toastMsg = document.getElementById('agency-toast-msg');
    if (!toast || !toastMsg) return;
    toastMsg.textContent = msg;
    toast.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
    toast.classList.add('opacity-100', 'translate-y-0');
    setTimeout(function() {
        toast.classList.remove('opacity-100', 'translate-y-0');
        toast.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
    }, 2800);
}
</script>
@endsection
