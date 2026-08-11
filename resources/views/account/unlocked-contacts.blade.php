@extends('layouts.public')

@section('title', 'My Unlocked Contacts | ApnaNest')

@section('content')
<div class="min-h-screen bg-slate-50">
    <main class="account-main unlocked-page">
        <header class="account-header">
            <div class="account-container">
                <div>
                    <span class="account-eyebrow">Your contacts</span>
                    <h1>My Unlocked Contacts</h1>
                    <p>{{ $unlocks->total() }} {{ Str::plural('property contact', $unlocks->total()) }} available in your account.</p>
                </div>
                <a href="{{ route('rooms.index') }}" class="account-action">
                    <i class="fas fa-magnifying-glass"></i> Find more properties
                </a>
            </div>
        </header>

        <div class="account-container account-body">
            @if($unlocks->count())
                <section class="unlock-grid" aria-label="Unlocked room contacts">
                    @foreach($unlocks as $unlock)
                        @php
                            $room = $unlock->room;
                            $owner = $room->owner;
                            $contact = trim((string) ($owner->phone ?: $owner->email));
                            $phoneDigits = preg_replace('/\D+/', '', (string) $owner->phone);
                            $whatsappNumber = $phoneDigits && strlen($phoneDigits) === 10 ? '91'.$phoneDigits : $phoneDigits;
                            $isAvailable = $room->status === 'active' && $room->listing_status === 'approved';
                        @endphp

                        <article class="unlock-card">
                            <div class="unlock-media">
                                <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" loading="lazy" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">
                                <em class="{{ $isAvailable ? 'available' : 'unavailable' }}">
                                    {{ $isAvailable ? 'Available' : 'Rented / unavailable' }}
                                </em>
                            </div>

                            <div class="unlock-copy">
                                <div class="unlock-price">
                                    <strong>₹{{ number_format((float) $room->rent) }}</strong>
                                    <span>/ month</span>
                                </div>
                                <h2>{{ $room->title }}</h2>
                                <p class="unlock-location"><i class="fas fa-location-dot"></i>{{ $room->city ?: $room->address }}</p>

                                <div class="contact-box">
                                    <span class="contact-avatar">{{ strtoupper(substr($owner->name, 0, 1)) }}</span>
                                    <div>
                                        <small>{{ $room->listing_type === 'broker' ? 'Broker contact' : 'Owner contact' }}</small>
                                        <strong>{{ $owner->name }}</strong>
                                        <a href="{{ $owner->phone ? 'tel:'.$owner->phone : 'mailto:'.$owner->email }}">{{ $contact }}</a>
                                    </div>
                                </div>

                                <div class="unlock-meta">
                                    <span><i class="far fa-clock"></i>Unlocked {{ ($unlock->unlocked_at ?? $unlock->created_at)->format('d M Y') }}</span>
                                    <span><i class="fas fa-receipt"></i>{{ ucfirst(str_replace('_', ' ', $unlock->payment?->gateway ?? 'free')) }}</span>
                                </div>

                                <div class="unlock-actions">
                                    @if($owner->phone)
                                        <a class="call" href="tel:{{ $owner->phone }}"><i class="fas fa-phone"></i> Call</a>
                                        <a class="whatsapp" href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                    @endif
                                    <a class="view" href="{{ route('rooms.show', $room) }}"><i class="fas fa-eye"></i> View Room</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                @if($unlocks->hasPages())
                    <div class="mt-8">{{ $unlocks->links() }}</div>
                @endif
            @else
                <div class="account-card account-empty">
                    <span><i class="fas fa-address-book"></i></span>
                    <h2>No unlocked contacts yet</h2>
                    <p>Browse properties and unlock the owner contact when you find a listing you like. It will remain saved here for future access.</p>
                    <a href="{{ route('rooms.index') }}" class="account-action">Browse rooms</a>
                </div>
            @endif
        </div>
    </main>
</div>

@include('account.partials.page-styles')

<link rel="stylesheet" href="{{ asset('css/account-unlocked-contacts.css') }}">
@endsection
