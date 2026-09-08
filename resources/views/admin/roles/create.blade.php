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
        ['Database backup', 'Create and download database backups', 'fa-database', 'data.backup', null],
        ['Brokers', 'Broker profiles, approval and listings', 'fa-handshake', 'brokers.view', 'brokers.manage'],
        ['Broker settings', 'Broker pricing and module settings', 'fa-sliders', null, 'brokers.settings'],
        ['Broker plans', 'Manage broker subscription plans', 'fa-layer-group', null, 'brokers.plans.manage'],
    ];
@endphp
<div class="space-y-4 p-5 lg:p-6" x-data="roleCreateForm()">
    <div>
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-500 admin-theme-hover-text">
            <i class="fas fa-arrow-left"></i>Roles & Permissions
        </a>
        <h1 class="mt-2 text-xl font-extrabold text-slate-950">Create Custom Role</h1>
        <p class="mt-1 text-xs text-slate-500">Create a focused access profile for a specific staff responsibility or pick a quick preset.</p>
    </div>

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-red-600"></i>{{ $errors->first() }}
        </div>
    @endif

    {{-- Quick Role Presets for 10+ Employees --}}
    <div class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50/70 to-slate-50 p-4">
        <p class="text-[10px] font-extrabold uppercase tracking-wider text-indigo-700 mb-2 flex items-center gap-1.5">
            <i class="fas fa-wand-magic-sparkles"></i> 1-Click Role Presets (Fast Setup for Staff)
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <button type="button" @click="applyPreset('property_ops')" class="flex flex-col text-left p-2.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:shadow-xs transition">
                <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5"><i class="fas fa-building text-indigo-600"></i> Listing Inspector</span>
                <span class="text-[9px] text-slate-500 mt-1">Approve/verify rooms & manage owners</span>
            </button>
            <button type="button" @click="applyPreset('support')" class="flex flex-col text-left p-2.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:shadow-xs transition">
                <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5"><i class="fas fa-headset text-emerald-600"></i> Support Care</span>
                <span class="text-[9px] text-slate-500 mt-1">Handle complaints & customer enquiries</span>
            </button>
            <button type="button" @click="applyPreset('finance')" class="flex flex-col text-left p-2.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:shadow-xs transition">
                <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5"><i class="fas fa-wallet text-amber-600"></i> Finance Officer</span>
                <span class="text-[9px] text-slate-500 mt-1">Process payouts & view transactions</span>
            </button>
            <button type="button" @click="applyPreset('broker_mgr')" class="flex flex-col text-left p-2.5 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:shadow-xs transition">
                <span class="text-xs font-extrabold text-slate-900 flex items-center gap-1.5"><i class="fas fa-handshake text-purple-600"></i> Broker Manager</span>
                <span class="text-[9px] text-slate-500 mt-1">Approve brokers & manage plans</span>
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @csrf
        
        <div class="grid gap-4 border-b border-slate-200 bg-slate-50/60 p-5 md:grid-cols-2">
            <div>
                <label class="text-xs font-bold text-slate-700">Role Title *</label>
                <input name="name" x-model="roleName" value="{{ old('name') }}" required placeholder="e.g. Listing Verification Officer" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:outline-none shadow-sm">
            </div>
            <div>
                <label class="text-xs font-bold text-slate-700">Role Purpose / Description</label>
                <input name="description" x-model="roleDesc" value="{{ old('description') }}" placeholder="What this staff member handles" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none shadow-sm">
            </div>
        </div>

        <div class="p-5">
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Module Permissions Access</h2>
                    <p class="mt-0.5 text-[10px] text-slate-500">Select View for read-only access and Manage for creating, updating, or deleting records.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex gap-3 text-[10px] font-bold mr-2">
                        <span class="admin-theme-text"><i class="fas fa-square mr-1"></i>View</span>
                        <span class="text-emerald-600"><i class="fas fa-square mr-1"></i>Manage</span>
                    </div>
                    <button type="button" @click="selectAll()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs">
                        <i class="fas fa-check-double mr-1 text-indigo-600"></i>Select All
                    </button>
                    <button type="button" @click="selectViewOnly()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs">
                        <i class="fas fa-eye mr-1 text-amber-600"></i>View Only
                    </button>
                    <button type="button" @click="clearAll()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs">
                        <i class="fas fa-xmark mr-1 text-red-500"></i>Clear All
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">
                <div class="permission-head">
                    <span>Module</span>
                    <span>View</span>
                    <span>Manage</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($permissionRows as [$module, $description, $icon, $viewKey, $manageKey])
                        <div class="permission-row hover:bg-slate-50/50 transition">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                    <i class="fas {{ $icon }} text-[10px]"></i>
                                </span>
                                <span>
                                    <strong class="block text-xs text-slate-800">{{ $module }}</strong>
                                    <small class="text-[9px] text-slate-400">{{ $description }}</small>
                                </span>
                            </div>
                            <div class="text-center">
                                @if($viewKey)
                                    <label class="permission-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $viewKey }}" data-key="{{ $viewKey }}" @checked(in_array($viewKey, old('permissions', [])))>
                                        <span><i class="fas fa-check"></i></span>
                                    </label>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </div>
                            <div class="text-center">
                                @if($manageKey)
                                    <label class="permission-check manage">
                                        <input type="checkbox" name="permissions[]" value="{{ $manageKey }}" data-key="{{ $manageKey }}" @checked(in_array($manageKey, old('permissions', [])))>
                                        <span><i class="fas fa-check"></i></span>
                                    </label>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
            <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">Cancel</a>
            <button class="rounded-xl admin-theme-bg px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:opacity-95 transition">
                <i class="fas fa-plus mr-2"></i>Create Role
            </button>
        </div>
    </form>
</div>

<script>
function roleCreateForm() {
    return {
        roleName: @js(old('name', '')),
        roleDesc: @js(old('description', '')),
        applyPreset(preset) {
            this.clearAll();
            const presets = {
                property_ops: {
                    name: 'Listing & Property Inspector',
                    desc: 'Responsible for reviewing, approving or rejecting property listings and owner accounts.',
                    keys: ['dashboard.view', 'listings.view', 'listings.manage', 'people.view', 'people.manage']
                },
                support: {
                    name: 'Customer Support Specialist',
                    desc: 'Responsible for resolving tenant/owner complaints, support enquiries and platform alerts.',
                    keys: ['dashboard.view', 'support.view', 'support.manage', 'people.view', 'listings.view']
                },
                finance: {
                    name: 'Finance & Payouts Officer',
                    desc: 'Manages platform payment collections, owner payout requests and subscription plans.',
                    keys: ['dashboard.view', 'finance.view', 'finance.manage', 'reports.view', 'people.view']
                },
                broker_mgr: {
                    name: 'Broker Partner Executive',
                    desc: 'Handles broker KYC approvals, agency spotlights, broker reviews and broker packages.',
                    keys: ['dashboard.view', 'brokers.view', 'brokers.manage', 'brokers.plans.manage', 'people.view']
                }
            };

            const data = presets[preset];
            if (!data) return;
            this.roleName = data.name;
            this.roleDesc = data.desc;
            
            document.querySelectorAll('input[type=checkbox][data-key]').forEach(cb => {
                if (data.keys.includes(cb.dataset.key)) {
                    cb.checked = true;
                }
            });
        },
        selectAll() {
            document.querySelectorAll('input[type=checkbox][name="permissions[]"]').forEach(cb => cb.checked = true);
        },
        selectViewOnly() {
            document.querySelectorAll('input[type=checkbox][name="permissions[]"]').forEach(cb => {
                cb.checked = !cb.closest('label').classList.contains('manage');
            });
        },
        clearAll() {
            document.querySelectorAll('input[type=checkbox][name="permissions[]"]').forEach(cb => cb.checked = false);
        }
    };
}
</script>
@endsection

@push('styles')
<style>
    .permission-head,.permission-row{display:grid;grid-template-columns:minmax(260px,1fr) 100px 100px;align-items:center}.permission-head{padding:9px 16px;background:#f8fafc;color:#64748b;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.permission-head span:not(:first-child){text-align:center}.permission-row{min-height:54px;padding:7px 16px}.permission-check{display:inline-flex;cursor:pointer}.permission-check input{position:absolute;opacity:0;pointer-events:none}.permission-check span{display:flex;width:28px;height:28px;align-items:center;justify-content:center;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:transparent;font-size:10px}.permission-check input:checked+span{border-color:#4f46e5;background:#4f46e5;color:#fff}.permission-check.manage input:checked+span{border-color:#059669;background:#059669}@media(max-width:700px){.permission-head,.permission-row{grid-template-columns:minmax(160px,1fr) 65px 65px;padding-left:10px;padding-right:10px}}
</style>
@endpush
