@extends('layouts.admin')
@section('title', 'Data Maintenance')

@section('admin-content')
@php
    $formatBytes = function ($bytes) {
        if ($bytes === null) return 'Not available';
        $units = ['B','KB','MB','GB','TB'];
        $power = $bytes > 0 ? min((int) floor(log($bytes, 1024)), count($units) - 1) : 0;
        return number_format($bytes / (1024 ** $power), $power ? 1 : 0).' '.$units[$power];
    };
    $eligibleLabels = [
        'otps' => 'Expired OTPs', 'sessions' => 'Old sessions', 'locked_enquiries' => 'Abandoned unlock attempts',
        'stale_payments' => 'Pending/failed payments', 'analytics_events' => 'Analytics events',
        'search_logs' => 'Search logs', 'activity_logs' => 'Admin activity logs',
        'read_contact_messages' => 'Read contact messages',
    ];
    $retentionFields = [
        'otp_days' => ['Expired OTPs', 'Verification codes that can no longer be used.'],
        'session_days' => ['Inactive sessions', 'Old browser login sessions.'],
        'payment_days' => ['Failed attempts', 'Pending/failed payments and locked enquiries only.'],
        'analytics_days' => ['Analytics events', 'Detailed traffic and conversion events.'],
        'search_log_days' => ['Search logs', 'Historical room-search terms and filters.'],
        'activity_log_days' => ['Admin activity logs', 'Successful administrative action history.'],
        'contact_message_days' => ['Read contact messages', 'Only messages already marked as read.'],
    ];
    $totalEligible = collect($eligible)->sum();
    $orphanBytes = collect($orphanMedia)->sum('bytes');
@endphp

<div class="space-y-6 p-5 lg:p-7">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-xs font-bold uppercase tracking-widest admin-theme-text">System health</p><h1 class="mt-1 text-2xl font-extrabold text-slate-950">Data Maintenance</h1><p class="mt-1 text-sm text-slate-500">Control data growth without removing genuine users, rooms, completed payments or unlocked contacts.</p></div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-700"><i class="fas fa-shield-halved mr-2"></i>Protected business records are never auto-deleted</div>
    </div>

    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"><i class="fas fa-circle-exclamation mr-2"></i>{{ $errors->first() }}</div>@endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border bg-white p-5 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-database"></i></span><p class="mt-4 text-xs font-bold uppercase text-slate-400">Database size</p><strong class="mt-1 block text-xl text-slate-900">{{ $formatBytes($summary['database_bytes']) }}</strong></div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-hard-drive"></i></span><p class="mt-4 text-xs font-bold uppercase text-slate-400">Public storage</p><strong class="mt-1 block text-xl text-slate-900">{{ $formatBytes($summary['storage_bytes']) }}</strong></div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-broom"></i></span><p class="mt-4 text-xs font-bold uppercase text-slate-400">Cleanup eligible</p><strong class="mt-1 block text-xl text-slate-900">{{ number_format($totalEligible) }} records</strong></div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-clock"></i></span><p class="mt-4 text-xs font-bold uppercase text-slate-400">Last cleanup</p><strong class="mt-1 block text-sm text-slate-900">{{ $summary['last_run_at'] ? \Carbon\Carbon::parse($summary['last_run_at'])->diffForHumans() : 'Not run yet' }}</strong></div>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-6">
        <div class="mb-5"><h2 class="font-extrabold text-slate-900">Current data volume</h2><p class="mt-1 text-sm text-slate-500">Quick visibility into the main business tables.</p></div>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">@foreach($summary['counts'] as $item)<div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-xs font-semibold text-slate-500">{{ $item['label'] }}</p><strong class="mt-1 block text-lg text-slate-900">{{ number_format($item['count']) }}</strong></div>@endforeach</div>
    </section>

    @if(count($summary['last_result']))
    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-sm font-extrabold text-emerald-900"><i class="fas fa-clock-rotate-left mr-2"></i>Last cleanup result</h2><p class="mt-1 text-xs text-emerald-700">{{ $summary['last_run_at'] ? \Carbon\Carbon::parse($summary['last_run_at'])->format('d M Y, h:i A') : '' }}</p></div><div class="flex flex-wrap gap-2">@foreach($summary['last_result'] as $key=>$count)@continue($key === 'orphan_media_bytes' || !$count)<span class="rounded-lg bg-white px-3 py-2 text-[10px] font-bold text-emerald-800">{{ ucfirst(str_replace('_',' ',$key)) }}: {{ number_format($count) }}</span>@endforeach</div></div></section>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <form action="{{ route('admin.data-maintenance.update') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-6">@csrf @method('PUT')
            <div><h2 class="font-extrabold text-slate-900">Retention settings</h2><p class="mt-1 text-sm text-slate-500">Choose how long temporary operational data should remain.</p></div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2">@foreach($retentionFields as $key => [$label,$help])<label class="rounded-xl border border-slate-200 p-4"><span class="text-xs font-bold text-slate-800">{{ $label }}</span><span class="mt-1 block min-h-8 text-[11px] leading-4 text-slate-500">{{ $help }}</span><span class="mt-3 flex items-center gap-2"><input type="number" name="{{ $key }}" min="1" max="3650" required value="{{ old($key,$retention[$key]) }}" class="h-10 w-full rounded-xl border-slate-200 text-sm"><em class="text-xs not-italic text-slate-500">days</em></span></label>@endforeach</div>
            <button class="mt-5 rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white "><i class="fas fa-floppy-disk mr-2"></i>Save retention settings</button>
        </form>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-6"><div class="flex items-start justify-between gap-4"><div><h2 class="font-extrabold text-slate-900">Cleanup preview</h2><p class="mt-1 text-sm text-slate-500">These records currently match your retention rules.</p></div><span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">{{ number_format($totalEligible) }} total</span></div><div class="mt-4 divide-y divide-slate-100">@foreach($eligible as $key=>$count)<div class="flex items-center justify-between py-2.5 text-xs"><span class="font-semibold text-slate-600">{{ $eligibleLabels[$key] }}</span><strong class="text-slate-900">{{ number_format($count) }}</strong></div>@endforeach</div></section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-6"><div class="flex items-start justify-between gap-4"><div><h2 class="font-extrabold text-slate-900">Orphan room media</h2><p class="mt-1 text-sm text-slate-500">Files under room storage that are not linked to any room.</p></div><span class="rounded-full admin-theme-soft px-3 py-1 text-xs font-bold">{{ count($orphanMedia) }} files - {{ $formatBytes($orphanBytes) }}</span></div>@if(count($orphanMedia))<div class="mt-4 max-h-44 space-y-2 overflow-y-auto">@foreach(array_slice($orphanMedia,0,20) as $file)<div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2 text-[11px]"><span class="truncate text-slate-600" title="{{ $file['path'] }}">{{ $file['path'] }}</span><strong class="shrink-0 text-slate-500">{{ $formatBytes($file['bytes']) }}</strong></div>@endforeach</div>@else<div class="mt-4 rounded-xl bg-emerald-50 p-4 text-xs font-semibold text-emerald-700"><i class="fas fa-circle-check mr-2"></i>No orphan room media found.</div>@endif</section>
        </div>
    </div>

    <form action="{{ route('admin.data-maintenance.cleanup') }}" method="POST" class="admin-confirm rounded-2xl border border-amber-200 bg-amber-50 p-5 lg:p-6" data-confirm-title="Run data cleanup?" data-confirm-text="The previewed records will be removed and this action cannot be undone." data-confirm-button="Yes, run cleanup">@csrf
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"><div><h2 class="font-extrabold text-amber-950"><i class="fas fa-triangle-exclamation mr-2"></i>Run cleanup now</h2><p class="mt-1 text-sm text-amber-800">Only eligible temporary records shown above will be removed. Successful payments, real unlocks, rooms and members remain safe.</p><label class="mt-4 flex items-center gap-2 text-xs font-bold text-amber-950"><input type="checkbox" name="delete_orphan_media" value="1" class="rounded border-amber-300 admin-theme-text">Also delete {{ count($orphanMedia) }} orphan room media files</label></div><div class="shrink-0"><label class="mb-3 flex items-center gap-2 text-xs font-bold text-amber-950"><input type="checkbox" name="confirm_cleanup" value="1" required class="rounded border-amber-300 admin-theme-text">I reviewed the cleanup preview</label><button class="w-full rounded-xl bg-amber-600 px-6 py-3 text-sm font-bold text-white hover:bg-amber-700"><i class="fas fa-broom mr-2"></i>Run Cleanup Now</button></div></div>
    </form>
</div>
@endsection
