@extends('layouts.admin')

@section('title','Broker Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-theme-text text-[10px] font-extrabold uppercase tracking-[.2em]">People management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Brokers</h1>
            <p class="mt-1 text-sm text-slate-500">Manage broker registrations, verification and access.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="brokers" />
        </div>
    </header>

    @include('admin.members.nav')

    <section class="people-kpis admin-kpis">
        @foreach([
            ['Total brokers',$stats['total'],'fa-handshake','admin-theme-text','admin-theme-soft'],
            ['Pending',$stats['pending'],'fa-clock','text-amber-600','bg-amber-50'],
            ['Approved',$stats['approved'],'fa-circle-check','text-emerald-600','bg-emerald-50'],
            ['Rejected',$stats['rejected'],'fa-circle-xmark','text-red-600','bg-red-50'],
        ] as [$label,$value,$icon,$tone,$bg])
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $bg }} {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                </div>
                <p class="mt-3 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <form class="owner-filter rounded-2xl border bg-white p-4 shadow-sm">
        <div class="relative">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search name, email or agency..." class="h-11 w-full rounded-xl border-slate-200 pl-10 text-sm">
        </div>
        <select name="verification_status" class="h-11 rounded-xl border-slate-200 text-sm">
            <option value="">All Status</option>
            <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('verification_status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            <option value="suspended" {{ request('verification_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
        </select>
        <select name="status" class="h-11 rounded-xl border-slate-200 text-sm">
            <option value="">All accounts</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
        </select>
        <div class="flex gap-2">
            <button class="h-11 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white">Apply</button>
            <a href="{{ route('admin.brokers.index') }}" class="inline-flex h-11 items-center rounded-xl border px-4 text-xs font-extrabold text-slate-600">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">Broker Directory</h2>
                <p class="text-xs text-slate-500">{{ $brokers->total() }} accounts match the current filters</p>
            </div>
            <span class="admin-theme-soft rounded-full px-3 py-1.5 text-[10px] font-extrabold">Page {{ $brokers->currentPage() }} / {{ max(1,$brokers->lastPage()) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="owners-table admin-table-base">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3">Broker</th>
                        <th class="px-4 py-3">Agency</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Listings</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($brokers as $broker)
                        @php
                            $tone=match($broker->broker_verification_status){
                                'approved'=>'bg-emerald-50 text-emerald-700',
                                'rejected'=>'bg-red-50 text-red-700',
                                'suspended'=>'bg-slate-100 text-slate-700',
                                default=>'bg-amber-50 text-amber-700'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex min-w-[220px] items-center gap-3">
                                    <span class="admin-theme-soft flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold">{{ strtoupper(substr($broker->name,0,1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-slate-900">{{ $broker->name }}</p>
                                        <p class="truncate text-xs text-slate-400">#{{ $broker->id }} - {{ $broker->email }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $broker->phone ?: 'No phone number' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-600 text-sm">{{ $broker->agency_name ?: '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $tone }}">{{ ucfirst($broker->broker_verification_status) }}</span>
                            </td>
                            <td class="px-4 py-4">
                                <x-admin.status-toggle
                                    :active="$broker->broker_verification_status === 'approved'"
                                    active-label="Active"
                                    inactive-label="Pending"
                                    :action="route('admin.brokers.approve', $broker)"
                                    :data-label="$broker->name"
                                    method="POST"
                                />
                                <p class="mt-1 text-[10px] text-slate-400">{{ $broker->broker_verification_status === 'approved' ? 'Verified' : 'Unverified' }}</p>
                            </td>
                            <td class="px-4 py-4 text-slate-600 text-sm">{{ $broker->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-4">
                                @if($broker->broker_verification_status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <form action="{{ route('admin.brokers.approve', $broker) }}" method="POST" class="admin-confirm inline" data-confirm-title="Approve broker?" data-confirm-text="This will activate the broker account." data-confirm-button="Yes, approve">@csrf @method('POST')<button type="submit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition"><i class="fas fa-check"></i></button></form>
                                        <form action="{{ route('admin.brokers.reject', $broker) }}" method="POST" class="admin-confirm inline" data-confirm-title="Reject broker?" data-confirm-text="This will reject the broker application." data-confirm-button="Yes, reject">@csrf @method('POST')<button type="submit" class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition"><i class="fas fa-times"></i></button></form>
                                    </div>
                                @elseif($broker->broker_verification_status === 'approved')
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.brokers.show', $broker) }}" title="View details" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200 transition"><i class="fas fa-eye"></i></a>
                                        <form action="{{ route('admin.brokers.suspend', $broker) }}" method="POST" class="admin-confirm inline" data-confirm-title="Suspend broker?" data-confirm-text="Broker will not be able to list properties." data-confirm-button="Yes, suspend">@csrf @method('POST')<button type="submit" class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-100 transition"><i class="fas fa-pause"></i></button></form>
                                    </div>
                                @elseif($broker->broker_verification_status === 'suspended')
                                    <div class="flex justify-end gap-2">
                                        <form action="{{ route('admin.brokers.activate', $broker) }}" method="POST" class="admin-confirm inline" data-confirm-title="Activate broker?" data-confirm-button="Yes, activate">@csrf @method('POST')<button type="submit" class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition"><i class="fas fa-play"></i></button></form>
                                    </div>
                                @elseif($broker->broker_verification_status === 'rejected')
                                    <div class="flex justify-end">
                                        <span class="text-[10px] font-bold text-slate-400">No actions</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-14 text-center"><i class="fas fa-users-slash text-3xl text-slate-300"></i><p class="mt-3 text-sm font-bold text-slate-600">No brokers found</p><p class="text-xs text-slate-400">Try changing the filters.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brokers->hasPages())<div class="border-t p-4">{{ $brokers->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
