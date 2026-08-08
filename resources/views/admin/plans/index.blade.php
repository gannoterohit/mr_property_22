@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('admin-content')
@php
    $activeCount = $plans->where('is_active', true)->count();
    $userCount = $plans->where('type', 'user')->count();
    $ownerCount = $plans->where('type', 'owner')->count();
@endphp

<div x-data="{ filter: 'all', query: '' }" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="admin-theme-text text-xs font-bold uppercase tracking-wider">Finance & Growth</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">Subscription Plans</h2>
            <p class="mt-1 text-sm text-slate-500">Control property-listing credits and room-contact unlock credits.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="plans" />
            <a href="{{ route('admin.plans.create') }}" class="admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold shadow-sm">
                <i class="fas fa-plus text-xs"></i> Create plan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold text-slate-500">Total plans</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $plans->count() }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold text-slate-500">Active</p><p class="mt-2 text-2xl font-bold text-emerald-600">{{ $activeCount }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold text-slate-500">User plans</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $userCount }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-xs font-semibold text-slate-500">Owner plans</p><p class="mt-2 text-2xl font-bold text-slate-950">{{ $ownerCount }}</p></div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-200 p-4">
            <div class="flex rounded-lg bg-slate-100 p-1">
                <button @click="filter='all'" :class="filter==='all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">All</button>
                <button @click="filter='user'" :class="filter==='user' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">User unlocks</button>
                <button @click="filter='owner'" :class="filter==='owner' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">Owner listings</button>
            </div>
            <div class="relative w-full md:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input x-model="query" type="search" placeholder="Search plans..." class="w-full rounded-lg border-slate-200 py-2.5 pl-9 pr-3 text-sm">
            </div>
        </div>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead><tr><th class="px-5 text-left">Plan</th><th class="px-5 text-left">Audience</th><th class="px-5 text-left">Price</th><th class="px-5 text-left">Credits</th><th class="px-5 text-left">Validity</th><th class="px-5 text-left">Status</th><th class="px-5 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans->sortByDesc('created_at') as $plan)
                        @php $limit = $plan->type === 'owner' ? $plan->listing_limit : $plan->contacts_limit; @endphp
                        <tr x-show="(filter === 'all' || filter === '{{ $plan->type }}') && '{{ strtolower(addslashes($plan->name)) }}'.includes(query.toLowerCase())" class="hover:bg-slate-50/70">
                            <td class="px-5"><div class="font-bold text-slate-900">{{ $plan->name }}</div><div class="mt-0.5 text-xs text-slate-400">ID #{{ $plan->id }}</div></td>
                            <td class="px-5"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $plan->type === 'owner' ? 'bg-amber-50 text-amber-700' : 'admin-theme-soft' }}">{{ $plan->type === 'owner' ? 'Owner' : 'User' }}</span></td>
                            <td class="px-5 font-bold text-slate-900">&#8377;{{ number_format($plan->price) }}</td>
                            <td class="px-5 text-slate-600">{{ $limit == -1 ? 'Unlimited' : number_format($limit) }} {{ $plan->type === 'owner' ? 'listings' : 'unlocks' }}</td>
                            <td class="px-5 text-slate-600">{{ $plan->duration_days }} days</td>
                            <td class="px-5"><x-admin.status-toggle :active="$plan->is_active" :action="route('admin.plans.toggleActive', $plan)" :data-label="$plan->name" method="POST" /></td>
                            <td class="px-5"><div class="flex justify-end gap-2">
                                <x-admin.action-icon variant="edit" :href="route('admin.plans.edit', $plan)" />
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="admin-confirm" data-confirm-title="Delete {{ $plan->name }}?" data-confirm-text="Existing subscription history may be affected." data-confirm-button="Yes, delete plan">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-16 text-center text-slate-500">No plans created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($plans->sortByDesc('created_at') as $plan)
                @php $limit = $plan->type === 'owner' ? $plan->listing_limit : $plan->contacts_limit; @endphp
                <article x-show="(filter === 'all' || filter === '{{ $plan->type }}') && '{{ strtolower(addslashes($plan->name)) }}'.includes(query.toLowerCase())" class="p-4">
                    <div class="flex items-start justify-between gap-3"><div><h3 class="font-bold text-slate-900">{{ $plan->name }}</h3><p class="mt-1 text-xs text-slate-500">{{ ucfirst($plan->type) }} · {{ $plan->duration_days }} days</p></div><span class="text-lg font-bold text-slate-950">&#8377;{{ number_format($plan->price) }}</span></div>
                    <div class="my-4 flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm"><span class="text-slate-500">Credits</span><strong class="text-slate-900">{{ $limit == -1 ? 'Unlimited' : $limit }} {{ $plan->type === 'owner' ? 'listings' : 'unlocks' }}</strong></div>
                    <div class="flex items-center justify-between gap-2"><x-admin.status-toggle :active="$plan->is_active" :action="route('admin.plans.toggleActive', $plan)" :data-label="$plan->name" method="POST" /><x-admin.action-icon variant="edit" :href="route('admin.plans.edit', $plan)" /></div>
                </article>
            @empty
                <div class="p-12 text-center text-sm text-slate-500">No plans created yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
