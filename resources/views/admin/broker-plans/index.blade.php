@extends('layouts.admin')

@section('title', 'Broker Plans')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Finance & Growth</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Broker Plans</h1>
            <p class="mt-1 text-sm text-slate-500">Create and manage subscription packages for brokers.</p>
        </div>
        <a href="{{ route('admin.broker-plans.create') }}" class="admin-theme-bg inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-sm">
            <i class="fas fa-plus text-xs"></i> Create Plan
        </a>
    </header>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            <i class="fas fa-circle-check"></i>{{ session('success') }}
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead><tr><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Plan</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Type</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Price</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Listings</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Duration</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Featured</th><th class="px-5 text-left text-[10px] font-bold uppercase text-slate-500">Status</th><th class="px-5 text-right text-[10px] font-bold uppercase text-slate-500">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                                        <i class="fas fa-handshake text-xs"></i>
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $plan->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $plan->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5"><span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-700">{{ ucfirst($plan->type) }}</span></td>
                            <td class="px-5 font-extrabold text-slate-900">&#8377;{{ number_format($plan->price) }}</td>
                            <td class="px-5 text-slate-600 text-sm">{{ $plan->max_listings ?? 'Unlimited' }}</td>
                            <td class="px-5 text-slate-600 text-sm">{{ $plan->duration_days ?? '-' }} days</td>
                            <td class="px-5 text-slate-600 text-sm">{{ $plan->is_featured_included ? 'Yes' : 'No' }}</td>
                            <td class="px-5">
                                <form action="{{ route('admin.broker-plans.toggleActive', $plan) }}" method="POST">
                                    @csrf @method('POST')
                                    <button type="submit" class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.broker-plans.edit', $plan) }}" class="rounded-lg bg-slate-100 p-2 text-xs font-bold text-slate-700 hover:bg-slate-200" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form action="{{ route('admin.broker-plans.destroy', $plan) }}" method="POST" class="admin-confirm" data-confirm-title="Delete plan?" data-confirm-button="Yes, delete">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-100 p-2 text-xs font-bold text-red-700 hover:bg-red-200" title="Delete"><i class="fas fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-16 text-center text-slate-500">No plans created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($plans as $plan)
                <article class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600"><i class="fas fa-handshake text-xs"></i></span>
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ $plan->name }}</h3>
                                    <p class="text-xs text-slate-500">{{ ucfirst($plan->type) }} · &#8377;{{ number_format($plan->price) }}</p>
                                </div>
                            </div>
                            <div class="mt-2 ml-10 text-xs text-slate-500">
                                {{ $plan->max_listings ?? 'Unlimited' }} listings · {{ $plan->duration_days ?? 0 }} days · Featured: {{ $plan->is_featured_included ? 'Yes' : 'No' }}
                            </div>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold shrink-0 {{ $plan->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('admin.broker-plans.edit', $plan) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700">Edit</a>
                        <form action="{{ route('admin.broker-plans.destroy', $plan) }}" method="POST" class="admin-confirm inline" data-confirm-title="Delete plan?" data-confirm-button="Yes, delete">@csrf @method('DELETE')<button type="submit" class="rounded-lg bg-red-100 px-3 py-2 text-xs font-bold text-red-700">Delete</button></form>
                    </div>
                </article>
            @empty
                <div class="p-12 text-center text-sm text-slate-500">No plans created yet.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
