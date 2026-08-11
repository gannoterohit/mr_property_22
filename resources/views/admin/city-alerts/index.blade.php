@extends('layouts.admin')
@section('title','City Alerts')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">@endpush
@section('admin-content')
<div class="space-y-5 p-5 lg:p-6"><header class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Audience notifications</p><h1 class="mt-1 text-2xl font-extrabold">City Alert Subscriptions</h1><p class="text-sm text-slate-500">Users waiting for new properties in their preferred cities.</p></div><x-admin.data-actions dataset="city-alerts" /></header>
<section class="city-stats admin-kpis">@forelse($cityStats as $stat)<div class="rounded-2xl border bg-white p-4"><div class="flex justify-between"><span class="flex h-9 w-9 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-location-dot"></i></span><strong class="text-2xl">{{ $stat->total }}</strong></div><p class="mt-3 truncate text-xs font-bold">{{ $stat->city?:'Unknown city' }}</p><p class="text-[10px] text-slate-400">active alerts</p></div>@empty<div class="col-span-4 rounded-2xl border bg-white p-5 text-sm text-slate-500">No city alerts yet.</div>@endforelse</section>
<form method="GET" class="space-y-3 rounded-2xl border bg-white p-4">
    <!-- Filter Bar Section -->
    <div class="city-filter admin-filter-bar">
        <div class="relative">
            <i class="fas fa-search absolute left-3 top-3.5 text-xs text-slate-400"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search user, email or city" class="h-10 w-full rounded-xl pl-9 text-xs">
        </div>
        <select name="city" class="h-10 rounded-xl text-xs">
            <option value="">All cities</option>
            @foreach($cities as $city)
                <option value="{{ $city }}" @selected(request('city')===$city)>{{ $city }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" title="From date" class="h-10 rounded-xl text-xs">
        <input type="date" name="to" value="{{ request('to') }}" title="To date" class="h-10 rounded-xl text-xs">
    </div>
    
    <!-- Filter Actions Section -->
    <div class="flex flex-wrap items-center justify-between gap-2 border-t pt-3">
        <div class="flex gap-2">
            <a href="{{ route('admin.city-alerts.index') }}" class="flex h-10 items-center rounded-xl border px-4 text-xs font-bold">Reset</a>
        </div>
        <button class="h-10 rounded-xl bg-slate-900 px-5 text-xs font-bold text-white">Apply filters</button>
    </div>
</form>
<section class="overflow-hidden rounded-2xl border bg-white"><div class="flex justify-between border-b px-5 py-4"><div><h2 class="text-sm font-extrabold">Alert directory</h2><p class="text-xs text-slate-500">{{ $alerts->total() }} matching subscriptions</p></div><span class="text-xs font-bold text-slate-500">Page {{ $alerts->currentPage() }} of {{ $alerts->lastPage() }}</span></div><div class="overflow-x-auto"><table class="city-table admin-table-base"><thead><tr><th>User</th><th>Requested city</th><th>Subscribed</th><th>Action</th></tr></thead><tbody class="divide-y">@forelse($alerts as $alert)<tr><td class="px-5"><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded-xl admin-theme-soft text-xs font-bold admin-theme-text">{{ strtoupper(substr($alert->user?->name??'U',0,1)) }}</span><div><strong class="block text-xs">{{ $alert->user?->name??'Deleted user' }}</strong><small class="text-[10px] text-slate-400">{{ $alert->user?->email??'Account unavailable' }}</small></div></div></td><td class="px-5"><span class="rounded-full admin-theme-soft px-3 py-1 text-xs font-bold admin-theme-text"><i class="fas fa-location-dot mr-1"></i>{{ $alert->city }}</span></td><td class="px-5"><p class="text-xs font-semibold">{{ $alert->created_at->format('d M Y') }}</p><p class="text-[10px] text-slate-400">{{ $alert->created_at->diffForHumans() }}</p></td><td class="px-5"><form action="{{ route('admin.city-alerts.destroy',$alert) }}" method="POST" class="confirm-remove">@csrf @method('DELETE')<x-admin.action-icon variant="remove" type="submit" /></form></td></tr>@empty<tr><td colspan="4" class="p-12 text-center text-sm text-slate-500">No city alert subscriptions found.</td></tr>@endforelse</tbody></table></div>@if($alerts->hasPages())<div class="border-t p-4">{{ $alerts->links() }}</div>@endif</section></div>
@endsection
@push('scripts')<script>document.querySelectorAll('.confirm-remove').forEach(f=>f.addEventListener('submit',async e=>{e.preventDefault();const r=await Swal.fire({title:'Remove city alert?',text:'The user will stop receiving notifications for this city.',icon:'warning',showCancelButton:true,confirmButtonText:'Yes, remove',confirmButtonColor:'#dc2626'});if(r.isConfirmed)f.submit()}));</script>@endpush
