@extends('layouts.broker')

@section('title', 'Broker Dashboard - ' . \App\Models\Setting::get('website_name', 'RoomRental'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="bg-white border-b border-slate-200">
        <div class="owner-dashboard-header max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent dashboard</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-950">Welcome back, {{ $user->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">Overview of your listings, leads and earnings.</p>
            </div>
            <a href="{{ route('rooms.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus"></i> Add New Property
            </a>
        </div>
    </header>

        <div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('partials.offer-banner', ['placement' => 'dashboard'])
            <section class="owner-dashboard-stats" aria-label="Dashboard statistics">
                @foreach([
                    ['Total Properties', $stats['total_properties'], 'fa-building', 'bg-indigo-50 text-indigo-600', route('agent.properties')],
                    ['Active Properties', $stats['active_properties'], 'fa-circle-check', 'bg-emerald-50 text-emerald-600', route('agent.properties')],
                    ['Pending Properties', $stats['pending_properties'], 'fa-clock', 'bg-amber-50 text-amber-700', route('agent.properties')],
                    ['Expired Properties', $stats['expired_properties'], 'fa-triangle-exclamation', 'bg-red-50 text-red-700', route('agent.properties')],
                ] as $stat)
                    <a href="{{ $stat[4] }}" class="owner-dashboard-stat block rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p><p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stat[3] }}"><i class="fas {{ $stat[2] }}"></i></span>
                        </div>
                    </a>
                @endforeach
                @foreach([
                    ['Featured Properties', $stats['featured_properties'], 'fa-star', 'bg-amber-50 text-amber-600', route('agent.properties')],
                    ['Subscription', $subscription ? ucfirst($subscription->status) : 'Inactive', 'fa-tags', 'bg-sky-50 text-sky-600', route('agent.subscription')],
                    ['Credits Remaining', $credits->sum('credits_remaining'), 'fa-coins', 'bg-emerald-50 text-emerald-600', route('agent.subscription')],
                    ['Wallet Balance', number_format($wallet?->balance ?? 0), 'fa-wallet', 'bg-purple-50 text-purple-600', route('agent.payments')],
                ] as $stat)
                    <a href="{{ $stat[4] }}" class="owner-dashboard-stat block rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p><p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p></div>
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $stat[3] }}"><i class="fas {{ $stat[2] }}"></i></span>
                        </div>
                    </a>
                @endforeach
            </section>

            <section class="owner-dashboard-body mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <div><h2 class="font-bold text-slate-950">Recent Properties</h2><p class="mt-0.5 text-xs text-slate-500">Latest listings</p></div>
                        <a href="{{ route('agent.properties') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all</a>
                    </div>
                    @forelse($recentProperties as $room)
                        <div class="flex items-center gap-4 px-5 py-4 border-b border-slate-100 last:border-0">
                            <div class="owner-recent-image">
                                <div class="owner-recent-placeholder"><i class="fas fa-house"></i></div>
                                @if($room->photo_url)<img src="{{ $room->photo_url }}" alt="" width="400" height="300" loading="lazy" onerror="this.style.display='none'">@endif
                            </div>
                            <div class="min-w-0 flex-1"><h3 class="truncate text-sm font-bold text-slate-900">{{ $room->title }}</h3><p class="mt-1 truncate text-xs text-slate-500"><i class="fas fa-location-dot mr-1 text-slate-400"></i>{{ $room->city }}</p><p class="mt-1 text-sm font-extrabold text-slate-900">&#8377;{{ number_format($room->rent) }}<span class="text-xs font-normal text-slate-400">/month</span></p></div>
                            <span class="hidden sm:inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $room->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($room->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">{{ $room->status === 'booked' ? 'Rented' : $room->status }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center text-sm text-slate-500">No properties listed yet. <a href="{{ route('rooms.create') }}" class="text-indigo-600 font-bold">Create your first listing</a></div>
                    @endforelse
                </div>

                <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-5 py-4">
                        <div><h2 class="font-bold text-slate-950">Recent Transactions</h2><p class="mt-0.5 text-xs text-slate-500">Latest payments and credits</p></div>
                        <a href="{{ route('agent.transactions') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">View all</a>
                    </div>
                    @forelse($recentTransactions as $txn)
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 last:border-0">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ ucfirst($txn->type) }} - {{ ucfirst($txn->category) }}</p>
                                <p class="text-xs text-slate-500">{{ $txn->description ?: $txn->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="text-sm font-bold {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->type === 'credit' ? '+' : '-' }}&#8377;{{ number_format($txn->amount, 2) }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center text-sm text-slate-500">No transactions yet.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection