@extends('layouts.admin')
@section('title','Property Owners')
@push('styles')
<style>
    .people-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .owner-filter{display:grid;grid-template-columns:minmax(240px,1fr) 180px 160px auto;gap:10px}
    .people-table{width:100%;min-width:1040px}.people-table th,.people-table td{text-align:left!important;vertical-align:middle!important}.people-table th:last-child,.people-table td:last-child{text-align:right!important}
    @media(max-width:1023px){.owner-filter{grid-template-columns:1fr 1fr}}@media(max-width:767px){.people-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.owner-filter{grid-template-columns:1fr}}
</style>
@endpush
@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4"><div><p class="text-[10px] font-extrabold uppercase tracking-[.2em] text-indigo-600">People management</p><h1 class="mt-1 text-2xl font-extrabold text-slate-950">Property Owners</h1><p class="mt-1 text-sm text-slate-500">Manage owner KYC, listings and account access.</p></div><a href="{{ route('admin.owners.create') }}" class="inline-flex h-11 items-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-extrabold text-white shadow-sm shadow-indigo-200"><i class="fas fa-user-plus"></i>Add Owner</a></header>
    @include('admin.members.nav')
    @if(session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-700"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>@endif

    <section class="people-kpis">
        @foreach([
            ['Total owners',$memberStats['total'],'fa-user-tie','text-indigo-600','bg-indigo-50'],
            ['KYC verified',$memberStats['verified'],'fa-user-check','text-emerald-600','bg-emerald-50'],
            ['Blocked',$memberStats['blocked'],'fa-ban','text-amber-600','bg-amber-50'],
            ['Deleted',$memberStats['deleted'],'fa-trash-arrow-up','text-red-600','bg-red-50'],
        ] as [$label,$value,$icon,$tone,$bg])
            <div class="rounded-2xl border bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p><span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $bg }} {{ $tone }}"><i class="fas {{ $icon }}"></i></span></div><p class="mt-3 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p></div>
        @endforeach
    </section>

    <form class="owner-filter rounded-2xl border bg-white p-4 shadow-sm">
        <div class="relative"><i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i><input name="search" value="{{ request('search') }}" placeholder="Search name, email or phone..." class="h-11 w-full rounded-xl border-slate-200 pl-10 text-sm"></div>
        <select name="verification_status" class="h-11 rounded-xl border-slate-200 text-sm"><option value="">All KYC states</option>@foreach(['pending','under_review','verified','rejected'] as $v)<option value="{{ $v }}" @selected(request('verification_status')===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>@endforeach</select>
        <select name="status" class="h-11 rounded-xl border-slate-200 text-sm"><option value="">All accounts</option><option value="active" @selected(request('status')==='active')>Active</option><option value="blocked" @selected(request('status')==='blocked')>Blocked</option><option value="deleted" @selected(request('status')==='deleted')>Deleted</option></select>
        <div class="flex gap-2"><button class="h-11 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white">Apply</button><a href="{{ route('admin.owners') }}" class="inline-flex h-11 items-center rounded-xl border px-4 text-xs font-extrabold text-slate-600">Reset</a></div>
    </form>

    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4"><div><h2 class="text-sm font-extrabold">Owner Directory</h2><p class="text-xs text-slate-500">{{ $owners->total() }} accounts match the current filters</p></div><span class="rounded-full bg-indigo-50 px-3 py-1.5 text-[10px] font-extrabold text-indigo-700">Page {{ $owners->currentPage() }} / {{ max(1,$owners->lastPage()) }}</span></div>
        <div class="overflow-x-auto"><table class="people-table"><thead class="bg-slate-50"><tr><th class="px-5 py-3">Owner</th><th class="px-4 py-3">Listings</th><th class="px-4 py-3">KYC</th><th class="px-4 py-3">Account</th><th class="px-4 py-3">Benefits</th><th class="px-4 py-3">Joined</th><th class="px-5 py-3">Actions</th></tr></thead><tbody class="divide-y">
        @forelse($owners as $owner)
            @php $tone=match($owner->verification_status){'verified'=>'bg-emerald-50 text-emerald-700','rejected'=>'bg-red-50 text-red-700','under_review'=>'bg-blue-50 text-blue-700',default=>'bg-amber-50 text-amber-700'}; @endphp
            <tr class="hover:bg-slate-50/70">
                <td class="px-5 py-4"><div class="flex min-w-[220px] items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 font-extrabold text-violet-600">{{ strtoupper(substr($owner->name,0,1)) }}</span><div class="min-w-0"><p class="truncate text-sm font-extrabold text-slate-900">{{ $owner->name }}</p><p class="truncate text-xs text-slate-400">#{{ $owner->id }} · {{ $owner->email }}</p><p class="text-[10px] text-slate-400">{{ $owner->phone ?: 'No phone number' }}</p></div></div></td>
                <td class="px-4 py-4"><p class="text-lg font-extrabold text-indigo-600">{{ $owner->rooms_count }}</p><p class="text-[10px] text-slate-400">properties</p></td>
                <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $tone }}">{{ ucfirst(str_replace('_',' ',$owner->verification_status)) }}</span></td>
                <td class="px-4 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $owner->trashed()||$owner->is_blocked?'bg-red-50 text-red-700':'bg-emerald-50 text-emerald-700' }}"><i class="fas fa-circle text-[5px]"></i>{{ $owner->trashed()?'Deleted':($owner->is_blocked?'Blocked':'Active') }}</span></td>
                <td class="px-4 py-4"><p class="text-xs font-bold">₹{{ number_format($owner->wallet_balance,2) }}</p><p class="text-[10px] text-slate-400">{{ $owner->free_unlocks ?? 0 }} unlocks</p></td>
                <td class="px-4 py-4 text-xs text-slate-500">{{ $owner->created_at->format('d M Y') }}</td>
                <td class="px-5 py-4">@if($owner->trashed())<form method="POST" action="{{ route('admin.members.restore',$owner->id) }}">@csrf<button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Restore</button></form>@else<div class="flex justify-end gap-2"><a href="{{ route('admin.members.index',['q'=>$owner->email,'member_id'=>$owner->id]) }}" title="Member 360" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700"><i class="fas fa-chart-pie"></i></a><a href="{{ route('admin.owners.detail',$owner) }}" class="rounded-lg border px-3 py-2 text-xs font-bold text-slate-700">View</a><a href="{{ route('admin.owners.edit',$owner) }}" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white">Edit</a></div>@endif</td>
            </tr>
        @empty<tr><td colspan="7" class="p-14 text-center"><i class="fas fa-users-slash text-3xl text-slate-300"></i><p class="mt-3 text-sm font-bold text-slate-600">No owners found</p><p class="text-xs text-slate-400">Try changing the filters.</p></td></tr>@endforelse
        </tbody></table></div>
        @if($owners->hasPages())<div class="border-t p-4">{{ $owners->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
