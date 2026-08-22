@extends('layouts.admin')

@section('title', 'Broker Management')

@section('admin-content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="admin-theme-text text-xs font-bold uppercase tracking-wider">People</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">Brokers</h2>
            <p class="mt-1 text-sm text-slate-500">Manage broker registrations, verification and access.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold text-slate-500">Total Brokers</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs font-semibold text-amber-700">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-800">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs font-semibold text-emerald-700">Approved</p>
            <p class="mt-2 text-2xl font-bold text-emerald-800">{{ $stats['approved'] }}</p>
        </div>
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <p class="text-xs font-semibold text-red-700">Rejected</p>
            <p class="mt-2 text-2xl font-bold text-red-800">{{ $stats['rejected'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-xs font-semibold text-slate-500">Suspended</p>
            <p class="mt-2 text-2xl font-bold text-slate-950">{{ $stats['suspended'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-3 border-b border-slate-200 p-4">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search brokers..." class="w-full rounded-lg border-slate-200 py-2.5 pl-9 pr-3 text-sm">
            </div>
            <select name="verification_status" class="rounded-lg border-slate-200 py-2.5 px-3 text-sm">
                <option value="">All Status</option>
                <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('verification_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="suspended" {{ request('verification_status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            <button type="submit" class="admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm">Filter</button>
        </form>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead><tr><th class="px-5 text-left">Broker</th><th class="px-5 text-left">Agency</th><th class="px-5 text-left">Status</th><th class="px-5 text-left">Listings</th><th class="px-5 text-left">Joined</th><th class="px-5 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($brokers as $broker)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600">{{ substr($broker->name, 0, 1) }}</div>
                                    <div><div class="font-bold text-slate-900">{{ $broker->name }}</div><div class="text-xs text-slate-400">{{ $broker->email }}</div></div>
                                </div>
                            </td>
                            <td class="px-5 text-slate-600">{{ $broker->agency_name ?: '-' }}</td>
                            <td class="px-5">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-amber-50 text-amber-700',
                                        'approved' => 'bg-emerald-50 text-emerald-700',
                                        'rejected' => 'bg-red-50 text-red-700',
                                        'suspended' => 'bg-slate-100 text-slate-700',
                                    ];
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusColors[$broker->broker_verification_status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($broker->broker_verification_status) }}</span>
                            </td>
                            <td class="px-5 text-slate-600">{{ $broker->broker_total_listings }}</td>
                            <td class="px-5 text-slate-600">{{ $broker->created_at->format('M d, Y') }}</td>
                            <td class="px-5"><div class="flex justify-end gap-2">
                                <a href="{{ route('admin.brokers.show', $broker) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700 hover:bg-slate-200"><i class="fas fa-eye"></i></a>
                                @if($broker->broker_verification_status === 'pending')
                                    <form action="{{ route('admin.brokers.approve', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Approve broker?" data-confirm-text="This will activate the broker account." data-confirm-button="Yes, approve">@csrf @method('POST')<button type="submit" class="rounded-lg bg-emerald-100 p-2 text-xs font-bold text-emerald-700 hover:bg-emerald-200"><i class="fas fa-check"></i></button></form>
                                    <button onclick="document.getElementById('reject-form-{{ $broker->id }}').submit()" class="rounded-lg bg-red-100 p-2 text-xs font-bold text-red-700 hover:bg-red-200"><i class="fas fa-times"></i></button>
                                @endif
                                @if($broker->broker_verification_status === 'approved')
                                    <form action="{{ route('admin.brokers.suspend', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Suspend broker?" data-confirm-text="Broker will not be able to list properties." data-confirm-button="Yes, suspend">@csrf @method('POST')<button type="submit" class="rounded-lg bg-amber-100 p-2 text-xs font-bold text-amber-700 hover:bg-amber-200"><i class="fas fa-pause"></i></button></form>
                                @endif
                                @if($broker->broker_verification_status === 'suspended')
                                    <form action="{{ route('admin.brokers.activate', $broker) }}" method="POST" class="admin-confirm" data-confirm-title="Activate broker?" data-confirm-button="Yes, activate">@csrf @method('POST')<button type="submit" class="rounded-lg bg-emerald-100 p-2 text-xs font-bold text-emerald-700 hover:bg-emerald-200"><i class="fas fa-play"></i></button></form>
                                @endif
                            </div></td>
                        </tr>
                        @if($broker->broker_verification_status === 'pending')
                            <form id="reject-form-{{ $broker->id }}" action="{{ route('admin.brokers.reject', $broker) }}" method="POST" class="hidden" onsubmit="event.preventDefault(); if(!this.querySelector('textarea').value){alert('Please enter a reason');return;} this.submit();">
                                @csrf @method('POST')
                                <textarea name="reason" placeholder="Rejection reason" class="sr-only"></textarea>
                            </form>
                        @endif
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-slate-500">No brokers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($brokers as $broker)
                <article class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-bold text-slate-600">{{ substr($broker->name, 0, 1) }}</div>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $broker->name }}</h3>
                                <p class="text-xs text-slate-500">{{ $broker->email }}</p>
                                <p class="text-xs text-slate-400">{{ $broker->agency_name ?: 'No agency' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.brokers.show', $broker) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700"><i class="fas fa-eye"></i></a>
                    </div>
                </article>
            @empty
                <div class="p-12 text-center text-sm text-slate-500">No brokers found.</div>
            @endforelse
        </div>
        <div class="p-4">{{ $brokers->links() }}</div>
    </div>
</div>
@endsection