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
        ['Database backup', 'Create and download database backups', 'fa-database', 'data.backup', null],
        ['Brokers', 'Broker profiles, approval and listings', 'fa-handshake', 'brokers.view', 'brokers.manage'],
        ['Broker settings', 'Broker pricing and module settings', 'fa-sliders', null, 'brokers.settings'],
        ['Broker plans', 'Manage broker subscription plans', 'fa-layer-group', null, 'brokers.plans.manage'],
    ];
@endphp
<div class="space-y-4 p-5 lg:p-6" x-data="{ activeRole: @js($firstRole) }">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.18em] admin-theme-text">Access control & Delegation</p>
            <h1 class="mt-1 text-xl font-extrabold text-slate-950">Roles & Permissions</h1>
            <p class="mt-1 text-xs text-slate-500">Configure roles and assign precise granular permissions for all your staff members.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.staff.index') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i class="fas fa-users-gear admin-theme-text"></i>Manage staff ({{ $roles->sum('staff_count') }})
            </a>
            <a href="{{ route('admin.roles.create') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl admin-theme-bg px-4 text-xs font-bold text-white shadow-sm hover:opacity-95 transition">
                <i class="fas fa-plus"></i>Create custom role
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-bold text-emerald-800 flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>{{ session('success') }}
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-red-600"></i>{{ $errors->first() }}
        </div>
    @endif

    <div class="role-workspace">
        <aside class="role-list-panel">
            <div class="border-b border-slate-200 px-4 py-3">
                <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Configured Roles</p>
                <p class="mt-1 text-xs text-slate-500">{{ $roles->count() }} access profiles</p>
            </div>
            <div class="space-y-1 p-2">
                @foreach($roles as $role)
                    <button type="button" @click="activeRole='{{ $role->slug }}'" :class="activeRole==='{{ $role->slug }}' ? 'admin-theme-bg shadow-sm' : 'text-slate-700 hover:bg-slate-50'" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition">
                        <span :class="activeRole==='{{ $role->slug }}' ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-500'" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg">
                            <i class="fas {{ match($role->slug) {
                                'super_admin' => 'fa-crown',
                                'property_operations_manager' => 'fa-building',
                                'customer_support_executive' => 'fa-headset',
                                'broker_operations_manager' => 'fa-handshake',
                                'finance_accounts_officer' => 'fa-coins',
                                'content_growth_manager' => 'fa-bullhorn',
                                default => 'fa-user-shield'
                            } }} text-[11px]"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <strong class="block truncate text-xs">{{ $role->name }}</strong>
                            <small :class="activeRole==='{{ $role->slug }}' ? 'text-white/80' : 'text-slate-400'" class="block text-[9px]">{{ $role->staff_count }} assigned staff</small>
                        </span>
                        <i class="fas fa-chevron-right text-[8px] opacity-50"></i>
                    </button>
                @endforeach
            </div>
            <div class="m-2 rounded-xl bg-slate-50 p-3 text-[10px] leading-4 text-slate-500 border border-slate-100">
                <strong class="text-slate-700 font-bold"><i class="fas fa-eye text-indigo-500 mr-1"></i>View:</strong> Read-only access<br>
                <strong class="text-slate-700 font-bold"><i class="fas fa-pen-to-square text-emerald-500 mr-1"></i>Manage:</strong> Create, edit, approve, delete
            </div>
        </aside>

        <section class="min-w-0">
            @foreach($roles as $role)
                <form x-show="activeRole==='{{ $role->slug }}'" x-cloak method="POST" action="{{ route('admin.roles.update', $role) }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between bg-slate-50/50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft">
                                <i class="fas {{ $role->slug === 'super_admin' ? 'fa-crown text-amber-500' : 'fa-shield-halved text-indigo-600' }}"></i>
                            </span>
                            <div>
                                <h2 class="text-base font-extrabold text-slate-900">{{ $role->name }}</h2>
                                <p class="text-[10px] text-slate-500">
                                    Slug: <code>{{ $role->slug }}</code> · 
                                    <a href="{{ route('admin.staff.index', ['admin_role_id' => $role->id]) }}" class="font-bold text-indigo-600 hover:underline">
                                        <i class="fas fa-users mr-0.5"></i>{{ $role->staff_count }} staff assigned
                                    </a>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full admin-theme-soft px-3 py-1 text-[9px] font-extrabold uppercase admin-theme-text">
                                {{ $role->is_system ? 'System Role' : 'Custom Role' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-bold text-slate-700">Role Title *</label>
                                @if($role->slug === 'super_admin')
                                    <input type="text" name="name" value="{{ $role->name }}" readonly class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 cursor-not-allowed">
                                @else
                                    <input type="text" name="name" value="{{ $role->name }}" required class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:outline-none shadow-sm">
                                @endif
                            </div>
                            <div>
                                <label class="text-[11px] font-bold text-slate-700">Role Description / Scope</label>
                                <input type="text" name="description" value="{{ $role->description }}" @readonly($role->slug==='super_admin') placeholder="e.g. Handles listing approvals and verification" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        @if($role->slug === 'super_admin')
                            <div class="mt-5 flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                <i class="fas fa-crown mt-0.5 text-amber-600 text-lg"></i>
                                <div>
                                    <p class="text-xs font-extrabold text-amber-900">Complete Platform Authority</p>
                                    <p class="mt-1 text-[11px] text-amber-700 leading-relaxed">
                                        The Super Admin profile possesses unrestricted system access across all existing and future modules. Its permissions cannot be reduced to ensure platform management continuity.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="mt-5 flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                                <div>
                                    <h3 class="text-xs font-extrabold text-slate-900">Granular Module Permissions</h3>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Toggle exact View and Manage rights for this role.</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="$el.closest('form').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = true)" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                                        <i class="fas fa-check-double mr-1 text-indigo-600"></i>Select All
                                    </button>
                                    <button type="button" @click="$el.closest('form').querySelectorAll('input[type=checkbox]').forEach(c => { c.checked = !c.closest('label').classList.contains('manage'); })" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                                        <i class="fas fa-eye mr-1 text-amber-600"></i>View Only
                                    </button>
                                    <button type="button" @click="$el.closest('form').querySelectorAll('input[type=checkbox]').forEach(c => c.checked = false)" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                                        <i class="fas fa-xmark mr-1 text-red-500"></i>Clear All
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 overflow-hidden rounded-xl border border-slate-200">
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
                                                    <i class="fas {{ $icon }} text-[11px]"></i>
                                                </span>
                                                <span class="min-w-0">
                                                    <strong class="block text-xs text-slate-800">{{ $module }}</strong>
                                                    <small class="block truncate text-[9px] text-slate-400">{{ $description }}</small>
                                                </span>
                                            </div>
                                            <div class="text-center">
                                                @if($viewKey)
                                                    <label class="permission-check" title="Allow reading {{ $module }}">
                                                        <input type="checkbox" name="permissions[]" value="{{ $viewKey }}" @checked(in_array($viewKey, $role->permissions))>
                                                        <span><i class="fas fa-check"></i></span>
                                                    </label>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </div>
                                            <div class="text-center">
                                                @if($manageKey)
                                                    <label class="permission-check manage" title="Allow modifying/deleting {{ $module }}">
                                                        <input type="checkbox" name="permissions[]" value="{{ $manageKey }}" @checked(in_array($manageKey, $role->permissions))>
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

                            <div class="mt-5 flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                                <p class="text-[10px] text-slate-500">
                                    <i class="fas fa-shield-check mr-1 admin-theme-text"></i>Manage permission automatically inherits read-only View access.
                                </p>
                                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                                    @if(!$role->is_system && $role->slug !== 'super_admin')
                                        <button type="button" onclick="confirmDeleteRole('{{ $role->id }}', '{{ addslashes($role->name) }}', {{ $role->staff_count }})" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-xs font-bold text-red-600 hover:bg-red-100 transition">
                                            <i class="fas fa-trash-can mr-1.5"></i>Delete Role
                                        </button>
                                    @endif
                                    <button type="submit" class="rounded-xl admin-theme-bg px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:opacity-95 transition">
                                        <i class="fas fa-floppy-disk mr-2"></i>Save Permissions
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </form>
            @endforeach
        </section>
    </div>

    {{-- Hidden Delete Role Form --}}
    <form id="deleteRoleForm" method="POST" action="" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function confirmDeleteRole(roleId, roleName, staffCount) {
    if (staffCount > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Role is In Use',
            text: `Cannot delete "${roleName}" because ${staffCount} staff member(s) are currently assigned to it. Please reassign them in Staff Management first.`,
            confirmButtonText: 'Go to Staff Management',
            showCancelButton: true,
            cancelButtonText: 'Close',
            confirmButtonColor: '#4f46e5'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.staff.index') }}?admin_role_id=" + roleId;
            }
        });
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: `Delete "${roleName}"?`,
        text: 'Are you sure you want to permanently delete this custom role? This action cannot be undone.',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete Role',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc2626',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteRoleForm');
            form.action = `/admin/roles/${roleId}`;
            form.submit();
        }
    });
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-misc.css') }}">

@endpush
