@extends('layouts.admin')
@section('title', 'Create Custom Role')

@section('admin-content')
@php
    $permissionRows = [
        ['Dashboard', 'Admin overview and operational summary', 'fa-chart-pie', 'dashboard.view', null],
        ['Property listings', 'Rooms, options and moderation', 'fa-building', 'listings.view', 'listings.manage'],
        ['Users & owners', 'Member accounts and access', 'fa-users', 'people.view', 'people.manage'],
        ['Support desk', 'Complaints, enquiries and alerts', 'fa-headset', 'support.view', 'support.manage'],
        ['Finance & plans', 'Payments, payouts and subscriptions', 'fa-wallet', 'finance.view', 'finance.manage'],
        ['Website content', 'CMS, blogs, homepage and offers', 'fa-pen-to-square', 'content.view', 'content.manage'],
        ['Reports', 'View reports or delete analytics history', 'fa-chart-line', 'reports.view', 'reports.manage'],
        ['Business settings', 'Configuration and maintenance', 'fa-gear', null, 'settings.manage'],
        ['Staff & roles', 'Staff accounts and role permissions', 'fa-user-shield', null, 'staff.manage'],
        ['Activity logs', 'Administrative audit history', 'fa-clock-rotate-left', 'activity.view', null],
    ];
@endphp
<div class="space-y-4 p-5 lg:p-6">
    <div><a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-500 admin-theme-hover-text"><i class="fas fa-arrow-left"></i>Roles & Permissions</a><h1 class="mt-2 text-xl font-extrabold text-slate-950">Create Custom Role</h1><p class="mt-1 text-xs text-slate-500">Create a focused access profile for a specific staff responsibility.</p></div>
    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.roles.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">@csrf
        <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 p-5 md:grid-cols-2"><div><label class="text-xs font-bold text-slate-700">Role name *</label><input name="name" value="{{ old('name') }}" required placeholder="Example: City Moderator" class="mt-1.5 w-full rounded-xl bg-white"></div><div><label class="text-xs font-bold text-slate-700">Role purpose</label><input name="description" value="{{ old('description') }}" placeholder="What this staff member handles" class="mt-1.5 w-full rounded-xl bg-white"></div></div>
        <div class="p-5"><div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><h2 class="text-sm font-extrabold text-slate-900">Permission access</h2><p class="mt-1 text-[10px] text-slate-500">Select View for read-only access and Manage for creating or changing records.</p></div><div class="flex gap-3 text-[10px] font-bold"><span class="admin-theme-text"><i class="fas fa-square mr-1"></i>View</span><span class="text-emerald-600"><i class="fas fa-square mr-1"></i>Manage</span></div></div>
            <div class="overflow-hidden rounded-xl border border-slate-200"><div class="permission-head"><span>Module</span><span>View</span><span>Manage</span></div><div class="divide-y divide-slate-100">@foreach($permissionRows as [$module,$description,$icon,$viewKey,$manageKey])<div class="permission-row"><div class="flex min-w-0 items-center gap-3"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"><i class="fas {{ $icon }} text-[10px]"></i></span><span><strong class="block text-xs">{{ $module }}</strong><small class="text-[9px] text-slate-400">{{ $description }}</small></span></div><div class="text-center">@if($viewKey)<label class="permission-check"><input type="checkbox" name="permissions[]" value="{{ $viewKey }}" @checked(in_array($viewKey,old('permissions',[])))><span><i class="fas fa-check"></i></span></label>@else<span class="text-slate-300">—</span>@endif</div><div class="text-center">@if($manageKey)<label class="permission-check manage"><input type="checkbox" name="permissions[]" value="{{ $manageKey }}" @checked(in_array($manageKey,old('permissions',[])))><span><i class="fas fa-check"></i></span></label>@else<span class="text-slate-300">—</span>@endif</div></div>@endforeach</div></div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4"><a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600">Cancel</a><button class="rounded-xl admin-theme-bg px-5 py-2.5 text-xs font-bold text-white"><i class="fas fa-plus mr-2"></i>Create role</button></div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .permission-head,.permission-row{display:grid;grid-template-columns:minmax(260px,1fr) 100px 100px;align-items:center}.permission-head{padding:9px 16px;background:#f8fafc;color:#64748b;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.permission-head span:not(:first-child){text-align:center}.permission-row{min-height:54px;padding:7px 16px}.permission-check{display:inline-flex;cursor:pointer}.permission-check input{position:absolute;opacity:0;pointer-events:none}.permission-check span{display:flex;width:28px;height:28px;align-items:center;justify-content:center;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:transparent;font-size:10px}.permission-check input:checked+span{border-color:#4f46e5;background:#4f46e5;color:#fff}.permission-check.manage input:checked+span{border-color:#059669;background:#059669}@media(max-width:700px){.permission-head,.permission-row{grid-template-columns:minmax(160px,1fr) 65px 65px;padding-left:10px;padding-right:10px}}
</style>
@endpush
