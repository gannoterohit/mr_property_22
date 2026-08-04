@extends('layouts.admin')

@section('title', 'Add Operational City')

@push('styles')
<style>
    .city-create-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(340px,.65fr);gap:20px;align-items:start}
    .city-input{height:44px;width:100%;border:1px solid #e2e8f0!important;border-radius:12px!important;font-size:13px!important}
    .city-input:focus{border-color:var(--admin-primary)!important;box-shadow:0 0 0 3px rgba(var(--admin-primary-rgb),.1)!important}
    #cityMap{height:560px;background:#f1f5f9}
    .leaflet-control-container{font-size:11px}
    @media(max-width:1199px){.city-create-grid{grid-template-columns:1fr}#cityMap{height:460px}}
    @media(max-width:639px){#cityMap{height:380px}.city-search-group{display:grid;grid-template-columns:1fr}.city-search-group button{width:100%}}
</style>
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Market operations</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Add Operational City</h1>
            <p class="mt-1 text-sm text-slate-500">Find the city on the map, verify its details and configure its launch status.</p>
        </div>
        <a href="{{ route('admin.cities.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-xs font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <i class="fas fa-arrow-left"></i> City Directory
        </a>
    </header>

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"><i class="fas fa-triangle-exclamation"></i></span>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-3">
        @foreach([
            ['1','Search location','Find the correct city on map','fa-magnifying-glass'],
            ['2','Confirm details','Review city, state and coordinates','fa-location-dot'],
            ['3','Set availability','Choose launch and fallback status','fa-circle-check'],
        ] as [$number,$title,$description,$icon])
            <div class="flex items-center gap-3 rounded-2xl border bg-white p-3 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $number === '1' ? 'admin-theme-bg' : 'bg-slate-100 text-slate-500' }}">
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

    <form method="POST" action="{{ route('admin.cities.store') }}" id="cityCreateForm" class="city-create-grid" enctype="multipart/form-data">
        @csrf
        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b p-4 sm:p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-map-location-dot"></i></span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">Search & confirm map location</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Search by city and state for the most accurate result.</p>
                    </div>
                </div>
                <div class="city-search-group mt-4 flex gap-2">
                    <div class="relative min-w-0 flex-1">
                        <i class="fas fa-location-dot absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input id="citySearchInput" value="{{ old('name') }}" placeholder="Example: Indore, Madhya Pradesh" autocomplete="off" class="city-input pl-10">
                    </div>
                    <button type="button" id="citySearchBtn" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white transition admin-theme-hover-bg disabled:cursor-wait disabled:opacity-60">
                        <i id="searchIcon" class="fas fa-search"></i> <span id="searchButtonText">Search City</span>
                    </button>
                </div>
                <div id="searchStatusBox" class="mt-3 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                    <i id="searchStatusIcon" class="fas fa-circle-info text-xs text-slate-400"></i>
                    <p id="searchStatus" class="text-[11px] text-slate-500">Search a city or click anywhere on the map to choose coordinates.</p>
                </div>
            </div>

            <div class="relative">
                <div id="cityMap" class="w-full"></div>
                <div id="mapEmptyState" class="pointer-events-none absolute inset-0 z-[400] flex items-center justify-center bg-slate-100/90">
                    <div class="text-center">
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white admin-theme-text shadow-sm"><i class="fas fa-map"></i></span>
                        <p class="mt-3 text-sm font-extrabold text-slate-700">Search to select a city</p>
                        <p class="mt-1 text-xs text-slate-500">The selected location will appear here.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t bg-slate-50 px-5 py-3">
                <div>
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Selected location</p>
                    <p id="selectedLocationLabel" class="text-xs font-bold text-slate-700">No location selected</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-[10px] font-bold text-slate-500 shadow-sm"><i class="fas fa-hand-pointer admin-theme-text"></i> Click map to fine-tune</span>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 border-b pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fas fa-building"></i></span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900">City details</h2>
                        <p class="text-xs text-slate-500">Fields fill automatically after search.</p>
                    </div>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="cityName" class="text-xs font-bold text-slate-700">City name <span class="text-red-500">*</span></label>
                        <input id="cityName" name="name" value="{{ old('name') }}" required placeholder="City name" class="city-input mt-1.5">
                    </div>
                    <div>
                        <label for="cityState" class="text-xs font-bold text-slate-700">State</label>
                        <input id="cityState" name="state" value="{{ old('state') }}" placeholder="State name" class="city-input mt-1.5">
                    </div>
                    <div>
                        <label for="cityImage" class="text-xs font-bold text-slate-700">Hero image</label>
                        <input id="cityImage" name="image" type="file" accept="image/*" class="mt-1.5 block w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white">
                        <p class="mt-1 text-[10px] text-slate-400">Upload a city image for the homepage hero. Leave blank to use the default fallback image.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="cityLatitude" class="text-xs font-bold text-slate-700">Latitude <span class="text-red-500">*</span></label>
                            <input id="cityLatitude" name="latitude" value="{{ old('latitude') }}" required readonly placeholder="--" class="city-input mt-1.5 bg-slate-50 font-mono text-[11px] text-slate-600">
                        </div>
                        <div>
                            <label for="cityLongitude" class="text-xs font-bold text-slate-700">Longitude <span class="text-red-500">*</span></label>
                            <input id="cityLongitude" name="longitude" value="{{ old('longitude') }}" required readonly placeholder="--" class="city-input mt-1.5 bg-slate-50 font-mono text-[11px] text-slate-600">
                        </div>
                    </div>
                    <div>
                        <label for="sortOrder" class="text-xs font-bold text-slate-700">Display order</label>
                        <input id="sortOrder" name="sort_order" value="{{ old('sort_order', 0) }}" type="number" min="0" class="city-input mt-1.5">
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
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="mt-1 rounded border-slate-300 admin-theme-text">
                    </label>
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3 transition hover:bg-slate-50">
                        <div class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><i class="fas fa-star"></i></span>
                            <div><p class="text-xs font-extrabold text-slate-700">Default fallback</p><p class="mt-0.5 text-[10px] text-slate-500">Use when a searched city is unavailable.</p></div>
                        </div>
                        <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="mt-1 rounded border-slate-300 admin-theme-text">
                    </label>
                </div>
            </section>

            <div class="grid grid-cols-[auto_1fr] gap-2">
                <a href="{{ route('admin.cities.index') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-extrabold text-slate-600">Cancel</a>
                <button id="saveCityButton" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl admin-theme-bg px-5 text-sm font-extrabold text-white shadow-sm  transition ">
                    <i class="fas fa-check"></i> Save City
                </button>
            </div>
        </aside>
    </form>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    'use strict';

    var searchInput = document.getElementById('citySearchInput');
    var searchButton = document.getElementById('citySearchBtn');
    var searchIcon = document.getElementById('searchIcon');
    var searchButtonText = document.getElementById('searchButtonText');
    var status = document.getElementById('searchStatus');
    var statusBox = document.getElementById('searchStatusBox');
    var statusIcon = document.getElementById('searchStatusIcon');
    var emptyState = document.getElementById('mapEmptyState');
    var locationLabel = document.getElementById('selectedLocationLabel');
    var nameInput = document.getElementById('cityName');
    var stateInput = document.getElementById('cityState');
    var latitudeInput = document.getElementById('cityLatitude');
    var longitudeInput = document.getElementById('cityLongitude');
    var initialLat = parseFloat(latitudeInput.value);
    var initialLng = parseFloat(longitudeInput.value);
    var hasInitialCoordinates = !isNaN(initialLat) && !isNaN(initialLng);
    var map = L.map('cityMap').setView(hasInitialCoordinates ? [initialLat, initialLng] : [22.9734, 78.6569], hasInitialCoordinates ? 12 : 5);
    var marker = null;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    function selectLocation(lat, lng, label, zoom) {
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        if (label) marker.bindPopup(label).openPopup();
        map.setView([lat, lng], zoom || 12);
        latitudeInput.value = Number(lat).toFixed(7);
        longitudeInput.value = Number(lng).toFixed(7);
        emptyState.classList.add('hidden');
        locationLabel.textContent = nameInput.value
            ? nameInput.value + (stateInput.value ? ', ' + stateInput.value : '')
            : 'Custom map location';
    }

    function setStatus(message, type) {
        var styles = {
            info: ['bg-slate-50', 'text-slate-500', 'fa-circle-info', 'text-slate-400'],
            loading: ['admin-theme-soft', 'admin-theme-text', 'fa-spinner fa-spin', 'admin-theme-text'],
            success: ['bg-emerald-50', 'text-emerald-700', 'fa-circle-check', 'text-emerald-500'],
            error: ['bg-red-50', 'text-red-700', 'fa-circle-exclamation', 'text-red-500']
        };
        var style = styles[type] || styles.info;
        statusBox.className = 'mt-3 flex items-center gap-2 rounded-xl px-3 py-2 ' + style[0];
        status.className = 'text-[11px] font-semibold ' + style[1];
        statusIcon.className = 'fas text-xs ' + style[2] + ' ' + style[3];
        status.textContent = message;
    }

    if (hasInitialCoordinates) {
        selectLocation(initialLat, initialLng, nameInput.value || 'Selected city', 12);
    }

    map.on('click', function (event) {
        selectLocation(event.latlng.lat, event.latlng.lng, 'Selected location', map.getZoom());
        setStatus('Coordinates updated from the map. You can now save the city.', 'success');
    });

    function searchCity() {
        var query = searchInput.value.trim();
        if (!query) {
            searchInput.focus();
            return;
        }

        searchButton.disabled = true;
        searchIcon.className = 'fas fa-spinner fa-spin';
        searchButtonText.textContent = 'Searching...';
        setStatus('Searching for the best matching city...', 'loading');

        fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&countrycodes=in&q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Search request failed');
                return response.json();
            })
            .then(function (results) {
                if (!results.length) throw new Error('City not found');
                var result = results[0];
                var address = result.address || {};
                var cityName = address.city || address.town || address.municipality || address.county || address.village || query;
                var stateName = address.state || address.region || '';

                nameInput.value = cityName;
                stateInput.value = stateName;
                selectLocation(parseFloat(result.lat), parseFloat(result.lon), result.display_name, 12);
                locationLabel.textContent = cityName + (stateName ? ', ' + stateName : '');
                setStatus('City found. Confirm the marker position, then save.', 'success');
            })
            .catch(function (error) {
                setStatus(error.message === 'City not found'
                    ? 'City not found. Try city name with state.'
                    : 'Search failed. Please try again.', 'error');
            })
            .finally(function () {
                searchButton.disabled = false;
                searchIcon.className = 'fas fa-search';
                searchButtonText.textContent = 'Search City';
            });
    }

    searchButton.addEventListener('click', searchCity);
    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchCity();
        }
    });

    document.getElementById('cityCreateForm').addEventListener('submit', function (event) {
        if (!latitudeInput.value || !longitudeInput.value) {
            event.preventDefault();
            setStatus('Search and select a city on the map before saving.', 'error');
            searchInput.focus();
        }
    });
})();
</script>
@endsection
