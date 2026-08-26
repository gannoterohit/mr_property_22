@extends('layouts.broker')

@section('title', 'Leads & Enquiries')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2>Leads & Enquiries</h2>
            <p>All contact unlock activity for your properties.</p>
        </div>
    </div>

    {{-- Stat Tiles --}}
    @php
        $total = $enquiries->total();
        $unlocked = $enquiries->filter(fn($e) => $e->unlocked)->count();
        $unread = $enquiries->filter(fn($e) => !$e->unlocked)->count();
    @endphp
    <div class="owner-room-stats" style="grid-template-columns: repeat(3, minmax(0,1fr))">
        <div class="owner-room-stat">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-address-card text-indigo-600 text-sm"></i>
                <p class="text-xs font-semibold text-slate-500">Total Leads</p>
            </div>
            <p class="text-2xl font-extrabold text-slate-950">{{ $total }}</p>
        </div>
        <div class="owner-room-stat">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-lock-open text-emerald-600 text-sm"></i>
                <p class="text-xs font-semibold text-slate-500">Unlocked</p>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600">{{ $unlocked }}</p>
        </div>
        <div class="owner-room-stat">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-bell text-amber-500 text-sm"></i>
                <p class="text-xs font-semibold text-slate-500">New Leads</p>
            </div>
            <p class="text-2xl font-extrabold text-amber-600">{{ $unread }}</p>
        </div>
    </div>

    {{-- Leads Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading flex items-end justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-950">All Leads</h2>
                <p class="mt-0.5 text-xs text-slate-500">Property seekers who showed interest in your listings.</p>
            </div>
            <span class="hidden sm:block text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                {{ $enquiries->total() }} {{ Str::plural('lead', $enquiries->total()) }}
            </span>
        </div>

        @if($enquiries->count())
            <div class="owner-room-grid">
                @foreach($enquiries as $enquiry)
                    @php
                        $room   = $enquiry->room;
                        $seeker = $enquiry->user;
                        $gateway = ucfirst(str_replace('_', ' ', $enquiry->payment?->gateway ?? 'free'));
                    @endphp
                    <article class="owner-room-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-user text-3xl"></i></div>
                            @if($room && $room->photo_url)
                                <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute right-3 top-3 z-10 rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-extrabold uppercase shadow-sm
                                {{ $enquiry->unlocked ? 'text-emerald-700' : 'text-amber-700' }}">
                                <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 {{ $enquiry->unlocked ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ $enquiry->unlocked ? 'Unlocked' : 'New' }}
                            </span>
                        </div>

                        <div class="p-5">
                            {{-- Seeker info --}}
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-sm flex-shrink-0">
                                    {{ strtoupper(substr($seeker->name ?? 'D', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h2 class="truncate font-bold text-slate-950 text-sm">{{ $seeker->name ?? 'Deleted user' }}</h2>
                                    <p class="truncate text-xs text-slate-500">
                                        <i class="fas fa-home mr-1 text-indigo-400"></i>
                                        {{ $room->title ?? 'Deleted property' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Details grid --}}
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2.5 text-xs text-slate-600 mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-rose-400 w-3.5 text-center flex-shrink-0"></i>
                                    <span class="truncate">{{ $room?->city ?? '—' }}{{ $room?->state ? ', '.$room->state : '' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-rupee-sign text-emerald-500 w-3.5 text-center flex-shrink-0"></i>
                                    <span>&#8377;{{ number_format($room?->rent ?? 0) }}/mo</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-credit-card text-indigo-400 w-3.5 text-center flex-shrink-0"></i>
                                    <span class="truncate">{{ $gateway }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-amber-400 w-3.5 text-center flex-shrink-0"></i>
                                    <span>{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('d M Y') }}</span>
                                </div>
                            </div>

                            @if($room)
                            <div class="grid grid-cols-2 gap-2.5">
                                <a href="{{ route('agent.rooms.show', $room) }}" target="_blank"
                                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                    <i class="fas fa-eye text-slate-400"></i> View
                                </a>
                                <a href="mailto:{{ $seeker->email ?? '' }}"
                                    class="flex items-center justify-center gap-1.5 rounded-xl bg-indigo-50 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition">
                                    <i class="fas fa-envelope"></i> Contact
                                </a>
                            </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="agent-empty-state">
                <i class="fas fa-inbox"></i>
                <h2>No leads yet</h2>
                <p>When a property seeker shows interest in your listings, leads will appear here.</p>
            </div>
        @endif

        @if($enquiries->hasPages())
            <div class="mt-8">{{ $enquiries->links() }}</div>
        @endif
    </section>
</div>
@endsection
