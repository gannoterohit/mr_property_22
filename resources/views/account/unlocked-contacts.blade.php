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

<style>
.unlocked-page{padding-bottom:48px}.unlocked-page .account-header{background:linear-gradient(180deg,#fff 0%,#f8fbff 100%)}.unlocked-page .account-body{padding-bottom:28px}.unlock-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.unlock-card{display:grid;grid-template-columns:190px minmax(0,1fr);overflow:hidden;border:1px solid #e2e8f0;border-radius:17px;background:#fff;box-shadow:0 3px 14px rgba(15,23,42,.04);transition:.2s}.unlock-card:hover{transform:translateY(-2px);border-color:rgba(var(--primary-rgb),.28);box-shadow:0 12px 28px rgba(15,23,42,.08)}.unlock-media{position:relative;min-height:285px;background:#f1f5f9}.unlock-media img{width:100%;height:100%;object-fit:cover}.unlock-media>span{display:grid;place-items:center;height:100%;color:#94a3b8;font-size:32px}.unlock-media em{position:absolute;left:10px;top:10px;padding:6px 8px;border-radius:7px;background:#fff;font-size:8px;font-style:normal;font-weight:900;text-transform:uppercase;box-shadow:0 4px 12px rgba(15,23,42,.12)}.unlock-media em.available{color:#047857}.unlock-media em.unavailable{color:#b91c1c}.unlock-copy{min-width:0;padding:17px}.unlock-price{display:flex;align-items:baseline;gap:4px}.unlock-price strong{color:#0f172a;font-size:18px}.unlock-price span{color:#94a3b8;font-size:9px}.unlock-copy h2{margin:7px 0 5px;overflow:hidden;color:#0f172a;font-size:13px;font-weight:850;text-overflow:ellipsis;white-space:nowrap}.unlock-location{margin:0;color:#64748b;font-size:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.unlock-location i{margin-right:5px;color:#f43f5e}.contact-box{display:grid;grid-template-columns:40px minmax(0,1fr);gap:10px;align-items:center;margin-top:15px;padding:11px;border:1px solid rgba(var(--primary-rgb),.18);border-radius:11px;background:rgba(var(--primary-rgb),.04)}.contact-avatar{display:grid;place-items:center;width:40px;height:40px;border-radius:10px;background:var(--primary);color:#fff;font-size:12px;font-weight:900}.contact-box div{display:grid;min-width:0}.contact-box small{color:#94a3b8;font-size:8px;font-weight:800;text-transform:uppercase}.contact-box strong,.contact-box a{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.contact-box strong{color:#334155;font-size:10px}.contact-box a{margin-top:2px;color:var(--primary);font-size:12px;font-weight:850;text-decoration:none}.unlock-meta{display:flex;flex-wrap:wrap;gap:7px;margin:12px 0}.unlock-meta span{padding:5px 7px;border-radius:6px;background:#f1f5f9;color:#64748b;font-size:8px;font-weight:700}.unlock-meta i{margin-right:4px}.unlock-actions{display:flex;flex-wrap:wrap;gap:7px}.unlock-actions a{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-height:34px;padding:0 10px;border-radius:8px;font-size:9px;font-weight:850;text-decoration:none}.unlock-actions .call{background:var(--primary);color:#fff}.unlock-actions .whatsapp{background:#dcfce7;color:#15803d}.unlock-actions .view{border:1px solid #e2e8f0;background:#fff;color:#475569}@media(max-width:1100px){.unlock-grid{grid-template-columns:1fr}}@media(max-width:620px){.unlocked-page{padding-bottom:76px}.unlock-card{grid-template-columns:1fr}.unlock-media{min-height:210px;height:210px}.unlock-copy{padding:15px}.unlock-actions a{flex:1}}
</style>
@endsection
