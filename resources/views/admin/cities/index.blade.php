@extends('layouts.admin')

@section('title', 'Operational Cities')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">

@endpush

@section('admin-content')
@php
    $activeCities = $cities->where('is_active', true)->count();
    $comingSoonCities = $cities->count() - $activeCities;
    $mappedCities = $cities->filter(fn($city) => $city->latitude !== null && $city->longitude !== null)->count();
    $defaultCity = $cities->firstWhere('is_default', true);
@endphp

<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Market operations</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Operational Cities</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">Manage launch status, map coordinates and the fallback city shown across the platform.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="cities" importable />
            <a href="{{ route('admin.cities.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl admin-theme-bg px-5 text-xs font-extrabold text-white shadow-sm  transition ">
                <i class="fas fa-plus"></i> Add New City
            </a>
        </div>
    </header>

    @if($errors->any())
        <div class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100"><i class="fas fa-triangle-exclamation"></i></span>
            {{ $errors->first() }}
        </div>
    @endif

    <section class="cities-kpis admin-kpis">
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total cities</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-city"></i></span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-slate-950">{{ $cities->count() }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Configured markets</p>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Active</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-circle-check"></i></span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-emerald-600">{{ $activeCities }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Live for customers</p>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Coming soon</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-clock"></i></span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-amber-600">{{ $comingSoonCities }}</p>
            <p class="mt-1 text-[11px] text-slate-500">Waiting for launch</p>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Map ready</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fas fa-location-dot"></i></span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-sky-600">{{ $mappedCities }}/{{ $cities->count() }}</p>
            <p class="mt-1 truncate text-[11px] text-slate-500">Default: {{ $defaultCity?->name ?? 'Not selected' }}</p>
        </article>
    </section>

    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div>
                <h2 class="text-sm font-extrabold text-slate-950">City Directory</h2>
                <p class="mt-0.5 text-xs text-slate-500">Edit a city directly and save that row.</p>
            </div>
            <div class="relative w-full sm:w-72">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input id="cityTableSearch" type="search" placeholder="Search city or state..." class="h-10 w-full rounded-xl border-slate-200 pl-9 text-xs">
            </div>
        </div>

        <div class="overflow-x-auto">
                <table class="cities-table admin-table-base">
                <thead class="bg-slate-50">
                    <tr class="border-b">
                        <th>City</th>
                        <th>State</th>
                        <th>Hero image</th>
                        <th>Map coordinates</th>
                        <th>Launch status</th>
                        <th>Fallback</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cityTableBody" class="divide-y divide-slate-100">
                    @forelse($cities as $city)
                        @php
                            $formId = 'city-form-' . $city->id;
                            $cityHeroImages = $city->hero_images_list;
                        @endphp
                        <tr class="city-row transition hover:bg-slate-50/70" data-search="{{ Str::lower($city->name.' '.$city->state) }}">
                            <td>
                                <form id="{{ $formId }}" method="POST" action="{{ route('admin.cities.update', $city) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="sort_order" value="{{ $city->sort_order }}">
                                </form>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-building"></i></span>
                                    <input form="{{ $formId }}" name="name" value="{{ $city->name }}" required class="city-field min-w-[145px] font-bold text-slate-800">
                                </div>
                            </td>
                            <td>
                                <input form="{{ $formId }}" name="state" value="{{ $city->state }}" placeholder="Add state" class="city-field min-w-[140px]">
                            </td>
                            <td>
                                <div class="min-w-[150px] space-y-1">
                                    @if(!empty($cityHeroImages))
                                        <div class="relative group inline-block">
                                            <img src="{{ \App\Models\City::resolveImageUrl($cityHeroImages[0]) }}" alt="{{ $city->name }} hero image" width="1920" height="800" class="h-11 w-24 rounded-lg border border-slate-200 object-cover shadow-sm">
                                            @if(count($cityHeroImages) > 1)
                                                <span class="absolute -top-1.5 -right-1.5 inline-flex items-center justify-center rounded-full bg-indigo-600 px-1.5 py-0.5 text-[9px] font-extrabold text-white shadow">
                                                    {{ count($cityHeroImages) }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400">
                                            <i class="fas fa-image"></i> Default
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="grid min-w-[230px] grid-cols-2 gap-2">
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[9px] font-bold text-slate-400">LAT</span>
                                        <input form="{{ $formId }}" name="latitude" value="{{ $city->latitude }}" placeholder="Latitude" class="city-field pl-10">
                                    </div>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[9px] font-bold text-slate-400">LNG</span>
                                        <input form="{{ $formId }}" name="longitude" value="{{ $city->longitude }}" placeholder="Longitude" class="city-field pl-10">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <x-admin.status-toggle
                                    :active="$city->is_active"
                                    inactive-label="Inactive"
                                    :action="route('admin.cities.toggle-status', $city)"
                                    :data-label="$city->name"
                                />
                            </td>
                            <td>
                                <label class="inline-flex min-w-max cursor-pointer items-center gap-2 text-xs font-bold {{ $city->is_default ? 'admin-theme-text' : 'text-slate-500' }}">
                                    <input form="{{ $formId }}" type="checkbox" name="is_default" value="1" @checked($city->is_default) class="rounded border-slate-300 admin-theme-text">
                                    <i class="{{ $city->is_default ? 'fas' : 'far' }} fa-star text-amber-400"></i>
                                    {{ $city->is_default ? 'Default' : 'Set default' }}
                                </label>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.cities.edit', $city) }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-indigo-50 px-3 text-xs font-extrabold text-indigo-700 hover:bg-indigo-100 transition shadow-sm" title="Edit City & Images">
                                        <i class="fas fa-pen-to-square"></i> Edit
                                    </a>
                                    <button form="{{ $formId }}" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-slate-900 px-3 text-xs font-extrabold text-white transition admin-theme-hover-bg shadow-sm" title="Save quick changes">
                                        <i class="fas fa-floppy-disk"></i> Save
                                    </button>
                                    <form method="POST" action="{{ route('admin.cities.destroy', $city) }}" class="admin-confirm" data-confirm-title="Delete {{ $city->name }}?" data-confirm-text="Only cities without related listings or alerts can be deleted." data-confirm-button="Yes, delete city">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.action-icon variant="delete" type="submit" title="Delete city" />
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-city"></i></span>
                                <p class="mt-3 text-sm font-extrabold text-slate-700">No cities configured</p>
                                <p class="mt-1 text-xs text-slate-500">Add your first operational city to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="citySearchEmpty" class="hidden px-5 py-14 text-center">
            <i class="fas fa-magnifying-glass text-2xl text-slate-300"></i>
            <p class="mt-3 text-sm font-bold text-slate-600">No matching city found</p>
            <p class="mt-1 text-xs text-slate-400">Try another city or state name.</p>
        </div>

        @if($cities->isNotEmpty())
            <div class="flex items-center justify-between border-t bg-slate-50 px-5 py-3 text-[11px] text-slate-500">
                <span><strong id="visibleCityCount" class="text-slate-700">{{ $cities->count() }}</strong> cities shown</span>
                <span>Changes save one row at a time</span>
            </div>
        @endif
    </section>
</div>

<script>
(function () {
    'use strict';
    var search = document.getElementById('cityTableSearch');
    var rows = Array.prototype.slice.call(document.querySelectorAll('.city-row'));
    var empty = document.getElementById('citySearchEmpty');
    var count = document.getElementById('visibleCityCount');

    if (!search) return;
    search.addEventListener('input', function () {
        var query = search.value.trim().toLowerCase();
        var visible = 0;
        rows.forEach(function (row) {
            var matches = !query || row.dataset.search.indexOf(query) !== -1;
            row.classList.toggle('hidden', !matches);
            if (matches) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible !== 0);
        if (count) count.textContent = visible;
    });
})();
</script>
@endsection
