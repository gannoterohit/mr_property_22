@extends('layouts.app')

@section('title', 'List Your Room - RoomRental')

@section('content')
<style>
    .toast-success { background-color: #51a351 !important; }
    .toast-error { background-color: #bd362f !important; }
    .toast-info { background-color: #2f96b4 !important; }
    .toast-warning { background-color: #f89406 !important; }
</style>

<div class="owner-workspace room-editor min-h-screen bg-slate-50">
    <!-- Mobile App Header -->
    <div class="lg:hidden bg-white px-4 py-4 flex items-center justify-between sticky top-0 z-40 border-b">
        <div class="flex items-center gap-3">
            <a href="{{ route('owner.dashboard') }}" class="text-gray-900">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-lg font-black text-gray-900">List Your Room</h1>
        </div>
        <div class="w-8"></div> <!-- Spacer -->
    </div>

    <div class="owner-form-shell">
        @include('owner.partials.sidebar', ['active' => 'create'])

        <!-- Main Content Area -->
        <main class="flex-1">
            <!-- Desktop Header -->
            <div class="owner-page-header owner-form-page-header hidden lg:block bg-white border-b border-slate-200">
                <div class="max-w-6xl mx-auto flex items-center justify-between">
                    <div>
                        <h1 class="font-black text-slate-950">List Your Property</h1>
                        <p class="text-slate-500">Fill in the details to reach thousands of potential tenants.</p>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="room-editor-content max-w-6xl mx-auto p-4 sm:p-6 lg:p-8 pb-12 lg:pb-16">
                <!-- Listing Information Alert -->
                <div class="bg-amber-50 border border-amber-200 rounded-[2rem] p-6 mb-8 flex items-start gap-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-2xl flex items-center justify-center text-amber-600 shrink-0">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        @if(filter_var(\App\Models\Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))
                            <p class="font-bold text-amber-900">Listing Fee: &#8377;{{ \App\Models\Setting::get('listing_fee', 199) }}</p>
                            <p class="text-sm text-amber-700">Your room will be submitted for admin approval after payment confirmation.</p>
                        @else
                            <p class="font-bold text-emerald-900">Room listing is currently free</p>
                            <p class="text-sm text-emerald-700">No payment or listing-plan credit will be used. Admin approval is still required.</p>
                        @endif
                    </div>
                </div>

                <form id="roomForm" enctype="multipart/form-data" class="owner-room-form-grid">
                    @csrf
                    
                    <!-- Basic Details -->
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-2">Basic Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Room Title</label>
                                <input type="text" name="title" required placeholder="e.g. Luxury 1BHK in Indiranagar"
                                       class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Room Type</label>
                                <select name="room_type" required class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                    @foreach(App\Models\RoomOption::optionsFor('room_type') as $option)
                                        <option value="{{ $option->id }}">{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Furnishing</label>
                                <select name="furnishing_type" required class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                    @foreach(App\Models\RoomOption::optionsFor('furnishing_type') as $option)
                                        <option value="{{ $option->id }}">{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Preferred Tenant</label>
                                <select name="tenant_type" required class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                    @foreach(App\Models\RoomOption::optionsFor('tenant_type') as $option)
                                        <option value="{{ $option->id }}">{{ $option->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Monthly Rent</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-gray-400">₹</span>
                                    <input type="number" name="rent" required min="0" placeholder="0"
                                           class="w-full bg-gray-50 border-none rounded-2xl pl-10 pr-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Deposit (Optional)</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-gray-400">₹</span>
                                    <input type="number" name="deposit" min="0" placeholder="0"
                                           class="w-full bg-gray-50 border-none rounded-2xl pl-10 pr-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Listing Type</label>
                                <select name="listing_type" id="listing_type" required onchange="toggleBrokerFee(this.value)" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                    <option value="owner">Direct Owner</option>
                                    <option value="broker">Verified Broker</option>
                                </select>
                            </div>

                            <div id="broker_fee_container" class="hidden">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Broker Fee</label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 font-bold text-gray-400">₹</span>
                                    <input type="number" name="broker_fee" id="broker_fee" min="0" placeholder="1000"
                                           class="w-full bg-gray-50 border-none rounded-2xl pl-10 pr-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Description</label>
                            <textarea name="description" rows="4" placeholder="Tell us more about the room, rules, and nearby facilities..."
                                      class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition resize-none font-bold text-gray-700"></textarea>
                        </div>
                    </div>

                    <!-- Media Assets -->
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-2">Photos & Video</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Upload Photos (Max 5)</label>
                                <div class="relative group">
                                    <input type="file" name="photos[]" accept="image/*" multiple required
                                           class="hidden" id="photosInput"
                                           onchange="handlePhotosUpload(event)">
                                    <label for="photosInput" class="cursor-pointer block border-2 border-dashed border-gray-200 rounded-[2rem] p-10 text-center hover:border-indigo-400 hover:bg-indigo-50 transition-all group">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-300 group-hover:text-indigo-400 transition mb-3"></i>
                                        <p class="text-sm font-bold text-gray-500 group-hover:text-indigo-600 transition">Select property photos</p>
                                    </label>
                                </div>
                                <div id="photosPreview" class="grid grid-cols-3 gap-3 mt-4 hidden"></div>
                            </div>

                            <div>
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Room Video (Optional)</label>
                                <input type="file" name="video" accept="video/*" class="hidden" id="videoInput" onchange="handleVideoUpload(event)">
                                <label for="videoInput" class="cursor-pointer block border-2 border-dashed border-gray-200 rounded-[2rem] p-10 text-center hover:border-indigo-400 hover:bg-indigo-50 transition-all group">
                                    <i class="fas fa-video text-4xl text-gray-300 group-hover:text-indigo-400 transition mb-3"></i>
                                    <p class="text-sm font-bold text-gray-500 group-hover:text-indigo-600 transition">Add virtual tour</p>
                                </label>
                                <video id="videoPreview" src="" controls class="hidden mt-4 max-h-40 mx-auto rounded-2xl w-full object-cover"></video>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Or YouTube Video URL</label>
                                <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..."
                                       class="w-full bg-gray-50 border-none rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                <p class="text-[10px] text-gray-400 mt-2 px-1">Paste a YouTube or Vimeo link to show a video tour.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="owner-form-wide bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-6">Common Amenities</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach(['Wifi', 'AC', 'TV', 'Geyser', 'Cooler', 'Parking', 'Kitchen', 'Cleaning', 'Laundry', 'Power Backup', 'CCTV', 'Lift', 'Security', 'Water Supply', 'Gym', 'Swimming Pool', 'Clubhouse'] as $amenity)
                            <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl cursor-pointer hover:bg-indigo-50 transition border border-transparent hover:border-indigo-100">
                                <input type="checkbox" name="amenities[]" value="{{ $amenity }}" class="w-5 h-5 rounded-lg text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                <span class="font-bold text-gray-700 text-sm">{{ $amenity }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Location Section -->
                    <div class="owner-form-wide bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 space-y-6">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-2">Location Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Search Property Location</label>
                                    <div class="relative">
                                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input type="text" id="locationSearch" placeholder="Enter neighborhood or address..." 
                                               class="w-full bg-gray-50 border-none rounded-2xl pl-12 pr-5 py-4 focus:ring-2 focus:ring-indigo-500 transition font-bold text-gray-700">
                                    </div>
                                </div>

                                <button type="button" id="getCurrentLocationBtn" class="w-full bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-bold py-4 rounded-2xl flex items-center justify-center gap-3 transition">
                                    <i class="fas fa-crosshairs"></i> Get Current Location
                                </button>

                                <div class="bg-gray-50 p-6 rounded-[2rem] space-y-4">
                                    <div class="flex justify-between text-xs font-black uppercase tracking-widest text-gray-400">
                                        <span>City</span>
                                        <span id="city-text" class="text-indigo-600">–</span>
                                    </div>
                                    <div class="flex justify-between text-xs font-black uppercase tracking-widest text-gray-400">
                                        <span>State</span>
                                        <span id="state-text" class="text-indigo-600">–</span>
                                    </div>
                                    <div class="pt-2 border-t border-gray-200">
                                         <p id="full-address-text" class="text-sm font-bold text-gray-700 line-clamp-2 italic">No address selected...</p>
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

                            <div class="h-80 md:h-auto min-h-[300px] bg-gray-100 rounded-[2rem] overflow-hidden border border-gray-200 shadow-inner">
                                <div id="map" class="w-full h-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Landmark Section (SEO) -->
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 space-y-4">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-2">Nearby Landmarks (SEO)</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest px-1">Help users find you via search engines</p>
                        <div id="landmark-container" class="flex flex-wrap gap-2 p-4 bg-gray-50 rounded-2xl min-h-[60px] cursor-text" onclick="document.getElementById('landmark-input').focus()">
                            <input type="text" id="landmark-input" placeholder="Type and press Enter (e.g. IIT Delhi, Metro Station)" 
                                   class="bg-transparent border-none outline-none font-bold text-gray-700 placeholder-gray-300 w-full min-w-[200px]">
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 space-y-4">
                        <h3 class="text-xl font-black text-gray-900 border-b pb-4 mb-2">Payment Method</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="online" checked class="hidden peer">
                                <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-sm">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Pay Online</p>
                                        <p class="text-xs text-gray-500">UPI, Card, Netbanking</p>
                                    </div>
                                    <i class="fas fa-check-circle text-indigo-600 text-xl ml-auto opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="wallet" class="hidden peer">
                                <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 transition-all flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">Wallet Balance</p>
                                        <p class="text-xs text-gray-500">Available: ₹{{ number_format(auth()->user()->wallet_balance ?? 0, 2) }}</p>
                                    </div>
                                    <i class="fas fa-check-circle text-indigo-600 text-xl ml-auto opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="owner-form-wide pt-2">
                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-black py-6 rounded-[2rem] shadow-xl shadow-indigo-100 hover:shadow-indigo-200 transition-all duration-300 transform active:scale-95 text-lg flex items-center justify-center gap-3">
                            <i class="fas fa-paper-plane"></i> Post Room Listing
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

@include('rooms.partials.owner-editor-styles')
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

landmarkInput?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim();
        if (val) {
            const tag = document.createElement('div');
            tag.className = 'bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl flex items-center gap-2 text-sm';
            tag.innerHTML = `<span>${val}</span><button type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="hidden" name="landmarks[]" value="${val}">`;
            landmarkContainer.insertBefore(tag, landmarkInput);
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

// Form Submission
document.getElementById('roomForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Auto-tag current landmark input if not empty
    const lInput = document.getElementById('landmark-input');
    if (lInput && lInput.value.trim()) {
        const val = lInput.value.trim();
        const tag = document.createElement('div');
        tag.className = 'bg-indigo-100 text-indigo-700 font-bold px-4 py-2 rounded-xl flex items-center gap-2 text-sm';
        tag.innerHTML = `<span>${val}</span><button type="button" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="hidden" name="landmarks[]" value="${val}">`;
        landmarkContainer.insertBefore(tag, lInput);
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
        const res = await fetch('{{ route("rooms.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        
        const data = await res.json();
        if (data.success) {
            if (data.subscription_used || data.free_listing || data.wallet_used) {
                toastr.success(data.message || 'Room listed successfully!');
                setTimeout(() => window.location.href = '{{ url("/rooms") }}', 1500);
            } else {
                await initiatePayment(data.payment_id, data.amount, data.room_id);
            }
        } else {
            toastr.error(data.message || 'Error creating listing');
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
                setTimeout(() => window.location.href = '{{ url("/rooms") }}', 1500);
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

function toggleBrokerFee(type) {
    const container = document.getElementById('broker_fee_container');
    const input = document.getElementById('broker_fee');
    if (type === 'broker') {
        container.classList.remove('hidden');
        input.required = true;
    } else {
        container.classList.add('hidden');
        input.required = false;
        input.value = 0;
    }
}

// Load Google Maps
const script = document.createElement('script');
script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapsKey}&libraries=places&callback=initMap&loading=async`;
script.async = true;
document.head.appendChild(script);
</script>
@endpush
@endsection
