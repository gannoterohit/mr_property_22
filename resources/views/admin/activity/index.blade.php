@extends('layouts.admin')
@section('title', 'Activity Logs')

@section('admin-content')
<div class="space-y-4 p-5 lg:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-[10px] font-extrabold uppercase tracking-[.18em] admin-theme-text">Security audit</p><h1 class="mt-1 text-xl font-extrabold text-slate-950">Admin Activity Logs</h1><p class="mt-1 text-xs text-slate-500">Track successful changes made by every administrator and staff member.</p></div><div class="audit-summary"><span><strong>{{ number_format($totalLogs) }}</strong>Total</span><span><strong>{{ number_format($todayLogs) }}</strong>Today</span><span><strong>{{ number_format($activeActors) }}</strong>Active staff</span><em><i class="fas fa-circle-check"></i>Logging active</em></div></div>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-4"><form method="GET" class="audit-filters"><div class="relative"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i><input name="search" value="{{ request('search') }}" placeholder="Search action or route..." class="h-10 w-full rounded-xl pl-9 text-xs"></div><select name="actor" class="h-10 rounded-xl text-xs"><option value="">All staff members</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected(request('actor')==$member->id)>{{ $member->name }}</option>@endforeach</select><button class="h-10 rounded-xl admin-theme-bg px-5 text-xs font-bold text-white">Apply</button>@if(request()->hasAny(['search','actor']))<a href="{{ route('admin.activity.index') }}" class="flex h-10 items-center justify-center rounded-xl border border-slate-200 px-4 text-xs font-bold text-slate-600">Clear</a>@endif</form></div>
        <div class="overflow-x-auto"><table class="w-full"><thead><tr><th>Date & time</th><th>Staff member</th><th>Administrative action</th><th>Module / route</th><th>IP address</th></tr></thead><tbody class="divide-y divide-slate-100">
            @forelse($logs as $log)
                @php
                    $methodClass = match($log->method) {'POST'=>'bg-emerald-50 text-emerald-700','PUT','PATCH'=>'bg-sky-50 text-sky-700','DELETE'=>'bg-red-50 text-red-700',default=>'bg-slate-100 text-slate-600'};
                    $routeParts = explode('.', str_replace('admin.','',$log->route_name ?? 'admin'));
                    $module = ucfirst(str_replace('-', ' ', $routeParts[0] ?? 'Admin'));
                @endphp
                <tr class="hover:bg-slate-50/60"><td class="whitespace-nowrap px-5"><p class="text-xs font-bold text-slate-700">{{ $log->created_at->format('d M Y') }}</p><p class="mt-0.5 text-[10px] text-slate-400">{{ $log->created_at->format('h:i:s A') }}</p></td><td class="px-5"><div class="flex items-center gap-2.5"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full admin-theme-soft text-[10px] font-extrabold">{{ strtoupper(substr($log->actor?->name ?? 'D',0,1)) }}</span><div><p class="text-xs font-bold text-slate-800">{{ $log->actor?->name ?? 'Deleted admin' }}</p><p class="max-w-[170px] truncate text-[10px] text-slate-400">{{ $log->actor?->email }}</p></div></div></td><td class="px-5"><div class="flex items-center gap-2"><span class="rounded-md px-2 py-1 text-[9px] font-extrabold {{ $methodClass }}">{{ $log->method }}</span><p class="text-xs text-slate-700">{{ $log->description }}</p></div></td><td class="px-5"><p class="text-xs font-bold text-slate-700">{{ $module }}</p><code class="text-[9px] admin-theme-text">{{ $log->route_name }}</code></td><td class="whitespace-nowrap px-5 font-mono text-[10px] text-slate-500">{{ $log->ip_address }}</td></tr>
            @empty<tr><td colspan="5" class="px-6 py-14 text-center"><span class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"><i class="fas fa-clock-rotate-left"></i></span><p class="mt-3 text-sm font-bold text-slate-800">No activity found</p><p class="mt-1 text-xs text-slate-500">Successful admin changes will automatically appear here.</p></td></tr>@endforelse
        </tbody></table></div>
        @if($logs->hasPages())<div class="border-t border-slate-200 px-5 py-4">{{ $logs->links() }}</div>@endif
    </section>
</div>
@endsection

@push('styles')
<style>
    .audit-summary{display:flex;align-items:center;overflow:hidden;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.audit-summary span{display:flex;min-width:76px;flex-direction:column;padding:8px 14px;border-right:1px solid #e2e8f0;color:#94a3b8;font-size:8px;font-weight:800;text-transform:uppercase}.audit-summary strong{color:#0f172a;font-size:14px;line-height:16px}.audit-summary em{display:flex;align-items:center;gap:5px;padding:0 13px;color:#059669;font-size:9px;font-style:normal;font-weight:800}.audit-filters{display:grid;grid-template-columns:minmax(260px,1fr) 220px 90px auto;gap:8px;align-items:center}
    @media(max-width:850px){.audit-summary{width:100%}.audit-summary span{flex:1}.audit-filters{grid-template-columns:1fr 1fr}.audit-filters button,.audit-filters a{width:100%}}
</style>
@endpush
