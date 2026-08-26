@extends('layouts.agent')

@section('title', 'List Your Property - Agent Workspace')

@section('broker-content')
<link rel="stylesheet" href="{{ asset('css/owner-rooms-create.css') }}">

<div class="owner-rooms-create-container max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('agent.dashboard') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">List Your Property</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Fill in property details, upload photos and select location to publish your listing.</p>
            </div>
        </div>
    </div>

    <!-- Listing Information Alert -->
    <div class="bg-gradient-to-r from-amber-50 to-orange-50/40 border border-amber-200/80 rounded-2xl p-4 sm:p-5 mb-6 sm:mb-8 flex items-start gap-3.5 shadow-sm">
        <div class="w-10 h-10 bg-amber-100/80 rounded-xl flex items-center justify-center text-amber-700 shrink-0 mt-0.5">
            <i class="fas fa-info-circle text-lg"></i>
        </div>
        <div class="min-w-0 flex-1">
            @if(filter_var(\App\Models\Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))
                <p class="font-bold text-amber-950 text-sm sm:text-base">Listing Fee: &#8377;{{ \App\Models\Setting::get('listing_fee', 199) }}</p>
                <p class="text-xs sm:text-sm text-amber-800/90 mt-0.5">Your property will be submitted for admin approval after payment confirmation.</p>
            @else
                <p class="font-bold text-emerald-950 text-sm sm:text-base flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Property listing is currently free
                </p>
                <p class="text-xs sm:text-sm text-emerald-800/90 mt-0.5">No payment or listing-plan credit will be used. Admin approval will be requested on submit.</p>
            @endif
        </div>
    </div>

    <form id="roomForm" enctype="multipart/form-data" class="owner-room-form-grid">
        @csrf
        <input type="hidden" name="listing_type" value="broker">
        
        <!-- Basic Details Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-home"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Basic Details</h3>
                    <p class="text-xs text-slate-500">Title, category, pricing, and description</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                <!-- Title -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Property Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Luxury 1BHK Apartment in Indiranagar"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                </div>

                <!-- Property Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Property Type <span class="text-rose-500">*</span></label>
                    <select name="property_type_id" id="property_type_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                        <option value="">Select property type</option>
                        @foreach(\App\Models\PropertyType::where('status', true)->orderBy('name')->get() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Property Category -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Property Category <span class="text-rose-500">*</span></label>
                    <select name="property_category_id" id="property_category_id" data-selected-category-id="{{ old('property_category_id') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm disabled:opacity-60 disabled:cursor-not-allowed" disabled>
                        <option value="">Select category</option>
                    </select>
                </div>

                <!-- Furnishing -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Furnishing <span class="text-rose-500">*</span></label>
                    <select name="furnishing_type" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                        @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                            <option value="{{ $option->id }}">{{ $option->label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Preferred Tenant -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Preferred Tenant <span class="text-rose-500">*</span></label>
                    <select name="tenant_type" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                        @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                            <option value="{{ $option->id }}">{{ $option->label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Monthly Rent -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Monthly Rent <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                        <input type="number" name="rent" required min="0" placeholder="e.g. 15000"
                               class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                    </div>
                </div>

                <!-- Deposit -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Deposit (Optional)</label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                        <input type="number" name="deposit" min="0" placeholder="e.g. 30000"
                               class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                    </div>
                </div>

                <!-- Area -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Area (sq ft)</label>
                    <input type="number" name="area_sqft" min="0" step="0.01" placeholder="e.g. 450"
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                </div>

                <!-- Broker Fee -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Broker Fee <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute left-4 text-slate-400 font-bold text-sm pointer-events-none">₹</span>
                        <input type="number" name="broker_fee" required min="0" placeholder="e.g. 1000"
                               class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                    </div>
                </div>

                <!-- Description -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" rows="4" placeholder="Tell us more about the property, rules, and nearby facilities..."
                              class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-medium text-slate-800 text-sm resize-none"></textarea>
                </div>
            </div>
        </div>

        <!-- Photos & Video Card -->
        <div class="bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-camera"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Photos & Video</h3>
                    <p class="text-xs text-slate-500">Showcase your property with clear media</p>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Upload Photos -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Upload Photos (Max 5) <span class="text-rose-500">*</span></label>
                    <div class="relative group">
                        <input type="file" name="photos[]" accept="image/*" multiple required
                               class="hidden" id="photosInput"
                               onchange="handlePhotosUpload(event)">
                        <label for="photosInput" class="cursor-pointer flex flex-col items-center justify-center w-full p-6 sm:p-8 border-2 border-dashed border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50/40 transition-all group text-center">
                            <div class="w-12 h-12 rounded-full bg-slate-100 group-hover:bg-indigo-100 text-slate-400 group-hover:text-indigo-600 flex items-center justify-center mb-3 transition-colors">
                                <i class="fas fa-cloud-upload-alt text-xl"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-700 group-hover:text-indigo-600 transition-colors">Select property photos</p>
                            <p class="text-xs text-slate-400 mt-1">PNG, JPG up to 5MB (Max 5 photos)</p>
                        </label>
                    </div>
                    <div id="photosPreview" class="grid grid-cols-3 gap-3 mt-4 hidden"></div>
                </div>

                <!-- Video Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Property Video (Optional)</label>
                        <input type="file" name="video" accept="video/*" class="hidden" id="videoInput" onchange="handleVideoUpload(event)">
                        <label for="videoInput" class="cursor-pointer flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 rounded-2xl hover:border-indigo-400 hover:bg-indigo-50/40 transition-all text-center group">
                            <i class="fas fa-video text-2xl text-slate-300 group-hover:text-indigo-500 transition-colors mb-1.5"></i>
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors">Add virtual tour</span>
                        </label>
                        <video id="videoPreview" src="" controls class="hidden mt-3 max-h-36 mx-auto rounded-xl w-full object-cover"></video>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Or YouTube Video URL</label>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-xs sm:text-sm">
                        <p class="text-[11px] text-slate-400 mt-1">Paste a YouTube or Vimeo link.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Common Facilities -->
        <div class="owner-form-wide bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-wifi"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Common Facilities</h3>
                    <p class="text-xs text-slate-500">Select amenities included with the property</p>
                </div>
            </div>
            <div class="facilities-grid">
                @foreach(\App\Models\RoomOption::optionsFor('amenity') as $amenityOption)
                    @php $amenity = $amenityOption->label; @endphp
                    <label class="flex items-center gap-3 p-3.5 bg-slate-50 hover:bg-indigo-50/50 rounded-2xl cursor-pointer transition-all border border-slate-200/70 hover:border-indigo-200 group">
                        <input type="checkbox" name="amenities[]" value="{{ $amenity }}" class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 shrink-0">
                        <span class="font-semibold text-slate-700 group-hover:text-indigo-900 text-xs sm:text-sm leading-normal select-none break-words">{{ $amenity }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Location Section -->
        <div class="owner-form-wide bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-6">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-map-marker-alt"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Location Information</h3>
                    <p class="text-xs text-slate-500">Search address or pick exact location on map</p>
                </div>
            </div>
            
            <div class="location-section-grid">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Search Property Location</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                            <input type="text" id="locationSearch" placeholder="Enter neighborhood or address..." 
                                   class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm">
                        </div>
                    </div>

                    <button type="button" id="getCurrentLocationBtn" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-all text-xs sm:text-sm border border-indigo-100">
                        <i class="fas fa-crosshairs"></i> Get Current Location
                    </button>

                    <div class="bg-slate-50 p-4 sm:p-5 rounded-2xl border border-slate-200/70 space-y-3">
                        <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                            <span>CITY</span>
                            <span id="city-text" class="text-indigo-600 font-bold uppercase tracking-wider">–</span>
                        </div>
                        <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                            <span>STATE</span>
                            <span id="state-text" class="text-indigo-600 font-bold uppercase tracking-wider">–</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200/80">
                             <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">SELECTED ADDRESS</p>
                             <p id="full-address-text" class="text-xs sm:text-sm font-semibold text-slate-700 leading-snug">No address selected...</p>
                        </div>
                    </div>

                    <div class="hidden">
                        <input type="text" name="country" id="countryInput">
                        <input type="text" name="state" id="stateInput">
                        <input type="text" name="city" id="cityInput">
                        <input type="text" name="address" id="location_address">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                    </div>
                </div>

                <div class="location-map-container shadow-inner">
                    <div id="map"></div>
                </div>
            </div>
        </div>

        <!-- Landmark Section (SEO) -->
        <div class="bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-compass"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Nearby Landmarks (SEO)</h3>
                    <p class="text-xs text-slate-500">Help users find you via search engines</p>
                </div>
            </div>
            <div id="landmark-container" class="flex flex-wrap gap-2 p-3 sm:p-4 bg-slate-50 rounded-2xl border border-slate-200/80 min-h-[64px] cursor-text items-center" onclick="document.getElementById('landmark-input').focus()">
                <input type="text" id="landmark-input" placeholder="Type and press Enter (e.g. IIT Delhi, Metro Station)" 
                       class="bg-transparent border-none outline-none font-semibold text-slate-800 placeholder-slate-400 text-xs sm:text-sm flex-1 min-w-[200px] px-1 py-1">
            </div>
        </div>

        @if(filter_var(\App\Models\Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))
        <!-- Payment Method -->
        <div class="bg-white rounded-3xl p-5 sm:p-8 shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base font-bold shrink-0">
                    <i class="fas fa-wallet"></i>
                </span>
                <div>
                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Payment Method</h3>
                    <p class="text-xs text-slate-500">Choose how to pay for listing fee</p>
                </div>
            </div>
            <div class="payment-options-grid">
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="online" checked class="hidden peer">
                    <div class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 transition-all flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-indigo-600 shadow-sm border border-slate-100 shrink-0">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm">Pay Online</p>
                            <p class="text-xs text-slate-500">UPI, Card, Netbanking</p>
                        </div>
                        <i class="fas fa-check-circle text-indigo-600 text-lg ml-auto opacity-0 peer-checked:opacity-100 transition-opacity shrink-0"></i>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="wallet" class="hidden peer">
                    <div class="p-4 rounded-2xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 transition-all flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-emerald-600 shadow-sm border border-slate-100 shrink-0">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-slate-900 text-sm">Wallet Balance</p>
                            <p class="text-xs text-slate-500">Available: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</p>
                        </div>
                        <i class="fas fa-check-circle text-indigo-600 text-lg ml-auto opacity-0 peer-checked:opacity-100 transition-opacity shrink-0"></i>
                    </div>
                </label>
            </div>
        </div>
        @endif

        <!-- Submit Button -->
        <div class="owner-form-wide pt-2">
            <button type="submit" 
                    class="w-full bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-extrabold py-4 sm:py-5 rounded-2xl shadow-lg shadow-indigo-200/60 hover:shadow-indigo-300 transition-all duration-200 transform active:scale-[0.99] text-base sm:text-lg flex items-center justify-center gap-3">
                <i class="fas fa-paper-plane"></i> Post Property Listing
            </button>
        </div>
    </form>
</div>
@endsection

@include('owner.rooms.partials.editor-styles')
@push('scripts')
<script>
const ROOM_PRIMARY_COLOR = '{{ \App\Models\Setting::get("primary_color", "#4F46E5") }}';
const razorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
const googleMapsKey = '{{ trim(\App\Models\Setting::get("google_maps_api_key", "")) }}';

window.gm_authFailure = function () {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        mapElement.innerHTML = '<div class="h-full min-h-[260px] flex flex-col items-center justify-center bg-slate-50 p-8 text-center"><span class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-3"><i class="fas fa-map-location-dot"></i></span><p class="font-bold text-slate-800">Map is temporarily unavailable</p><p class="mt-1 text-xs text-slate-500">You can still search and enter the property address.</p></div>';
    }
};

// Initialize Map
let map;
let marker;
let geocoder;
let autocomplete;

// Function to extract and fill address components
function fillAddressComponents(place) {
    let country = '';
    let state = '';
    let city = '';
    
    if (place.address_components) {
        for (const component of place.address_components) {
            const types = component.types;
            if (types.includes('country')) country = component.long_name;
            if (types.includes('administrative_area_level_1')) state = component.long_name;
            if (types.includes('locality')) {
                city = component.long_name;
            } else if (types.includes('administrative_area_level_2') && !city) {
                city = component.long_name;
            }
        }
    }
    
    document.getElementById('city-text').textContent = city || '–';
    document.getElementById('state-text').textContent = state || '–';
    document.getElementById('full-address-text').textContent = place.formatted_address || 'No address selected...';
    
    document.getElementById('countryInput').value = country;
    document.getElementById('stateInput').value = state;
    document.getElementById('cityInput').value = city;
    document.getElementById('location_address').value = place.formatted_address || '';
}

function updateLocation(lat, lng, address) {
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
    
    if (marker) {
        marker.setPosition({ lat, lng });
    } else {
        marker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });
        
        marker.addListener('dragend', function(event) {
            const newLat = event.latLng.lat();
            const newLng = event.latLng.lng();
            geocoder.geocode({ location: { lat: newLat, lng: newLng } }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    updateLocation(newLat, newLng, results[0].formatted_address);
                    fillAddressComponents(results[0]);
                }
            });
        });
    }
    
    map.setCenter({ lat, lng });
    map.setZoom(16);
    
    if (address) {
        document.getElementById('full-address-text').textContent = address;
        document.getElementById('location_address').value = address;
    }
}

function getLocationByIP() {
    const btn = document.getElementById('getCurrentLocationBtn');
    if (!btn) return;
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Getting location...';
    
    fetch('https://ipapi.co/json/')
        .then(response => response.json())
        .then(data => {
            if (data.latitude && data.longitude) {
                updateLocation(data.latitude, data.longitude, data.city + ', ' + data.region);
                
                document.getElementById('state-text').textContent = data.region || 'Not set';
                document.getElementById('city-text').textContent = data.city || 'Not set';
                document.getElementById('full-address-text').textContent = data.city + ', ' + data.region + ', ' + data.country_name;
                
                document.getElementById('countryInput').value = data.country_name || '';
                document.getElementById('stateInput').value = data.region || '';
                document.getElementById('cityInput').value = data.city || '';
                document.getElementById('location_address').value = data.city + ', ' + data.region + ', ' + data.country_name;
                
                btn.disabled = false;
                btn.innerHTML = originalText;
            } else {
                throw new Error('Could not determine location from IP');
            }
        })
        .catch(error => {
            console.error('Error getting location by IP:', error);
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}

function getCurrentLocation() {
    const btn = document.getElementById('getCurrentLocationBtn');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';

    // IP-based fallback (immediate but less accurate)
    getLocationByIP();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;
                
                console.log(`GPS precision: ${accuracy} meters`);

                geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                    if (status === 'OK' && results[0]) {
                        updateLocation(lat, lng, results[0].formatted_address);
                        fillAddressComponents(results[0]);
                        toastr.success('Precise location detected via GPS!');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                });
            },
            (error) => {
                console.error('GPS failed:', error);
                if (btn.disabled) {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    toastr.info('Using approximate IP-based location.');
                }
            },
            { 
                enableHighAccuracy: true, 
                timeout: 10000, 
                maximumAge: 0 
            }
        );
    } else {
        toastr.error('Geolocation is not supported by your browser.');
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

function initMap() {
    const defaultLocation = { lat: 20.5937, lng: 78.9629 };
    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLocation,
        zoom: 5,
        disableDefaultUI: true,
        zoomControl: true,
        styles: [{"featureType":"poi","stylers":[{"visibility":"off"}]}]
    });
    geocoder = new google.maps.Geocoder();

    const searchInput = document.getElementById('locationSearch');
    autocomplete = new google.maps.places.Autocomplete(searchInput, {
        componentRestrictions: { country: 'in' },
        fields: ['geometry', 'formatted_address', 'address_components']
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (place.geometry) {
            updateLocation(place.geometry.location.lat(), place.geometry.location.lng(), place.formatted_address);
            fillAddressComponents(place);
        }
    });

    map.addListener('click', (e) => {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status === 'OK' && results[0]) {
                updateLocation(lat, lng, results[0].formatted_address);
                fillAddressComponents(results[0]);
            }
        });
    });

    document.getElementById('getCurrentLocationBtn').addEventListener('click', getCurrentLocation);
}
window.initMap = initMap;

// Landmarks Logic
const landmarkInput = document.getElementById('landmark-input');
const landmarkContainer = document.getElementById('landmark-container');

function addLandmarkTag(value) {
    const tag = document.createElement('div');
    tag.className = 'bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl flex items-center gap-2 text-sm';
    const label = document.createElement('span');
    label.textContent = value;
    const remove = document.createElement('button');
    remove.type = 'button';
    remove.setAttribute('aria-label', 'Remove landmark');
    remove.innerHTML = '<i class="fas fa-times"></i>';
    remove.addEventListener('click', () => tag.remove());
    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'landmarks[]';
    hidden.value = value;
    tag.append(label, remove, hidden);
    landmarkContainer.insertBefore(tag, landmarkInput);
}

landmarkInput?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (val) {
            addLandmarkTag(val);
            this.value = '';
        }
    }
});

// Photo Previews
function handlePhotosUpload(e) {
    const preview = document.getElementById('photosPreview');
    preview.innerHTML = '';
    preview.classList.remove('hidden');
    const files = Array.from(e.target.files).slice(0, 5);
    
    files.forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = (ev) => {
            const div = document.createElement('div');
            div.className = 'relative aspect-square rounded-2xl overflow-hidden border-2 border-white shadow-sm';
            div.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function handleVideoUpload(e) {
    const video = document.getElementById('videoPreview');
    const file = e.target.files[0];
    if (file) {
        video.src = URL.createObjectURL(file);
        video.classList.remove('hidden');
    }
}

function loadPropertyCategories(propertyTypeId, selectedCategoryId = null) {
    const propertyCategorySelect = document.getElementById('property_category_id');
    if (!propertyCategorySelect) {
        return;
    }

    propertyCategorySelect.innerHTML = '<option value="">Select category</option>';
    propertyCategorySelect.disabled = true;

    if (!propertyTypeId) {
        return;
    }

    fetch(`{{ url('/api/v1/property-categories') }}?property_type_id=${encodeURIComponent(propertyTypeId)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(payload => {
        const categories = payload?.data ?? [];
        if (!Array.isArray(categories) || !categories.length) {
            propertyCategorySelect.innerHTML = '<option value="">No categories available</option>';
            return;
        }

        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            propertyCategorySelect.appendChild(option);
        });

        propertyCategorySelect.disabled = false;
        if (selectedCategoryId) {
            propertyCategorySelect.value = String(selectedCategoryId);
        }
    })
    .catch(() => {
        propertyCategorySelect.innerHTML = '<option value="">Unable to load categories</option>';
    });
}

const propertyTypeSelect = document.getElementById('property_type_id');
const propertyCategorySelect = document.getElementById('property_category_id');
if (propertyTypeSelect && propertyCategorySelect) {
    propertyTypeSelect.addEventListener('change', function () {
        loadPropertyCategories(this.value, null);
    });

    const initialPropertyTypeId = propertyTypeSelect.value;
    const initialCategoryId = propertyCategorySelect.dataset.selectedCategoryId || '';
    loadPropertyCategories(initialPropertyTypeId, initialCategoryId);
}

// Form Submission
function showRoomFormErrors(form, payload, fallback) {
    const messages = payload?.errors
        ? Object.values(payload.errors).flat().filter(Boolean)
        : [];

    window.renderFormErrors?.(form, payload?.errors || {});

    if (messages.length) {
        messages.forEach(message => toastr.error(message, 'Please check the form'));
    } else {
        toastr.error(payload?.message || fallback);
    }
}

document.getElementById('roomForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Auto-tag current landmark input if not empty
    const lInput = document.getElementById('landmark-input');
    if (lInput && lInput.value.trim()) {
        const val = lInput.value.trim();
        addLandmarkTag(val);
        lInput.value = '';
    }

    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    
    const lat = document.getElementById('latitude').value;
    if (!lat) {
        toastr.error('Please select property location on the map');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';

    const formData = new FormData(this);
    try {
        const res = await fetch('{{ route("agent.rooms.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        
        const data = await res.json();
        if (data.success) {
            if (data.subscription_used || data.free_listing || data.wallet_used || data.credits_used) {
                toastr.success(data.message || 'Property listed successfully!');
                setTimeout(() => window.location.href = '{{ route("agent.properties") }}', 1500);
            } else {
                await initiatePayment(data.payment_id, data.amount, data.room_id);
            }
        } else {
            showRoomFormErrors(this, data, 'Error creating listing');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (err) {
        toastr.error('Something went wrong. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

async function initiatePayment(paymentId, amount, roomId) {
    try {
        // Lazy load Razorpay SDK
        const Razorpay = await loadRazorpaySDK();

        const orderRes = await fetch('{{ route("razorpay.createOrder") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ payment_id: paymentId })
    });
    const order = await orderRes.json();
    
    const options = {
        key: order.key || razorpayKey,
        amount: order.amount * 100,
        currency: 'INR',
        name: '{{ \App\Models\Setting::get("website_name", "RoomRental") }}',
        order_id: order.order_id,
        handler: async function(res) {
            const verify = await fetch('{{ route("razorpay.verify") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ...res, payment_id: paymentId, type: 'listing', reference_id: roomId })
            });
            const verifyData = await verify.json();
            if (verifyData.status === 'success') {
                toastr.success('Payment successful! Your listing is active.');
                setTimeout(() => window.location.href = '{{ route("agent.properties") }}', 1500);
            } else {
                toastr.error('Verification failed');
            }
        },
        prefill: { name: '{{ auth()->user()->name }}', email: '{{ auth()->user()->email }}' },
        theme: { color: ROOM_PRIMARY_COLOR }
    };
    new Razorpay(options).open();
    } catch (error) {
    console.error('Razorpay init failed:', error);
    toastr.error('Payment initialization failed');
}
}

// Load Google Maps
const script = document.createElement('script');
script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsKey}&libraries=places&callback=initMap&loading=async`;
script.async = true;
document.head.appendChild(script);
</script>
@endpush
