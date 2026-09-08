@extends('layouts.admin')
@section('title', 'Create Custom Role')

@section('admin-content')
@php
    $departments = [
        [
            'title' => 'Property & Inventory Operations',
            'icon' => 'fa-building',
            'desc' => 'Manage property listings, room options, amenities, and owner identity verification.',
            'badge' => 'Core Operations',
            'items' => [
                [
                    'name' => 'Property Listings',
                    'desc' => 'Rooms, photos, amenities, categories & rejection reasons',
                    'icon' => 'fa-door-open',
                    'view_key' => 'listings.view',
                    'manage_key' => 'listings.manage',
                ],
                [
                    'name' => 'Users & Property Owners',
                    'desc' => 'Member accounts, owner profiles & KYC identity verifications',
                    'icon' => 'fa-users',
                    'view_key' => 'people.view',
                    'manage_key' => 'people.manage',
                ],
            ]
        ],
        [
            'title' => 'Customer Care & Support Desk',
            'icon' => 'fa-headset',
            'desc' => 'Resolve tenant and owner complaints, inquiry tickets, and platform alerts.',
            'badge' => 'Customer Service',
            'items' => [
                [
                    'name' => 'Support & Helpdesk',
                    'desc' => 'Complaints, ticket replies, enquiries, alerts & subscribers',
                    'icon' => 'fa-envelope-open-text',
                    'view_key' => 'support.view',
                    'manage_key' => 'support.manage',
                ],
            ]
        ],
        [
            'title' => 'Broker & Agency Network',
            'icon' => 'fa-handshake',
            'desc' => 'Broker onboarding, agency verification, public reviews, and broker packages.',
            'badge' => 'Partnerships',
            'items' => [
                [
                    'name' => 'Brokers & Agencies',
                    'desc' => 'Broker directory, agency KYC approvals & review moderation',
                    'icon' => 'fa-id-card',
                    'view_key' => 'brokers.view',
                    'manage_key' => 'brokers.manage',
                ],
                [
                    'name' => 'Broker Packages & Plans',
                    'desc' => 'Broker listing subscription tiers and credit packages',
                    'icon' => 'fa-layer-group',
                    'view_key' => null,
                    'manage_key' => 'brokers.plans.manage',
                ],
                [
                    'name' => 'Broker Module Settings',
                    'desc' => 'Broker contact unlock rules and commission parameters',
                    'icon' => 'fa-sliders',
                    'view_key' => null,
                    'manage_key' => 'brokers.settings',
                ],
            ]
        ],
        [
            'title' => 'Finance, Billing & Payouts',
            'icon' => 'fa-wallet',
            'desc' => 'Track listing revenues, unlock fees, subscription plans, and owner payouts.',
            'badge' => 'Financial Control',
            'items' => [
                [
                    'name' => 'Payments & Payouts',
                    'desc' => 'Transaction history, revenue logs & owner payout processing',
                    'icon' => 'fa-money-bill-transfer',
                    'view_key' => 'finance.view',
                    'manage_key' => 'finance.manage',
                ],
            ]
        ],
        [
            'title' => 'Marketing, CMS & Growth',
            'icon' => 'fa-bullhorn',
            'desc' => 'Manage promotional banner offers, CMS pages, blogs, and marketing assets.',
            'badge' => 'Content & Growth',
            'items' => [
                [
                    'name' => 'Website Content & Blogs',
                    'desc' => 'Blog articles, promotional offers, homepage CMS & testimonials',
                    'icon' => 'fa-pen-to-square',
                    'view_key' => 'content.view',
                    'manage_key' => 'content.manage',
                ],
            ]
        ],
        [
            'title' => 'Analytics & Search Trends',
            'icon' => 'fa-chart-line',
            'desc' => 'Operational reports, search demand insights, and visitor analytics.',
            'badge' => 'Intelligence',
            'items' => [
                [
                    'name' => 'Reports & Search Logs',
                    'desc' => 'System reports, city search trends & search analytics logs',
                    'icon' => 'fa-magnifying-glass-chart',
                    'view_key' => 'reports.view',
                    'manage_key' => 'reports.manage',
                ],
            ]
        ],
        [
            'title' => 'Governance, Security & System',
            'icon' => 'fa-gear',
            'desc' => 'Core administrative operations, staff roles, system settings, and database backups.',
            'badge' => 'Administration',
            'items' => [
                [
                    'name' => 'Admin Dashboard',
                    'desc' => 'High-level operational summaries and analytics counters',
                    'icon' => 'fa-chart-pie',
                    'view_key' => 'dashboard.view',
                    'manage_key' => null,
                ],
                [
                    'name' => 'Activity Audit Logs',
                    'desc' => 'Track staff logins, listing modifications, and security logs',
                    'icon' => 'fa-clock-rotate-left',
                    'view_key' => 'activity.view',
                    'manage_key' => null,
                ],
                [
                    'name' => 'Database Backup',
                    'desc' => 'Create and download full database SQL backups',
                    'icon' => 'fa-database',
                    'view_key' => 'data.backup',
                    'manage_key' => null,
                ],
                [
                    'name' => 'Staff & Role Delegation',
                    'desc' => 'Create admin staff accounts and assign access roles',
                    'icon' => 'fa-user-shield',
                    'view_key' => null,
                    'manage_key' => 'staff.manage',
                ],
                [
                    'name' => 'Business & Platform Settings',
                    'desc' => 'System configuration, maintenance mode & active cities',
                    'icon' => 'fa-sliders',
                    'view_key' => null,
                    'manage_key' => 'settings.manage',
                ],
            ]
        ],
    ];
@endphp

<div class="space-y-6 p-5 lg:p-7">
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i>Back to Roles & Permissions
            </a>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950">Create Custom Role</h1>
            <p class="mt-1 text-xs text-slate-500">Define a specialized operational profile with custom module permissions for your staff.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                Cancel
            </a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-bold text-red-700 flex items-center gap-2 shadow-xs">
            <i class="fas fa-circle-exclamation text-red-600"></i>{{ $errors->first() }}
        </div>
    @endif

    {{-- Quick 1-Click Role Presets --}}
    <div class="rounded-2xl border border-indigo-100 bg-gradient-to-r from-indigo-50/80 via-white to-slate-50 p-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 flex items-center gap-1.5">
                    <i class="fas fa-wand-magic-sparkles"></i> 1-Click Role Presets
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Click any template below to auto-fill title, scope, and recommended permissions instantly.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="selectAllPermissions()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                    <i class="fas fa-check-double mr-1 text-indigo-600"></i>Select All
                </button>
                <button type="button" onclick="selectViewOnlyPermissions()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                    <i class="fas fa-eye mr-1 text-amber-600"></i>View Only
                </button>
                <button type="button" onclick="clearAllPermissions()" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                    <i class="fas fa-xmark mr-1 text-red-500"></i>Clear All
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
            <button type="button" onclick="applyRolePreset('property_ops')" class="flex flex-col text-left p-3 rounded-xl border border-slate-200 bg-white hover:border-indigo-500 hover:shadow-sm transition group">
                <span class="text-xs font-extrabold text-slate-900 group-hover:text-indigo-600 flex items-center gap-1.5">
                    <i class="fas fa-building text-indigo-600"></i> Listing Inspector
                </span>
                <span class="text-[10px] text-slate-500 mt-1 leading-snug">Verify rooms, reject fake photos & manage owner KYC</span>
            </button>
            <button type="button" onclick="applyRolePreset('support')" class="flex flex-col text-left p-3 rounded-xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-sm transition group">
                <span class="text-xs font-extrabold text-slate-900 group-hover:text-emerald-600 flex items-center gap-1.5">
                    <i class="fas fa-headset text-emerald-600"></i> Customer Support
                </span>
                <span class="text-[10px] text-slate-500 mt-1 leading-snug">Resolve tenant complaints & respond to enquiries</span>
            </button>
            <button type="button" onclick="applyRolePreset('broker_mgr')" class="flex flex-col text-left p-3 rounded-xl border border-slate-200 bg-white hover:border-purple-500 hover:shadow-sm transition group">
                <span class="text-xs font-extrabold text-slate-900 group-hover:text-purple-600 flex items-center gap-1.5">
                    <i class="fas fa-handshake text-purple-600"></i> Broker Manager
                </span>
                <span class="text-[10px] text-slate-500 mt-1 leading-snug">Onboard realtors, verify agencies & moderate reviews</span>
            </button>
            <button type="button" onclick="applyRolePreset('finance')" class="flex flex-col text-left p-3 rounded-xl border border-slate-200 bg-white hover:border-amber-500 hover:shadow-sm transition group">
                <span class="text-xs font-extrabold text-slate-900 group-hover:text-amber-600 flex items-center gap-1.5">
                    <i class="fas fa-wallet text-amber-600"></i> Finance Officer
                </span>
                <span class="text-[10px] text-slate-500 mt-1 leading-snug">Process owner payouts & track subscription revenues</span>
            </button>
            <button type="button" onclick="applyRolePreset('growth')" class="flex flex-col text-left p-3 rounded-xl border border-slate-200 bg-white hover:border-rose-500 hover:shadow-sm transition group">
                <span class="text-xs font-extrabold text-slate-900 group-hover:text-rose-600 flex items-center gap-1.5">
                    <i class="fas fa-bullhorn text-rose-600"></i> Growth & Marketing
                </span>
                <span class="text-[10px] text-slate-500 mt-1 leading-snug">Publish blogs, create discount offers & CMS pages</span>
            </button>
        </div>
    </div>

    {{-- Main Workspace Form --}}
    <form method="POST" action="{{ route('admin.roles.store') }}" id="createRoleForm" class="space-y-6">
        @csrf

        {{-- Basic Information Card --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-slate-50/60 px-5 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900">Role Information</h2>
                    <p class="text-[11px] text-slate-500">Provide a descriptive title and purpose for this operational role.</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-extrabold uppercase text-indigo-700">New Role Profile</span>
            </div>
            <div class="p-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="roleNameInput" class="text-xs font-bold text-slate-700">Role Title <span class="text-red-500">*</span></label>
                    <input type="text" id="roleNameInput" name="name" value="{{ old('name') }}" required placeholder="e.g. Property Inspection Officer" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:border-indigo-500 focus:outline-none shadow-xs">
                    <p class="text-[10px] text-slate-400 mt-1">This will be displayed across staff management and permission guards.</p>
                </div>
                <div>
                    <label for="roleDescInput" class="text-xs font-bold text-slate-700">Role Purpose / Department Scope</label>
                    <input type="text" id="roleDescInput" name="description" value="{{ old('description') }}" placeholder="What this staff member handles" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-indigo-500 focus:outline-none shadow-xs">
                    <p class="text-[10px] text-slate-400 mt-1">Brief summary of responsibilities assigned to this role.</p>
                </div>
            </div>
        </div>

        {{-- Categorized Permissions Grid --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-extrabold text-slate-950">Module Permissions Matrix</h2>
                    <p class="text-xs text-slate-500">Toggle exact View (read-only) and Manage (create/edit/delete) privileges across all departments.</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5 text-indigo-700"><span class="h-3 w-3 rounded bg-indigo-600 inline-block"></span> View (Read-Only)</span>
                    <span class="flex items-center gap-1.5 text-emerald-700"><span class="h-3 w-3 rounded bg-emerald-600 inline-block"></span> Manage (Full Action)</span>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($departments as $dept)
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
                        <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white shadow-xs text-slate-700 border border-slate-200">
                                    <i class="fas {{ $dept['icon'] }} text-[12px]"></i>
                                </span>
                                <div>
                                    <h3 class="text-xs font-extrabold text-slate-900">{{ $dept['title'] }}</h3>
                                    <p class="text-[10px] text-slate-400 truncate max-w-[240px]">{{ $dept['desc'] }}</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[9px] font-extrabold uppercase text-slate-600">{{ $dept['badge'] }}</span>
                        </div>

                        <div class="divide-y divide-slate-100 p-2">
                            @foreach($dept['items'] as $item)
                                <div class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition">
                                    <div class="min-w-0 pr-3">
                                        <div class="flex items-center gap-2">
                                            <i class="fas {{ $item['icon'] }} text-[11px] text-slate-400"></i>
                                            <strong class="text-xs font-bold text-slate-800">{{ $item['name'] }}</strong>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-0.5 truncate max-w-[280px]">{{ $item['desc'] }}</p>
                                    </div>

                                    <div class="flex items-center gap-2 shrink-0">
                                        {{-- View Checkbox --}}
                                        @if(!empty($item['view_key']))
                                            <label class="permission-check" title="Grant View access for {{ $item['name'] }}">
                                                <input type="checkbox" name="permissions[]" value="{{ $item['view_key'] }}" data-key="{{ $item['view_key'] }}" class="perm-checkbox perm-view" @checked(in_array($item['view_key'], old('permissions', [])))>
                                                <span><i class="fas fa-check"></i></span>
                                            </label>
                                        @else
                                            <span class="w-7 text-center text-slate-300 text-xs">—</span>
                                        @endif

                                        {{-- Manage Checkbox --}}
                                        @if(!empty($item['manage_key']))
                                            <label class="permission-check manage" title="Grant Manage/Edit access for {{ $item['name'] }}">
                                                <input type="checkbox" name="permissions[]" value="{{ $item['manage_key'] }}" data-key="{{ $item['manage_key'] }}" class="perm-checkbox perm-manage" @checked(in_array($item['manage_key'], old('permissions', [])))>
                                                <span><i class="fas fa-check"></i></span>
                                            </label>
                                        @else
                                            <span class="w-7 text-center text-slate-300 text-xs">—</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Bottom Action Footer --}}
        <div class="sticky bottom-4 z-20 flex flex-col sm:flex-row items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur-md">
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <i class="fas fa-shield-halved text-indigo-600 text-base"></i>
                <span>Manage permissions automatically grant read-only view access upon saving.</span>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl admin-theme-bg px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:opacity-95 transition flex items-center gap-2">
                    <i class="fas fa-check"></i>Create Role
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function selectAllPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
}

function selectViewOnlyPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = cb.classList.contains('perm-view');
    });
}

function clearAllPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
}

function applyRolePreset(preset) {
    clearAllPermissions();

    const presets = {
        property_ops: {
            name: 'Property & Listing Operations',
            desc: 'Reviews property listings, room amenities, moderation flags, and owner KYC verifications.',
            keys: ['dashboard.view', 'listings.view', 'listings.manage', 'people.view', 'people.manage', 'reports.view']
        },
        support: {
            name: 'Customer Support & Helpdesk',
            desc: 'Handles tenant complaints, customer queries, emergency alerts, and scam report tickets.',
            keys: ['dashboard.view', 'support.view', 'support.manage', 'people.view', 'listings.view']
        },
        broker_mgr: {
            name: 'Broker & Agency Manager',
            desc: 'Onboards real estate brokers, verifies agencies, moderates reviews, and manages broker plans.',
            keys: ['dashboard.view', 'brokers.view', 'brokers.manage', 'brokers.plans.manage', 'people.view', 'listings.view']
        },
        finance: {
            name: 'Finance & Payouts Officer',
            desc: 'Manages platform fee collections, owner bank payout requests, and subscription pricing.',
            keys: ['dashboard.view', 'finance.view', 'finance.manage', 'reports.view', 'people.view']
        },
        growth: {
            name: 'Marketing & Content Growth',
            desc: 'Publishes blog articles, promotional banner offers, CMS pages, and reviews search trends.',
            keys: ['dashboard.view', 'content.view', 'content.manage', 'reports.view', 'listings.view']
        }
    };

    const data = presets[preset];
    if (!data) return;

    document.getElementById('roleNameInput').value = data.name;
    document.getElementById('roleDescInput').value = data.desc;

    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        if (data.keys.includes(cb.dataset.key)) {
            cb.checked = true;
        }
    });
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-misc.css') }}">
@endpush
