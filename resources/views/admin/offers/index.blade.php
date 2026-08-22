@extends('layouts.admin')

@section('title', 'Offer Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">

@endpush

@section('admin-content')
@php
    $placementMeta = [
        'top_nav' => ['Top announcement', 'Public website header', 'fa-window-maximize'],
        'home_hero' => ['Home promotion', 'Below homepage hero', 'fa-house'],
        'dashboard' => ['Dashboard banner', 'User and owner dashboards', 'fa-chart-pie'],
        'sidebar' => ['Sidebar banner', 'Rooms and blog desktop sidebar', 'fa-table-columns'],
        'mobile_feed' => ['Mobile feed', 'Rooms and blog mobile feed', 'fa-mobile-screen'],
        'popup' => ['Popup modal', 'Public pages only', 'fa-clone'],
    ];
@endphp

<div class="space-y-6 p-5 lg:p-7">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest admin-theme-text">Marketing</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Offer Banner Management</h1>
            <p class="mt-1 text-sm text-slate-500">Create, schedule and place promotions across the website.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="offers" />
            <a href="{{ route('admin.offers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white shadow-sm ">
                <i class="fas fa-plus"></i>Create Offer
            </a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="offer-overview">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-map-location-dot"></i></span>
                <div><h2 class="font-bold text-slate-900">Website placements</h2><p class="text-xs text-slate-500">Active and scheduled offers by location</p></div>
            </div>
            <div class="offer-placement-grid">
                @foreach($placementMeta as $key => $meta)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                        <div class="flex items-start justify-between gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white admin-theme-text shadow-sm"><i class="fas {{ $meta[2] }} text-xs"></i></span><span class="rounded-full bg-white px-2 py-1 text-xs font-extrabold text-slate-700">{{ $placementCounts[$key] ?? 0 }}</span></div>
                        <p class="mt-3 text-sm font-bold text-slate-900">{{ $meta[0] }}</p><p class="mt-0.5 text-[11px] text-slate-500">{{ $meta[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <form action="{{ route('admin.offers.display-settings') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            @csrf
            <div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-clock"></i></span><div><h2 class="font-bold text-slate-900">Popup behaviour</h2><p class="text-xs text-slate-500">Applies to active popup offers</p></div></div>
            <label for="popup_delay" class="mt-5 block text-sm font-bold text-slate-700">Popup delay (seconds)</label>
            <input id="popup_delay" type="number" name="popup_delay" min="0" max="300" value="{{ old('popup_delay', \App\Models\Setting::get('popup_delay', 5)) }}" class="mt-2 w-full rounded-xl border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold /20">
            <p class="mt-2 text-xs leading-5 text-slate-500">Use 0 for immediate display. Popup is shown once per browser session and never inside admin/account workspaces.</p>
            <button class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800"><i class="fas fa-floppy-disk"></i>Save popup setting</button>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div><h2 class="font-bold text-slate-900">All offers</h2><p class="text-xs text-slate-500">{{ $offers->total() }} promotion{{ $offers->total() === 1 ? '' : 's' }} found</p></div>
                <form method="GET" action="{{ route('admin.offers.index') }}" class="offer-filter-grid items-end">
                    <div class="relative"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i><input name="search" value="{{ request('search') }}" placeholder="Search offers..." class="h-10 w-full rounded-xl border-slate-200 pl-9 pr-3 text-xs font-semibold /20"></div>
                    <select name="placement" class="h-10 rounded-xl border-slate-200 text-xs font-semibold /20"><option value="">All placements</option>@foreach(\App\Models\Offer::PLACEMENTS as $key => $label)<option value="{{ $key }}" @selected(request('placement') === $key)>{{ $label }}</option>@endforeach</select>
                    <select name="audience" class="h-10 rounded-xl border-slate-200 text-xs font-semibold /20"><option value="">All audiences</option><option value="both" @selected(request('audience') === 'both')>Both</option><option value="user" @selected(request('audience') === 'user')>Users</option><option value="owner" @selected(request('audience') === 'owner')>Owners</option><option value="broker" @selected(request('audience') === 'broker')>Brokers</option></select>
                    <select name="status" class="h-10 rounded-xl border-slate-200 text-xs font-semibold /20"><option value="">Any status</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select>
                    <div class="flex gap-2 justify-end">
                        <button class="h-10 rounded-xl admin-theme-bg px-4 text-xs font-bold text-white ">Filter</button>
                        @if(request()->hasAny(['search','placement','audience','status']))<a href="{{ route('admin.offers.index') }}" title="Clear filters" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50"><i class="fas fa-rotate-left text-xs"></i></a>@endif
                    </div>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="offer-table admin-table-base divide-y divide-slate-200">
                <thead class="bg-slate-50"><tr>@foreach(['Offer','Placement','Audience','Schedule','Status','Actions'] as $heading)<th class="px-5 py-3 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-500">{{ $heading }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($offers as $offer)
                    @php
                        $now = now();
                        $status = !$offer->is_active ? 'Inactive' : ($offer->start_date && $offer->start_date->startOfDay()->gt($now) ? 'Scheduled' : ($offer->end_date && $offer->end_date->endOfDay()->lt($now) ? 'Expired' : 'Live'));
                        $statusClass = ['Live'=>'bg-emerald-50 text-emerald-700','Scheduled'=>'bg-sky-50 text-sky-700','Expired'=>'bg-amber-50 text-amber-700','Inactive'=>'bg-slate-100 text-slate-600'][$status];
                    @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-4"><div class="flex min-w-[210px] items-center gap-3">@if($offer->image_path)<img src="{{ $offer->image_url }}" alt="" width="210" height="120" class="h-12 w-16 rounded-xl border border-slate-200 object-cover">@else<div class="flex h-12 w-16 items-center justify-center rounded-xl text-xs font-black text-white" style="background:{{ $offer->banner_color }}">{{ $offer->discount_text ?: 'OFFER' }}</div>@endif<div><p class="text-sm font-bold text-slate-900">{{ $offer->title }}</p><p class="mt-0.5 text-[11px] font-semibold uppercase text-slate-400">{{ str_replace('_', ' ', $offer->type) }}</p></div></div></td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">{{ $placementMeta[$offer->placement][0] ?? str($offer->placement)->replace('_',' ')->title() }}</td>
                        <td class="px-5 py-4"><span class="rounded-full admin-theme-soft px-2.5 py-1 text-xs font-bold admin-theme-text">{{ ucfirst($offer->target_audience) }}</span></td>
                        <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">{{ $offer->start_date?->format('d M Y') ?? 'Now' }}<span class="mx-1">–</span>{{ $offer->end_date?->format('d M Y') ?? 'No end' }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ $status }}</span></td>
                        <td class="px-5 py-4"><div class="flex items-center gap-1.5"><x-admin.status-toggle :active="$offer->is_active" :action="route('admin.offers.toggleActive', $offer)" :data-label="$offer->title" method="POST" /><x-admin.action-icon variant="edit" :href="route('admin.offers.edit', $offer)" title="Edit offer" /><form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="admin-confirm" data-confirm-title="Delete {{ $offer->title }}?" data-confirm-text="This offer will be permanently removed." data-confirm-button="Yes, delete offer">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" title="Delete offer" /></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-14 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-tags"></i></span><p class="mt-3 font-bold text-slate-800">No offers created yet</p><p class="mt-1 text-sm text-slate-500">Create your first promotion and choose where it should appear.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $offers->links() }}</div>@endif
    </section>
</div>
@endsection
