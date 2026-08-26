@extends('layouts.broker')

@section('title', 'Agent Dashboard - ' . \App\Models\Setting::get('website_name', 'RoomRental'))

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    @include('partials.offer-banner', ['placement' => 'dashboard'])

    {{-- Welcome Banner --}}
    <div class="agent-welcome-banner">
        <div class="welcome-text">
            <h2>Welcome back, {{ explode(' ', $user->name)[0] }}! 👋</h2>
            <p>Here's an overview of your agent workspace and listings.</p>
        </div>
        <div class="welcome-actions">
            <a href="{{ route('agent.rooms.create') }}" class="welcome-btn welcome-btn-primary">
                <i class="fas fa-plus"></i> Add Property
            </a>
            <a href="{{ route('agent.properties') }}" class="welcome-btn">
                <i class="fas fa-list"></i> View All
            </a>
        </div>
    </div>

    {{-- Stat Cards Row 1 --}}
    <section class="owner-dashboard-stats" aria-label="Dashboard statistics">
        @foreach([
            ['Total Properties',   $stats['total_properties'],   'fa-building',            'bg-indigo-50 text-indigo-600',   route('agent.properties')],
            ['Active Properties',  $stats['active_properties'],  'fa-circle-check',         'bg-emerald-50 text-emerald-600', route('agent.properties')],
            ['Pending Properties', $stats['pending_properties'], 'fa-clock',               'bg-amber-50 text-amber-700',    route('agent.properties')],
            ['Expired Properties', $stats['expired_properties'], 'fa-triangle-exclamation','bg-red-50 text-red-700',        route('agent.properties')],
        ] as $stat)
            <a href="{{ $stat[4] }}" class="owner-dashboard-stat block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat[3] }} shadow-sm">
                        <i class="fas {{ $stat[2] }}"></i>
                    </span>
                </div>
            </a>
        @endforeach

        {{-- Stat Cards Row 2 --}}
        @foreach([
            ['Featured Properties', $stats['featured_properties'],             'fa-star',   'bg-amber-50 text-amber-600',    route('agent.properties')],
            ['Credits Remaining',   $credits->sum('credits_remaining'),         'fa-coins',  'bg-emerald-50 text-emerald-600', route('agent.plans')],
            ['Wallet Balance',      '₹' . number_format($wallet?->balance ?? 0),'fa-wallet', 'bg-purple-50 text-purple-600',  route('agent.payments')],
        ] as $stat)
            <a href="{{ $stat[4] }}" class="owner-dashboard-stat block">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">{{ $stat[0] }}</p>
                        <p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $stat[1] }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $stat[3] }} shadow-sm">
                        <i class="fas {{ $stat[2] }}"></i>
                    </span>
                </div>
            </a>
        @endforeach
    </section>

    {{-- Bottom Panels --}}
    <section class="owner-dashboard-body mt-4" aria-label="Recent activity">

        {{-- Recent Properties --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">Recent Properties</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Latest listings</p>
                </div>
                <a href="{{ route('agent.properties') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    View all <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @forelse($recentProperties as $room)
                <div class="flex items-center gap-4 px-5 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition">
                    <div class="owner-recent-image flex-shrink-0">
                        <div class="owner-recent-placeholder"><i class="fas fa-house text-sm"></i></div>
                        @if($room->photo_url)
                            <img src="{{ $room->photo_url }}" alt="" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-bold text-slate-900">{{ $room->title }}</h3>
                        <p class="mt-0.5 truncate text-xs text-slate-500">
                            <i class="fas fa-location-dot mr-1 text-slate-400"></i>{{ $room->city }}
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-sm font-extrabold text-slate-900">&#8377;{{ number_format($room->rent) }}</p>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase mt-1
                            {{ $room->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($room->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $room->status === 'booked' ? 'Rented' : $room->status }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <i class="fas fa-house-circle-xmark text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No properties listed yet.</p>
                    <a href="{{ route('agent.rooms.create') }}" class="mt-2 inline-block text-indigo-600 text-sm font-bold hover:underline">Create your first listing</a>
                </div>
            @endforelse
        </div>

        {{-- Recent Transactions --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">Recent Transactions</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Latest payments & credits</p>
                </div>
                <a href="{{ route('agent.transactions') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
                    View all <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @forelse($recentTransactions as $txn)
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-50 last:border-0 hover:bg-slate-50/60 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl {{ $txn->type === 'credit' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                            <i class="fas {{ $txn->type === 'credit' ? 'fa-arrow-down-left' : 'fa-arrow-up-right' }} text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ ucfirst($txn->type) }} — {{ ucfirst($txn->category) }}</p>
                            <p class="text-xs text-slate-500">{{ $txn->description ?: $txn->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold flex-shrink-0 ml-3 {{ $txn->type === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $txn->type === 'credit' ? '+' : '-' }}&#8377;{{ number_format($txn->amount, 2) }}
                    </span>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <i class="fas fa-receipt text-3xl text-slate-200 block mb-3"></i>
                    <p class="text-sm text-slate-500">No transactions yet.</p>
                </div>
            @endforelse
        </div>

    </section>
</div>
@endsection