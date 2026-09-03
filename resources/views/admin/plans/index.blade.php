@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('admin-content')
<div x-data="{ filter: 'all', query: '' }" class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Finance & Growth</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Subscription Plans</h1>
            <p class="mt-1 text-sm text-slate-500">Control property-listing credits and room-contact unlock credits.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm">
            <i class="fas fa-plus text-xs"></i> Create plan
        </a>
    </header>

    @php
        $activeCount = $plans->where('is_active', true)->count();
        $userCount = $plans->where('type', 'user')->count();
        $ownerCount = $plans->where('type', 'owner')->count();
        $brokerCount = $plans->where('type', 'broker')->count();
    @endphp

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Total plans</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $plans->count() }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i class="fas fa-tags text-xs"></i></span>
            </div>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Active</p>
                    <p class="mt-2 text-2xl font-extrabold text-emerald-600">{{ $activeCount }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-circle-check text-xs"></i></span>
            </div>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Listing plans</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $ownerCount + $brokerCount }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fas fa-building text-xs"></i></span>
            </div>
        </article>
        <article class="rounded-2xl border bg-white p-4 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-bold uppercase text-slate-400">Unlock plans</p>
                    <p class="mt-2 text-2xl font-extrabold text-slate-900">{{ $userCount }}</p>
                </div>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fas fa-address-card text-xs"></i></span>
            </div>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-200 p-4">
            <div class="flex rounded-lg bg-slate-100 p-1">
                <button @click="filter='all'" :class="filter==='all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">All</button>
                <button @click="filter='user'" :class="filter==='user' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">User unlocks</button>
                <button @click="filter='owner'" :class="filter==='owner' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">Owner listings</button>
                <button @click="filter='broker'" :class="filter==='broker' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" class="rounded-md px-3 py-2 text-xs font-bold">Broker listings</button>
            </div>
            <div class="relative w-full md:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input x-model="query" type="search" placeholder="Search plans..." class="w-full rounded-lg border-slate-200 py-2.5 pl-9 pr-3 text-sm">
            </div>
        </div>

        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead><tr><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Plan</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Audience</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Price</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Credits</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Validity</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Status</th><th class="px-5 text-right text-[10px] font-bold uppercase text-slate-500">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans->sortByDesc('created_at') as $plan)
                        @php $limit = $plan->type === 'owner' || $plan->type === 'broker' ? $plan->listing_limit : $plan->contacts_limit; @endphp
                        <tr x-show="(filter === 'all' || filter === '{{ $plan->type }}') && '{{ strtolower(addslashes($plan->name)) }}'.includes(query.toLowerCase())" class="hover:bg-slate-50/70">
                            <td class="px-5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $plan->type === 'owner' ? 'bg-amber-50 text-amber-600' : ($plan->type === 'broker' ? 'bg-sky-50 text-sky-600' : 'admin-theme-soft') }}">
                                        <i class="fas {{ $plan->type === 'owner' || $plan->type === 'broker' ? 'fa-building' : 'fa-address-card' }} text-xs"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $plan->name }}</div>
                                        <div class="text-[10px] text-slate-400">ID #{{ $plan->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $plan->type === 'owner' ? 'bg-amber-50 text-amber-700' : ($plan->type === 'broker' ? 'bg-sky-50 text-sky-700' : 'admin-theme-soft') }}">{{ ucfirst($plan->type) }}</span></td>
                            <td class="px-5 font-extrabold text-slate-900">&#8377;{{ number_format($plan->price) }}</td>
                            <td class="px-5 text-slate-600">{{ $limit == -1 ? 'Unlimited' : number_format($limit) }} {{ $plan->type === 'owner' || $plan->type === 'broker' ? 'listings' : 'unlocks' }}</td>
                            <td class="px-5 text-slate-600">{{ $plan->duration_days }} days</td>
                            <td class="px-5"><x-admin.status-toggle :active="$plan->is_active" :action="route('admin.plans.toggleActive', $plan)" :data-label="$plan->name" method="POST" /></td>
                            <td class="px-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.plans.edit', $plan) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700 hover:bg-slate-200" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="admin-confirm" data-confirm-title="Delete {{ $plan->name }}?" data-confirm-text="Existing subscription history may be affected." data-confirm-button="Yes, delete plan">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-100 p-2 text-xs font-bold text-red-700 hover:bg-red-200" title="Delete"><i class="fas fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-16 text-center text-slate-500">No plans created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($plans->sortByDesc('created_at') as $plan)
                @php $limit = $plan->type === 'owner' || $plan->type === 'broker' ? $plan->listing_limit : $plan->contacts_limit; @endphp
                <article x-show="(filter === 'all' || filter === '{{ $plan->type }}') && '{{ strtolower(addslashes($plan->name)) }}'.includes(query.toLowerCase())" class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $plan->type === 'owner' ? 'bg-amber-50 text-amber-600' : ($plan->type === 'broker' ? 'bg-sky-50 text-sky-600' : 'admin-theme-soft') }}">
                                <i class="fas {{ $plan->type === 'owner' || $plan->type === 'broker' ? 'fa-building' : 'fa-address-card' }} text-xs"></i>
                            </span>
                            <div>
                                <h3 class="font-bold text-slate-900">{{ $plan->name }}</h3>
                                <p class="text-xs text-slate-500">{{ ucfirst($plan->type) }} · {{ $plan->duration_days }} days</p>
                            </div>
                        </div>
                        <span class="text-lg font-extrabold text-slate-950">&#8377;{{ number_format($plan->price) }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-slate-50 p-3 text-sm">
                        <span class="text-slate-500">Credits</span>
                        <strong class="text-slate-900">{{ $limit == -1 ? 'Unlimited' : $limit }} {{ $plan->type === 'owner' || $plan->type === 'broker' ? 'listings' : 'unlocks' }}</strong>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <x-admin.status-toggle :active="$plan->is_active" :action="route('admin.plans.toggleActive', $plan)" :data-label="$plan->name" method="POST" />
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">Edit</a>
                    </div>
                </article>
            @empty
                <div class="p-12 text-center text-sm text-slate-500">No plans created yet.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
