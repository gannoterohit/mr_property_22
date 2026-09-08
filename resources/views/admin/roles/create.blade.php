@extends('layouts.admin')
@section('title', 'Create Custom Role')

@section('admin-content')
@php
    $departments = [
        [
            'id' => 'dept-property',
            'title' => 'Property & Listings',
            'icon' => 'fa-building',
            'icon_color' => 'text-indigo-600 bg-indigo-50 border-indigo-100',
            'desc' => 'Manage property listings, room options, amenities, and owner identity verification.',
            'items' => [
                [
                    'name' => 'Property Listings',
                    'desc' => 'Rooms, photos, amenities, categories & rejection reasons',
                    'icon' => 'fa-door-open',
                    'view_key' => 'listings.view',
                    'view_label' => 'View Rooms',
                    'manage_key' => 'listings.manage',
                    'manage_label' => 'Approve / Edit',
                ],
                [
                    'name' => 'Users & Owners',
                    'desc' => 'Member accounts, owner profiles & KYC identity verifications',
                    'icon' => 'fa-users',
                    'view_key' => 'people.view',
                    'view_label' => 'View Profiles',
                    'manage_key' => 'people.manage',
                    'manage_label' => 'Verify / Block',
                ],
            ]
        ],
        [
            'id' => 'dept-support',
            'title' => 'Customer Care & Support',
            'icon' => 'fa-headset',
            'icon_color' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
            'desc' => 'Resolve tenant complaints, enquiry tickets, contact requests, and city alerts.',
            'items' => [
                [
                    'name' => 'Support & Helpdesk',
                    'desc' => 'Complaints, ticket replies, enquiries, alerts & subscribers',
                    'icon' => 'fa-envelope-open-text',
                    'view_key' => 'support.view',
                    'view_label' => 'View Tickets',
                    'manage_key' => 'support.manage',
                    'manage_label' => 'Reply & Resolve',
                ],
            ]
        ],
        [
            'id' => 'dept-brokers',
            'title' => 'Broker & Agency Network',
            'icon' => 'fa-handshake',
            'icon_color' => 'text-purple-600 bg-purple-50 border-purple-100',
            'desc' => 'Broker onboarding, agency verification, public reviews, and broker packages.',
            'items' => [
                [
                    'name' => 'Brokers & Agencies',
                    'desc' => 'Broker directory, agency KYC approvals & review moderation',
                    'icon' => 'fa-id-card',
                    'view_key' => 'brokers.view',
                    'view_label' => 'View Brokers',
                    'manage_key' => 'brokers.manage',
                    'manage_label' => 'Approve / Verify',
                ],
                [
                    'name' => 'Broker Packages',
                    'desc' => 'Broker listing subscription tiers and credit packages',
                    'icon' => 'fa-layer-group',
                    'view_key' => null,
                    'view_label' => null,
                    'manage_key' => 'brokers.plans.manage',
                    'manage_label' => 'Manage Plans',
                ],
                [
                    'name' => 'Broker Settings',
                    'desc' => 'Broker contact unlock rules and commission parameters',
                    'icon' => 'fa-sliders',
                    'view_key' => null,
                    'view_label' => null,
                    'manage_key' => 'brokers.settings',
                    'manage_label' => 'Configure Rules',
                ],
            ]
        ],
        [
            'id' => 'dept-finance',
            'title' => 'Finance & Billing',
            'icon' => 'fa-wallet',
            'icon_color' => 'text-amber-600 bg-amber-50 border-amber-100',
            'desc' => 'Track listing revenues, unlock fees, subscription plans, and owner payouts.',
            'items' => [
                [
                    'name' => 'Payments & Payouts',
                    'desc' => 'Transaction history, revenue logs & owner payout settlements',
                    'icon' => 'fa-money-bill-transfer',
                    'view_key' => 'finance.view',
                    'view_label' => 'View Payments',
                    'manage_key' => 'finance.manage',
                    'manage_label' => 'Process Payouts',
                ],
            ]
        ],
        [
            'id' => 'dept-content',
            'title' => 'Marketing & Content Growth',
            'icon' => 'fa-bullhorn',
            'icon_color' => 'text-rose-600 bg-rose-50 border-rose-100',
            'desc' => 'Manage promotional banner offers, CMS pages, blogs, and marketing assets.',
            'items' => [
                [
                    'name' => 'Website Content & Blogs',
                    'desc' => 'Blog articles, promotional offers, homepage CMS & testimonials',
                    'icon' => 'fa-pen-to-square',
                    'view_key' => 'content.view',
                    'view_label' => 'View Content',
                    'manage_key' => 'content.manage',
                    'manage_label' => 'Publish / Edit',
                ],
            ]
        ],
        [
            'id' => 'dept-reports',
            'title' => 'Analytics & Intelligence',
            'icon' => 'fa-chart-line',
            'icon_color' => 'text-cyan-600 bg-cyan-50 border-cyan-100',
            'desc' => 'Operational reports, search demand insights, and visitor analytics.',
            'items' => [
                [
                    'name' => 'Reports & Search Logs',
                    'desc' => 'System reports, city search trends & search analytics logs',
                    'icon' => 'fa-magnifying-glass-chart',
                    'view_key' => 'reports.view',
                    'view_label' => 'View Reports',
                    'manage_key' => 'reports.manage',
                    'manage_label' => 'Manage Analytics',
                ],
            ]
        ],
        [
            'id' => 'dept-system',
            'title' => 'Governance & System Security',
            'icon' => 'fa-gear',
            'icon_color' => 'text-slate-600 bg-slate-100 border-slate-200',
            'desc' => 'Core administrative operations, staff roles, system settings, and database backups.',
            'items' => [
                [
                    'name' => 'Admin Dashboard',
                    'desc' => 'High-level operational summaries and analytics counters',
                    'icon' => 'fa-chart-pie',
                    'view_key' => 'dashboard.view',
                    'view_label' => 'View Dashboard',
                    'manage_key' => null,
                    'manage_label' => null,
                ],
                [
                    'name' => 'Activity Audit Logs',
                    'desc' => 'Track staff logins, listing modifications, and security logs',
                    'icon' => 'fa-clock-rotate-left',
                    'view_key' => 'activity.view',
                    'view_label' => 'View Logs',
                    'manage_key' => null,
                    'manage_label' => null,
                ],
                [
                    'name' => 'Database Backup',
                    'desc' => 'Create and download full database SQL backups',
                    'icon' => 'fa-database',
                    'view_key' => 'data.backup',
                    'view_label' => 'Download Backup',
                    'manage_key' => null,
                    'manage_label' => null,
                ],
                [
                    'name' => 'Staff & Roles',
                    'desc' => 'Create admin staff accounts and assign access roles',
                    'icon' => 'fa-user-shield',
                    'view_key' => null,
                    'view_label' => null,
                    'manage_key' => 'staff.manage',
                    'manage_label' => 'Manage Staff',
                ],
                [
                    'name' => 'Business Settings',
                    'desc' => 'System configuration, maintenance mode & active cities',
                    'icon' => 'fa-sliders',
                    'view_key' => null,
                    'view_label' => null,
                    'manage_key' => 'settings.manage',
                    'manage_label' => 'Edit Settings',
                ],
            ]
        ],
    ];

    $totalPermissions = 21;
@endphp

<div class="space-y-6 p-5 lg:p-7">
    {{-- Navigation Breadcrumb & Title Bar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-5">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i> Roles & Permissions
            </a>
            <h1 class="mt-2 text-2xl font-extrabold text-slate-950 flex items-center gap-3">
                <span>Create Custom Role</span>
                <span class="rounded-full bg-indigo-50 border border-indigo-100 px-3 py-1 text-[11px] font-bold text-indigo-700">Access Profile</span>
            </h1>
            <p class="mt-1 text-xs text-slate-500">Configure role responsibilities and toggle granular View/Manage permissions for your staff members.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.roles.index') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-xs transition">
                <i class="fas fa-times mr-1.5 text-slate-400"></i> Cancel
            </a>
            <button type="button" onclick="document.getElementById('createRoleForm').requestSubmit()" class="inline-flex h-10 items-center justify-center rounded-xl admin-theme-bg px-5 text-xs font-bold text-white shadow-sm hover:opacity-95 transition gap-2">
                <i class="fas fa-plus"></i> Create Role
            </button>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-700 flex items-center gap-3 shadow-xs">
            <i class="fas fa-circle-exclamation text-red-600 text-base"></i>
            <div>
                <p class="font-extrabold">Please check the form for errors:</p>
                <p class="mt-0.5 font-normal">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    {{-- Fast Role Presets Bar --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-indigo-700 flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 text-xs"><i class="fas fa-wand-magic-sparkles"></i></span>
                    1-Click Role Presets (Fast Setup)
                </h2>
                <p class="text-xs text-slate-500 mt-1">Select a pre-built industry template to automatically configure role details and permissions in 1 second.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="selectAllPermissions()" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                    <i class="fas fa-check-double text-indigo-600"></i> Select All
                </button>
                <button type="button" onclick="selectViewOnlyPermissions()" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                    <i class="fas fa-eye text-amber-600"></i> View Only
                </button>
                <button type="button" onclick="clearAllPermissions()" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                    <i class="fas fa-xmark text-red-500"></i> Clear All
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <button type="button" onclick="applyRolePreset('property_ops', this)" class="preset-card text-left p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-indigo-500 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 text-xs">
                        <i class="fas fa-building"></i>
                    </span>
                    <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">6 Perms</span>
                </div>
                <strong class="block text-xs font-extrabold text-slate-900 group-hover:text-indigo-600 mt-2.5">Listing Inspector</strong>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-snug line-clamp-2">Approve listings, verify photos, rejection reasons & owner KYC</p>
            </button>

            <button type="button" onclick="applyRolePreset('support', this)" class="preset-card text-left p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-emerald-500 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 text-xs">
                        <i class="fas fa-headset"></i>
                    </span>
                    <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">5 Perms</span>
                </div>
                <strong class="block text-xs font-extrabold text-slate-900 group-hover:text-emerald-600 mt-2.5">Customer Support</strong>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-snug line-clamp-2">Resolve complaints, ticket replies, enquiries & emergency alerts</p>
            </button>

            <button type="button" onclick="applyRolePreset('broker_mgr', this)" class="preset-card text-left p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-purple-500 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600 text-xs">
                        <i class="fas fa-handshake"></i>
                    </span>
                    <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">6 Perms</span>
                </div>
                <strong class="block text-xs font-extrabold text-slate-900 group-hover:text-purple-600 mt-2.5">Broker Manager</strong>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-snug line-clamp-2">Onboard realtors, verify agencies, reviews & broker packages</p>
            </button>

            <button type="button" onclick="applyRolePreset('finance', this)" class="preset-card text-left p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-amber-500 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600 text-xs">
                        <i class="fas fa-wallet"></i>
                    </span>
                    <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">5 Perms</span>
                </div>
                <strong class="block text-xs font-extrabold text-slate-900 group-hover:text-amber-600 mt-2.5">Finance Officer</strong>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-snug line-clamp-2">Process owner bank payouts & manage listing subscription plans</p>
            </button>

            <button type="button" onclick="applyRolePreset('growth', this)" class="preset-card text-left p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:border-rose-500 hover:shadow-sm transition group">
                <div class="flex items-center justify-between">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-600 text-xs">
                        <i class="fas fa-bullhorn"></i>
                    </span>
                    <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">5 Perms</span>
                </div>
                <strong class="block text-xs font-extrabold text-slate-900 group-hover:text-rose-600 mt-2.5">Marketing & Growth</strong>
                <p class="text-[10px] text-slate-500 mt-0.5 leading-snug line-clamp-2">Publish blog articles, promotional banner offers & SEO content</p>
            </button>
        </div>
    </div>

    {{-- Main Workspace Form with Sticky Side Summary --}}
    <form method="POST" action="{{ route('admin.roles.store') }}" id="createRoleForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-6 items-start">
            {{-- Left Column: Role Details & Permissions Matrix --}}
            <div class="space-y-6 min-w-0">
                {{-- Role Basic Info Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-id-card-clip text-indigo-600"></i> Role Identification
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Assign an intuitive name and summary of responsibilities for this role profile.</p>

                    <div class="grid gap-4 sm:grid-cols-2 mt-4">
                        <div>
                            <label for="roleNameInput" class="block text-xs font-bold text-slate-700">Role Title <span class="text-red-500">*</span></label>
                            <input type="text" id="roleNameInput" name="name" value="{{ old('name') }}" required placeholder="e.g. Property Inspection Officer" oninput="updateLiveSummary()" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs font-bold text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-indigo-500 focus:outline-none shadow-xs transition">
                            <p class="text-[10px] text-slate-400 mt-1">Appears in staff dropdowns and activity logs.</p>
                        </div>
                        <div>
                            <label for="roleDescInput" class="block text-xs font-bold text-slate-700">Purpose / Department Scope</label>
                            <input type="text" id="roleDescInput" name="description" value="{{ old('description') }}" placeholder="What this staff member handles" oninput="updateLiveSummary()" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-indigo-500 focus:outline-none shadow-xs transition">
                            <p class="text-[10px] text-slate-400 mt-1">Helpful reference when delegating tasks.</p>
                        </div>
                    </div>
                </div>

                {{-- Permissions Modules --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                        <div>
                            <h2 class="text-sm font-extrabold text-slate-900">Module Permissions Matrix</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Toggle exact permissions for each section. Selecting Manage automatically inherits View.</p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] font-bold">
                            <span class="flex items-center gap-1.5 text-indigo-700"><span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span> View</span>
                            <span class="flex items-center gap-1.5 text-emerald-700"><span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span> Manage</span>
                        </div>
                    </div>

                    @foreach($departments as $dept)
                        <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
                            <div class="bg-slate-50/80 px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg border text-xs {{ $dept['icon_color'] }}">
                                        <i class="fas {{ $dept['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-xs font-extrabold text-slate-900">{{ $dept['title'] }}</h3>
                                        <p class="text-[10px] text-slate-500 truncate max-w-sm">{{ $dept['desc'] }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ count($dept['items']) }} {{ count($dept['items']) === 1 ? 'Module' : 'Modules' }}</span>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach($dept['items'] as $item)
                                    <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-slate-50/50 transition">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 text-xs mt-0.5">
                                                <i class="fas {{ $item['icon'] }}"></i>
                                            </span>
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-900">{{ $item['name'] }}</h4>
                                                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">{{ $item['desc'] }}</p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto pl-11 sm:pl-0">
                                            {{-- View Pill Checkbox --}}
                                            @if(!empty($item['view_key']))
                                                <label class="perm-pill perm-pill-view" title="Allow viewing {{ $item['name'] }}">
                                                    <input type="checkbox" name="permissions[]" value="{{ $item['view_key'] }}" data-key="{{ $item['view_key'] }}" class="perm-checkbox perm-view-cb" onchange="syncManageDependency(this); updateLiveSummary();" @checked(in_array($item['view_key'], old('permissions', [])))>
                                                    <span>
                                                        <i class="fas fa-eye text-[10px]"></i>
                                                        {{ $item['view_label'] ?: 'View' }}
                                                    </span>
                                                </label>
                                            @endif

                                            {{-- Manage Pill Checkbox --}}
                                            @if(!empty($item['manage_key']))
                                                <label class="perm-pill perm-pill-manage" title="Allow managing/editing {{ $item['name'] }}">
                                                    <input type="checkbox" name="permissions[]" value="{{ $item['manage_key'] }}" data-key="{{ $item['manage_key'] }}" class="perm-checkbox perm-manage-cb" onchange="autoEnableView(this); updateLiveSummary();" @checked(in_array($item['manage_key'], old('permissions', [])))>
                                                    <span>
                                                        <i class="fas fa-pen-to-square text-[10px]"></i>
                                                        {{ $item['manage_label'] ?: 'Manage' }}
                                                    </span>
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Right Column: Sticky Summary & Action Panel --}}
            <aside class="sticky top-20 space-y-4">
                {{-- Live Role Preview Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Live Role Summary</h3>
                        <span id="summaryBadge" class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-bold text-slate-600">Custom</span>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Role Title</p>
                        <p id="summaryName" class="text-base font-extrabold text-slate-900 truncate mt-0.5">Untitled Role</p>
                        <p id="summaryDesc" class="text-xs text-slate-500 mt-1 line-clamp-2 leading-snug italic">No description provided</p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3.5 border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Access Coverage</span>
                            <span id="summaryPermCount" class="text-xs font-extrabold text-indigo-700">0 / {{ $totalPermissions }}</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-200 mt-2 overflow-hidden">
                            <div id="summaryProgressBar" class="h-full bg-indigo-600 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p id="summaryCoverageText" class="text-[10px] text-slate-400 mt-1.5">No permissions assigned yet</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-xl admin-theme-bg py-3 text-xs font-bold text-white shadow-sm hover:opacity-95 transition">
                            <i class="fas fa-check"></i> Save & Create Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="w-full mt-2 flex items-center justify-center rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                            Cancel
                        </a>
                    </div>
                </div>

                {{-- Reference: Existing Roles Card --}}
                @if(isset($existingRoles) && $existingRoles->isNotEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-2">Existing Roles ({{ $existingRoles->count() }})</h4>
                        <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                            @foreach($existingRoles as $r)
                                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 text-xs">
                                    <span class="font-bold text-slate-700 truncate max-w-[180px]">{{ $r->name }}</span>
                                    <span class="text-[10px] font-semibold text-slate-400">{{ $r->staff_count }} staff</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Modern Permission Pill Toggle Buttons */
.perm-pill {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
    position: relative;
}
.perm-pill input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.perm-pill span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 11px;
    border-radius: 9px;
    border: 1.5px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
}
.perm-pill:hover span {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #334155;
}
/* View Checked State */
.perm-pill-view input:checked + span {
    border-color: #4f46e5;
    background: #eef2ff;
    color: #4338ca;
    box-shadow: 0 1px 4px rgba(79, 70, 229, 0.18);
}
.perm-pill-view input:checked + span i {
    color: #4f46e5;
}
/* Manage Checked State */
.perm-pill-manage input:checked + span {
    border-color: #059669;
    background: #ecfdf5;
    color: #047857;
    box-shadow: 0 1px 4px rgba(5, 150, 105, 0.18);
}
.perm-pill-manage input:checked + span i {
    color: #059669;
}
/* Active Preset Card */
.preset-card.active-preset {
    border-color: #4f46e5 !important;
    background: #ffffff !important;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.12) !important;
}
</style>
@endpush

@push('scripts')
<script>
const TOTAL_PERMISSIONS = {{ $totalPermissions }};

function updateLiveSummary() {
    const nameInput = document.getElementById('roleNameInput');
    const descInput = document.getElementById('roleDescInput');
    const summaryName = document.getElementById('summaryName');
    const summaryDesc = document.getElementById('summaryDesc');
    const summaryCount = document.getElementById('summaryPermCount');
    const progressBar = document.getElementById('summaryProgressBar');
    const coverageText = document.getElementById('summaryCoverageText');

    if (summaryName) summaryName.textContent = nameInput.value.trim() || 'Untitled Role';
    if (summaryDesc) summaryDesc.textContent = descInput.value.trim() || 'No description provided';

    const checkedBoxes = document.querySelectorAll('.perm-checkbox:checked');
    const count = checkedBoxes.length;
    const percent = Math.min(100, Math.round((count / TOTAL_PERMISSIONS) * 100));

    if (summaryCount) summaryCount.textContent = `${count} / ${TOTAL_PERMISSIONS}`;
    if (progressBar) progressBar.style.width = `${percent}%`;
    if (coverageText) {
        if (count === 0) coverageText.textContent = 'No permissions assigned yet';
        else if (count >= TOTAL_PERMISSIONS) coverageText.textContent = 'Full platform coverage (Unrestricted)';
        else coverageText.textContent = `${percent}% operational coverage`;
    }
}

function autoEnableView(manageCheckbox) {
    if (manageCheckbox.checked) {
        const row = manageCheckbox.closest('.p-4');
        const viewCheckbox = row ? row.querySelector('.perm-view-cb') : null;
        if (viewCheckbox) {
            viewCheckbox.checked = true;
        }
    }
}

function syncManageDependency(viewCheckbox) {
    if (!viewCheckbox.checked) {
        const row = viewCheckbox.closest('.p-4');
        const manageCheckbox = row ? row.querySelector('.perm-manage-cb') : null;
        if (manageCheckbox) {
            manageCheckbox.checked = false;
        }
    }
}

function selectAllPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.preset-card').forEach(p => p.classList.remove('active-preset'));
    updateLiveSummary();
}

function selectViewOnlyPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = cb.classList.contains('perm-view-cb');
    });
    document.querySelectorAll('.preset-card').forEach(p => p.classList.remove('active-preset'));
    updateLiveSummary();
}

function clearAllPermissions() {
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.preset-card').forEach(p => p.classList.remove('active-preset'));
    updateLiveSummary();
}

function applyRolePreset(presetKey, buttonEl) {
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

    const data = presets[presetKey];
    if (!data) return;

    document.getElementById('roleNameInput').value = data.name;
    document.getElementById('roleDescInput').value = data.desc;

    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        if (data.keys.includes(cb.dataset.key)) {
            cb.checked = true;
        }
    });

    document.querySelectorAll('.preset-card').forEach(p => p.classList.remove('active-preset'));
    if (buttonEl) buttonEl.classList.add('active-preset');

    updateLiveSummary();
}

document.addEventListener('DOMContentLoaded', () => {
    updateLiveSummary();
});
</script>
@endpush
@endsection
