@extends('layouts.broker')

@section('title', 'Leads & Enquiries')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
@php
    $total    = $enquiries->total();
    $unlocked = $enquiries->filter(fn($e) => $e->unlocked)->count();
    $unread   = $enquiries->filter(fn($e) => !$e->unlocked)->count();
@endphp
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2 class="agent-page-header-title">Leads &amp; Enquiries</h2>
            <p class="agent-page-header-sub">All contact unlock activity for your properties.</p>
        </div>
    </div>

    {{-- Stat Tiles --}}
    <div class="owner-room-stats owner-room-stats-3">
        <div class="owner-room-stat">
            <div class="owner-room-stat-row">
                <i class="fas fa-address-card owner-room-stat-icon stat-indigo"></i>
                <span class="owner-room-stat-label">Total Leads</span>
            </div>
            <div class="owner-room-stat-value">{{ $total }}</div>
        </div>
        <div class="owner-room-stat">
            <div class="owner-room-stat-row">
                <i class="fas fa-lock-open owner-room-stat-icon stat-emerald"></i>
                <span class="owner-room-stat-label">Unlocked</span>
            </div>
            <div class="owner-room-stat-value stat-emerald-val">{{ $unlocked }}</div>
        </div>
        <div class="owner-room-stat">
            <div class="owner-room-stat-row">
                <i class="fas fa-bell owner-room-stat-icon stat-amber"></i>
                <span class="owner-room-stat-label">New Leads</span>
            </div>
            <div class="owner-room-stat-value stat-amber-val">{{ $unread }}</div>
        </div>
    </div>

    {{-- Leads Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading">
            <div>
                <h2 class="owner-listing-title">All Leads</h2>
                <p class="owner-listing-sub">Property seekers who showed interest in your listings.</p>
            </div>
            <span class="owner-listing-count">
                {{ $enquiries->total() }} {{ Str::plural('lead', $enquiries->total()) }}
            </span>
        </div>

        @if($enquiries->count())
            <div class="owner-room-grid">
                @foreach($enquiries as $enquiry)
                    @php
                        $room    = $enquiry->room;
                        $seeker  = $enquiry->user;
                        $gateway = ucfirst(str_replace('_', ' ', $enquiry->payment?->gateway ?? 'free'));
                        $badgeClass = $enquiry->unlocked ? 'badge-active' : 'badge-pending';
                        $badgeLabel = $enquiry->unlocked ? 'Unlocked' : 'New';
                    @endphp
                    <article class="owner-room-card">
                        {{-- Room thumbnail --}}
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-user"></i></div>
                            @if($room && $room->photo_url)
                                <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="owner-room-status-badge {{ $badgeClass }}">
                                <span class="badge-dot"></span>{{ $badgeLabel }}
                            </span>
                        </div>

                        <div class="owner-room-body">
                            {{-- Seeker Info --}}
                            <div class="owner-lead-info">
                                <div class="owner-lead-avatar">
                                    {{ strtoupper(substr($seeker->name ?? 'D', 0, 1)) }}
                                </div>
                                <div style="min-width:0">
                                    <p class="owner-lead-name">{{ $seeker->name ?? 'Deleted user' }}</p>
                                    <p class="owner-lead-prop">
                                        <i class="fas fa-home" style="color:#818cf8;margin-right:.2rem"></i>
                                        {{ $room->title ?? 'Deleted property' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Details --}}
                            <div class="owner-lead-grid">
                                <div class="owner-lead-row">
                                    <i class="fas fa-map-marker-alt" style="color:#f43f5e"></i>
                                    <span>{{ $room?->city ?? '—' }}{{ $room?->state ? ', '.$room->state : '' }}</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-rupee-sign" style="color:#10b981"></i>
                                    <span>&#8377;{{ number_format($room?->rent ?? 0) }}/mo</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-credit-card" style="color:#818cf8"></i>
                                    <span>{{ $gateway }}</span>
                                </div>
                                <div class="owner-lead-row">
                                    <i class="fas fa-clock" style="color:#f59e0b"></i>
                                    <span>{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('d M Y') }}</span>
                                </div>
                            </div>

                            @if($room)
                            <div class="owner-room-actions" style="grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .35rem;">
                                <a href="{{ route('agent.rooms.show', $room) }}" target="_blank" class="owner-room-btn owner-room-btn-outline" title="View Property">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if(!empty($seeker?->phone))
                                    @php
                                        $seekerDigits = preg_replace('/[^0-9]/', '', $seeker->phone);
                                        if(strlen($seekerDigits) === 10) { $seekerDigits = '91' . $seekerDigits; }
                                        $waMsg = 'Hello ' . ($seeker->name ?? '') . ', I saw your enquiry for ' . ($room->title ?? 'our property') . ' on ' . config('app.name', 'ApnaNest') . '. When would you like to visit?';
                                    @endphp
                                    <a href="tel:{{ $seeker->phone }}" class="owner-room-btn owner-room-btn-indigo" title="Call Seeker">
                                        <i class="fas fa-phone-alt"></i> Call
                                    </a>
                                    <a href="https://wa.me/{{ $seekerDigits }}?text={{ rawurlencode($waMsg) }}" target="_blank" class="owner-room-btn owner-room-btn-green" title="Chat on WhatsApp" style="background:#10b981;color:#fff;">
                                        <i class="fa-brands fa-whatsapp"></i> Chat
                                    </a>
                                @else
                                    <a href="mailto:{{ $seeker->email ?? '' }}" class="owner-room-btn owner-room-btn-indigo" style="grid-column: span 2;" title="Send Email">
                                        <i class="fas fa-envelope"></i> Email
                                    </a>
                                @endif
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
            <div style="margin-top:2rem">{{ $enquiries->links() }}</div>
        @endif
    </section>
</div>
@endsection
