@extends('layouts.admin')

@section('title', 'Edit ' . $city->name)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-form.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #cityMap { height: 420px; }
    .city-create-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
    }
    @media (min-width: 1024px) {
        .city-create-grid {
            grid-template-columns: minmax(0, 1.6fr) minmax(320px, 1fr);
        }
    }
    .img-cross-delete-btn {
        position: absolute !important;
        top: 6px !important;
        right: 6px !important;
        z-index: 50 !important;
        width: 28px !important;
        height: 28px !important;
        border-radius: 9999px !important;
        background: #e11d48 !important;
        color: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        box-shadow: 0 4px 10px rgba(0,0,0,0.4) !important;
        border: 2px solid #ffffff !important;
        cursor: pointer !important;
        transition: transform 0.15s ease, background 0.15s ease !important;
    }
    .img-cross-delete-btn:hover {
        background: #be123c !important;
        transform: scale(1.15) !important;
    }
    .img-cross-delete-btn:active {
        transform: scale(0.95) !important;
    }
</style>
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Market operations</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Edit City: {{ $city->name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Update map location, hero images carousel, and operational availability.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.cities.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-xs font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fas fa-arrow-left"></i> City Directory
            </a>
        </div>
    </header>

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"><i class="fas fa-triangle-exclamation"></i></span>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([
            ['1','Search location','Adjust map marker & coordinates','fa-magnifying-glass'],
            ['2','Manage carousel','Upload & curate hero banner images','fa-images'],
            ['3','Set availability','Control launch and fallback status','fa-circle-check'],
        ] as [$number,$title,$description,$icon])
            <div class="flex items-center gap-3 rounded-2xl border bg-white p-3 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $number === '2' ? 'admin-theme-bg' : 'bg-slate-100 text-slate-500' }}">
                    <i class="fas {{ $icon }}"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Step {{ $number }}</p>
                    <p class="truncate text-xs font-extrabold text-slate-800">{{ $title }}</p>
                    <p class="truncate text-[10px] text-slate-500">{{ $description }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.cities.update', $city) }}" id="cityEditForm" class="city-create-grid" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-map-location-dot"></i></span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Map &amp; Geocoding</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Search to recalculate coordinates or drag marker on map.</p>
                    </div>
                </div>
                <div class="city-search-group mt-4 flex gap-2">
                    <div class="relative min-w-0 flex-1">
                        <i class="fas fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input id="citySearchInput" value="{{ $city->name }}{{ $city->state ? ', ' . $city->state : '' }}" placeholder="Example: Indore, Madhya Pradesh" autocomplete="off" class="city-input pl-10">
                    </div>
                    <button type="button" id="citySearchBtn" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white transition admin-theme-hover-bg disabled:cursor-wait disabled:opacity-60">
                        <i id="searchIcon" class="fas fa-search"></i> <span id="searchButtonText">Search City</span>
                    </button>
                </div>
                <div id="searchStatusBox" class="mt-3 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                    <i id="searchStatusIcon" class="fas fa-circle-info text-xs text-slate-400"></i>
                    <p id="searchStatus" class="text-[11px] text-slate-500">Drag or click on the map to adjust coordinates.</p>
                </div>
            </div>

            <div class="relative">
                <div id="cityMap" class="w-full"></div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t bg-slate-50 p-4 sm:px-5 sm:py-3.5">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Current Position</p>
                    <p id="selectedLocationLabel" class="truncate text-xs font-bold text-slate-700">{{ $city->name }}{{ $city->state ? ', ' . $city->state : '' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative w-28 sm:w-32">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[9px] font-extrabold text-slate-400">LAT</span>
                        <input id="cityLatitude" name="latitude" value="{{ old('latitude', $city->latitude) }}" required readonly class="h-9 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-2 font-mono text-[11px] font-bold text-slate-700 shadow-xs">
                    </div>
                    <div class="relative w-28 sm:w-32">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[9px] font-extrabold text-slate-400">LNG</span>
                        <input id="cityLongitude" name="longitude" value="{{ old('longitude', $city->longitude) }}" required readonly class="h-9 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-2 font-mono text-[11px] font-bold text-slate-700 shadow-xs">
                    </div>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 border-b pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fas fa-building"></i></span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">City details</h2>
                        <p class="text-xs text-slate-500">Operational parameters.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="cityName" class="text-xs font-bold text-slate-700">City name <span class="text-red-500">*</span></label>
                        <input id="cityName" name="name" value="{{ old('name', $city->name) }}" required placeholder="City name" class="city-input mt-1.5 font-bold">
                    </div>
                    <div>
                        <label for="cityState" class="text-xs font-bold text-slate-700">State</label>
                        <input id="cityState" name="state" value="{{ old('state', $city->state) }}" placeholder="State name" class="city-input mt-1.5">
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="cityImages" class="text-xs font-bold text-slate-700">Hero Images (Carousel Banners)</label>
                            <span class="text-[10px] font-bold text-indigo-600">Multi-Upload</span>
                        </div>

                        @php
                            $existingImages = $city->hero_images_list;
                        @endphp

                        @if(!empty($existingImages))
                            <div class="mt-2.5" id="existingImagesWrapper">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-[11px] font-bold text-slate-700">Current Carousel Images (<span id="existingImgCount">{{ count($existingImages) }}</span>):</p>
                                    <button type="button" onclick="removeAllExistingImages()" class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-extrabold text-rose-600 hover:bg-rose-100 transition border border-rose-200">
                                        <i class="fas fa-trash-can"></i> Delete All
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5" id="existingImagesContainer">
                                    @foreach($existingImages as $idx => $img)
                                        <div class="existing-img-card relative overflow-hidden rounded-xl border-2 border-slate-200 bg-slate-900 aspect-[16/9] shadow-sm">
                                            <img src="{{ \App\Models\City::resolveImageUrl($img) }}" alt="Hero image {{ $idx + 1 }}" class="h-full w-full object-cover opacity-90">
                                            <input type="hidden" name="existing_hero_images[]" value="{{ $img }}">
                                            <span class="absolute bottom-1.5 left-1.5 rounded-md bg-slate-950/80 px-2 py-0.5 text-[9px] font-bold text-white shadow-xs">
                                                Image #{{ $idx + 1 }}
                                            </span>
                                            <button type="button" onclick="removeExistingImage(this)" class="img-cross-delete-btn" title="Delete this image">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <label for="cityImages" class="text-[11px] font-bold text-slate-700">Add More Images (Select Multiple):</label>
                            <input id="cityImages" name="images[]" type="file" accept="image/*" multiple class="mt-1 block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white">
                            <div id="newImagePreviewGrid" class="mt-2 grid grid-cols-2 gap-2.5 hidden"></div>
                        </div>

                        <div class="mt-2.5 rounded-xl border border-indigo-100 bg-indigo-50/50 p-2.5 text-[11px] text-slate-600 space-y-1">
                            <div class="flex items-center gap-1.5 font-extrabold text-indigo-900">
                                <i class="fas fa-images text-indigo-500"></i> Best Exact Fit: 2400 × 525 px (1920 × 420 px)
                            </div>
                            <p class="text-[10.5px] text-slate-500">
                                • Current 420px height ke hisab se <strong>2400 × 525 px</strong> upar-niche se bina kate 100% full fit dikhegi.<br>
                                • Formats: <strong>WebP, JPG, PNG</strong> (Max 4MB per image).
                            </p>
                        </div>
                    </div>
                    <div>
                        <label for="sortOrder" class="text-xs font-bold text-slate-700">Display order</label>
                        <input id="sortOrder" name="sort_order" value="{{ old('sort_order', $city->sort_order) }}" type="number" min="0" class="city-input mt-1.5">
                        <p class="mt-1 text-[10px] text-slate-400">Lower numbers appear first in city selectors.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-900">Availability</h2>
                <p class="mt-1 text-xs text-slate-500">Control how this city appears to customers.</p>
                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3 transition hover:bg-slate-50">
                        <div class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><i class="fas fa-bolt"></i></span>
                            <div><p class="text-xs font-extrabold text-slate-700">Active city</p><p class="mt-0.5 text-[10px] text-slate-500">Make listings available immediately.</p></div>
                        </div>
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $city->is_active)) class="mt-1 rounded border-slate-300 admin-theme-text">
                    </label>
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3 transition hover:bg-slate-50">
                        <div class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="fas fa-star"></i></span>
                            <div><p class="text-xs font-extrabold text-slate-700">Default fallback</p><p class="mt-0.5 text-[10px] text-slate-500">Use when a searched city is unavailable.</p></div>
                        </div>
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $city->is_default)) class="mt-1 rounded border-slate-300 admin-theme-text">
                    </label>
                </div>
            </section>

            <div class="grid grid-cols-[auto_1fr] gap-2">
                <a href="{{ route('admin.cities.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600">Cancel</a>
                <button id="saveCityButton" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl admin-theme-bg px-5 text-sm font-extrabold text-white shadow-sm transition">
                    <i class="fas fa-check"></i> Save Changes
                </button>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function updateExistingImageCount() {
    var container = document.getElementById('existingImagesContainer');
    var countBadge = document.getElementById('existingImgCount');
    if (container && countBadge) {
        var remaining = container.querySelectorAll('.existing-img-card').length;
        countBadge.textContent = remaining;
        if (remaining === 0) {
            container.innerHTML = '<p class="col-span-2 text-[11px] text-amber-700 font-bold bg-amber-50 p-2.5 rounded-xl border border-amber-200"><i class="fas fa-circle-info"></i> All images removed. Click "Save Changes" to update.</p>';
        }
    }
}

function removeExistingImage(btn) {
    var card = btn.closest('.existing-img-card');
    if (card) {
        card.style.transition = 'all 0.2s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.8)';
        setTimeout(function () {
            card.remove();
            updateExistingImageCount();
        }, 150);
    }
}

function removeAllExistingImages() {
    if (confirm('Are you sure you want to delete all current images for this city?')) {
        var container = document.getElementById('existingImagesContainer');
        var countBadge = document.getElementById('existingImgCount');
        if (container) {
            container.innerHTML = '<p class="col-span-2 text-[11px] text-amber-700 font-bold bg-amber-50 p-2.5 rounded-xl border border-amber-200"><i class="fas fa-circle-info"></i> All images removed. Click "Save Changes" to update.</p>';
        }
        if (countBadge) {
            countBadge.textContent = '0';
        }
    }
}

(function () {
    'use strict';

    var defaultLat = {{ (float) ($city->latitude ?: 22.7196) }};
    var defaultLng = {{ (float) ($city->longitude ?: 75.8577) }};
    var map = L.map('cityMap').setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    var latInput = document.getElementById('cityLatitude');
    var lngInput = document.getElementById('cityLongitude');
    var nameInput = document.getElementById('cityName');
    var stateInput = document.getElementById('cityState');
    var locationLabel = document.getElementById('selectedLocationLabel');

    function updateMarker(lat, lng, labelText) {
        marker.setLatLng([lat, lng]);
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);
        if (labelText) {
            locationLabel.textContent = labelText;
        }
    }

    marker.on('dragend', function () {
        var pos = marker.getLatLng();
        updateMarker(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        updateMarker(e.latlng.lat, e.latlng.lng);
    });

    // Search Geocoding
    var searchInput = document.getElementById('citySearchInput');
    var searchBtn = document.getElementById('citySearchBtn');
    var searchStatus = document.getElementById('searchStatus');
    var searchIcon = document.getElementById('searchIcon');
    var searchButtonText = document.getElementById('searchButtonText');

    function searchCity() {
        var query = searchInput.value.trim();
        if (!query) return;

        searchBtn.disabled = true;
        searchIcon.className = 'fas fa-spinner fa-spin';
        searchButtonText.textContent = 'Searching...';
        searchStatus.textContent = 'Searching location...';

        fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=' + encodeURIComponent(query))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.length > 0) {
                    var result = data[0];
                    var lat = parseFloat(result.lat);
                    var lng = parseFloat(result.lon);
                    map.setView([lat, lng], 12);
                    updateMarker(lat, lng, result.display_name);

                    var address = result.address || {};
                    var cityName = address.city || address.town || address.village || address.municipality || address.county || searchInput.value.split(',')[0].trim();
                    var stateName = address.state || address.region || '';

                    if (!nameInput.value) nameInput.value = cityName;
                    if (stateName && !stateInput.value) stateInput.value = stateName;

                    searchStatus.textContent = 'Location found: ' + result.display_name;
                } else {
                    searchStatus.textContent = 'Location not found. Try searching city name with state.';
                }
            })
            .catch(function () {
                searchStatus.textContent = 'Search failed. Please try clicking directly on the map.';
            })
            .finally(function () {
                searchBtn.disabled = false;
                searchIcon.className = 'fas fa-search';
                searchButtonText.textContent = 'Search City';
            });
    }

    searchBtn.addEventListener('click', searchCity);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchCity();
        }
    });

    // Live preview for newly chosen images
    var cityImagesInput = document.getElementById('cityImages');
    var previewGrid = document.getElementById('newImagePreviewGrid');
    if (cityImagesInput && previewGrid) {
        cityImagesInput.addEventListener('change', function () {
            previewGrid.innerHTML = '';
            if (this.files && this.files.length > 0) {
                previewGrid.classList.remove('hidden');
                Array.from(this.files).forEach(function (file) {
                    if (file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            var div = document.createElement('div');
                            div.className = 'relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100 aspect-[16/9] shadow-xs';
                            div.innerHTML = '<img src="' + e.target.result + '" class="h-full w-full object-cover"><span class="absolute bottom-1 right-1 rounded bg-indigo-600/90 px-1.5 py-0.5 text-[9px] font-bold text-white">New</span>';
                            previewGrid.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewGrid.classList.add('hidden');
            }
        });
    }
})();
</script>
@endpush
