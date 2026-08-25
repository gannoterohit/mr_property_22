@extends('layouts.broker')

@section('title', 'Leads')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
        <div class="owner-rooms-header-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Leads</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-950">All Enquiries</h1>
                <p class="mt-2 text-sm text-slate-500">Property seekers who showed interest in your listings.</p>
            </div>
        </div>
    </header>
    <div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="owner-room-stats">
            @php
                $total = $enquiries->total();
                $unlocked = $enquiries->filter(fn($e) => $e->unlocked)->count();
                $unread = $enquiries->filter(fn($e) => !$e->unlocked)->count();
            @endphp
            <div class="owner-room-stat"><p class="text-xs font-semibold text-slate-500">Total leads</p><p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $total }}</p></div>
            <div class="owner-room-stat"><p class="text-xs font-semibold text-slate-500">Unlocked</p><p class="mt-2 text-2xl font-extrabold text-emerald-600">{{ $unlocked }}</p></div>
            <div class="owner-room-stat"><p class="text-xs font-semibold text-slate-500">New leads</p><p class="mt-2 text-2xl font-extrabold text-amber-600">{{ $unread }}</p></div>
        </div>

        <section class="owner-listing-section">
            <div class="owner-listing-heading flex items-end justify-between gap-4">
                <div><h2 class="text-lg font-extrabold text-slate-950">Leads</h2><p class="mt-1 text-sm text-slate-500">All contact unlock activity for your properties.</p></div>
                <span class="hidden sm:block text-xs font-bold text-slate-400">{{ $enquiries->total() }} {{ Str::plural('lead', $enquiries->total()) }}</span>
            </div>
            @if($enquiries->count())
                <div class="owner-room-grid">
                    @foreach($enquiries as $enquiry)
                        @php
                            $room = $enquiry->room;
                            $seeker = $enquiry->user;
                            $gateway = ucfirst(str_replace('_', ' ', $enquiry->payment?->gateway ?? 'free'));
                        @endphp
                        <article class="owner-room-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
                            <div class="owner-room-media">
                                <div class="owner-room-placeholder"><i class="fas fa-user text-3xl"></i></div>
                                @if($room && $room->photo_url)
                                     <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="absolute right-3 top-3 z-10 rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase shadow-sm {{ $enquiry->unlocked ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $enquiry->unlocked ? 'Unlocked' : 'New' }}
                                </span>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="truncate font-bold text-slate-950">{{ $seeker->name ?? 'Deleted user' }}</h2>
                                        <p class="mt-1 truncate text-xs text-slate-500">
                                            <i class="fas fa-home mr-1 text-indigo-400"></i>
                                            {{ $room->title ?? 'Deleted property' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fas fa-map-marker-alt text-rose-400 w-4"></i>
                                        <span>{{ $room->city ?? '-' }}{{ $room->state ? ', '.$room->state : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fas fa-rupee-sign text-emerald-500 w-4"></i>
                                        <span>&#8377;{{ number_format($room->rent ?? 0) }}/month</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fas fa-credit-card text-indigo-400 w-4"></i>
                                        <span>{{ $gateway }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fas fa-clock text-amber-400 w-4"></i>
                                        <span>{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('d M Y, h:i A') }}</span>
                                    </div>
                                </div>
                                @if($room)
                                <div class="mt-4 grid grid-cols-2 gap-3">
                                    <a href="{{ route('agent.rooms.show', $room) }}" target="_blank" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                                        <i class="fas fa-eye"></i>View
                                    </a>
                                    <a href="mailto:{{ $seeker->email ?? '' }}" class="flex items-center justify-center gap-2 rounded-xl bg-indigo-50 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100">
                                        <i class="fas fa-envelope"></i>Contact
                                    </a>
                                </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-300"></i>
                    <h2 class="mt-4 text-lg font-bold text-slate-900">No leads yet</h2>
                    <p class="mt-2 text-sm text-slate-500">When a property seeker shows interest in your listings, leads will appear here.</p>
                </div>
            @endif
            @if($enquiries->hasPages())<div class="mt-8">{{ $enquiries->links() }}</div>@endif
        </section>
    </div>
</div>
@endsection
