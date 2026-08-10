@extends('layouts.admin')
@section('title', 'Roles & Permissions')

@section('admin-content')
@php
    $firstRole = $roles->first()?->slug;
    $permissionRows = [
        ['Dashboard', 'Admin overview and operational summary', 'fa-chart-pie', 'dashboard.view', null],
        ['Property listings', 'Rooms, options and moderation', 'fa-building', 'listings.view', 'listings.manage'],
        ['Users & owners', 'Member accounts and access', 'fa-users', 'people.view', 'people.manage'],
        ['Support desk', 'Complaints, enquiries and alerts', 'fa-headset', 'support.view', 'support.manage'],
        ['Finance & plans', 'Payments, payouts and subscriptions', 'fa-wallet', 'finance.view', 'finance.manage'],
        ['Website content', 'CMS, blogs, homepage and offers', 'fa-pen-to-square', 'content.view', 'content.manage'],
        ['Reports', 'View reports or delete analytics history', 'fa-chart-line', 'reports.view', 'reports.manage'],
        ['Business settings', 'Configuration and maintenance', 'fa-gear', null, 'settings.manage'],
        ['Staff & roles', 'Staff accounts and permissions', 'fa-user-shield', null, 'staff.manage'],
        ['Activity logs', 'Administrative audit history', 'fa-clock-rotate-left', 'activity.view', null],
    ];
@endphp
<div class="space-y-4 p-5 lg:p-6" x-data="{ activeRole: @js($firstRole) }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="text-[10px] font-extrabold uppercase tracking-[.18em] admin-theme-text">Access control</p><h1 class="mt-1 text-xl font-extrabold text-slate-950">Roles & Permissions</h1><p class="mt-1 text-xs text-slate-500">Choose a role and control exactly what its staff can view or manage.</p></div><div class="flex gap-2"><a href="{{ route('admin.staff.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700"><i class="fas fa-users-gear admin-theme-text"></i>Manage staff</a><a href="{{ route('admin.roles.create') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl admin-theme-bg px-4 text-xs font-bold text-white"><i class="fas fa-plus"></i>Create custom role</a></div></div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700">{{ $errors->first() }}</div>@endif

    <div class="role-workspace">
        <aside class="role-list-panel">
            <div class="border-b border-slate-200 px-4 py-3"><p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Roles</p><p class="mt-1 text-xs text-slate-500">{{ $roles->count() }} access profiles</p></div>
            <div class="space-y-1 p-2">
                @foreach($roles as $role)
                    <button type="button" @click="activeRole='{{ $role->slug }}'" :class="activeRole==='{{ $role->slug }}' ? 'admin-theme-bg shadow-sm' : 'text-slate-700 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left">
                        <span :class="activeRole==='{{ $role->slug }}' ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"><i class="fas {{ $role->slug==='super_admin'?'fa-crown':($role->slug==='finance_manager'?'fa-coins':($role->slug==='support_executive'?'fa-headset':'fa-user-shield')) }} text-[11px]"></i></span>
                        <span class="min-w-0 flex-1"><strong class="block truncate text-xs">{{ $role->name }}</strong><small :class="activeRole==='{{ $role->slug }}' ? 'text-white/70' : 'text-slate-400'" class="block text-[9px]">{{ $role->staff_count }} staff</small></span><i class="fas fa-chevron-right text-[8px] opacity-50"></i>
                    </button>
                @endforeach
            </div>
            <div class="m-2 rounded-xl bg-slate-50 p-3 text-[10px] leading-4 text-slate-500"><strong class="text-slate-700">View</strong> = read only<br><strong class="text-slate-700">Manage</strong> = create/change/delete</div>
        </aside>

        <section class="min-w-0">
            @foreach($roles as $role)
                <form x-show="activeRole==='{{ $role->slug }}'" x-cloak method="POST" action="{{ route('admin.roles.update',$role) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">@csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $role->name }}">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-key"></i></span><div><h2 class="text-base font-extrabold text-slate-900">{{ $role->name }}</h2><p class="text-[10px] text-slate-500">{{ $role->slug }} · {{ $role->staff_count }} assigned staff</p></div></div><span class="w-fit rounded-full admin-theme-soft px-3 py-1 text-[9px] font-extrabold uppercase admin-theme-text">{{ $role->is_system?'System role':'Custom role' }}</span></div>
                    <div class="p-5"><div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_230px]"><div><label class="text-[11px] font-bold text-slate-700">Role purpose</label><input name="description" value="{{ $role->description }}" @readonly($role->slug==='super_admin') class="mt-1 w-full rounded-xl bg-slate-50 text-xs"></div><div class="rounded-xl bg-slate-50 px-4 py-3"><p class="text-[9px] font-extrabold uppercase text-slate-400">Access summary</p><p class="mt-1 text-sm font-extrabold text-slate-900">{{ in_array('*',$role->permissions) ? 'Full access' : count($role->permissions).' of '.count($catalog).' permissions' }}</p></div></div>
                        @if($role->slug==='super_admin')
                            <div class="mt-4 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4"><i class="fas fa-crown mt-0.5 text-amber-600"></i><div><p class="text-xs font-extrabold text-amber-900">Complete platform control</p><p class="mt-1 text-[11px] text-amber-700">Super Admin automatically receives every current and future permission. It cannot be restricted.</p></div></div>
                        @else
                            <div class="mt-4 overflow-hidden rounded-xl border border-slate-200"><div class="permission-head"><span>Module</span><span>View</span><span>Manage</span></div><div class="divide-y divide-slate-100">
                                @foreach($permissionRows as [$module,$description,$icon,$viewKey,$manageKey])
                                    <div class="permission-row"><div class="flex min-w-0 items-center gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas {{ $icon }} text-[10px]"></i></span><span class="min-w-0"><strong class="block text-xs text-slate-800">{{ $module }}</strong><small class="block truncate text-[9px] text-slate-400">{{ $description }}</small></span></div><div class="text-center">@if($viewKey)<label class="permission-check"><input type="checkbox" name="permissions[]" value="{{ $viewKey }}" @checked(in_array($viewKey,$role->permissions))><span><i class="fas fa-check"></i></span></label>@else<span class="text-slate-300">—</span>@endif</div><div class="text-center">@if($manageKey)<label class="permission-check manage"><input type="checkbox" name="permissions[]" value="{{ $manageKey }}" @checked(in_array($manageKey,$role->permissions))><span><i class="fas fa-check"></i></span></label>@else<span class="text-slate-300">—</span>@endif</div></div>
                                @endforeach
                            </div></div>
                            <div class="mt-4 flex items-center justify-between"><p class="text-[10px] text-slate-500"><i class="fas fa-circle-info mr-1 admin-theme-text"></i>Give only the access required for this job.</p><button class="rounded-xl admin-theme-bg px-5 py-2.5 text-xs font-bold text-white"><i class="fas fa-floppy-disk mr-2"></i>Save permissions</button></div>
                        @endif
                    </div>
                </form>
            @endforeach
        </section>
    </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-roles-index.css') }}">
@endpush
