@extends('layouts.broker')

@section('title', 'My Properties')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2 class="agent-page-header-title">Your Properties</h2>
            <p class="agent-page-header-sub">Manage your listings, pricing, and availability.</p>
        </div>
        <a href="{{ route('agent.rooms.create') }}" class="agent-page-header-action">
            <i class="fas fa-plus"></i> Add Property
        </a>
    </div>

    {{-- Stat Tiles --}}
    <div class="owner-room-stats">
        @foreach([
            [$roomCounts['all'],    'All Properties', 'fa-building',     'stat-indigo'],
            [$roomCounts['active'], 'Active',          'fa-circle-check', 'stat-emerald'],
            [$roomCounts['pending'],'Pending',         'fa-clock',        'stat-amber'],
            [$roomCounts['booked'], 'Rented',          'fa-key',          'stat-rose'],
        ] as [$count, $label, $icon, $color])
            <div class="owner-room-stat">
                <div class="owner-room-stat-row">
                    <i class="fas {{ $icon }} owner-room-stat-icon {{ $color }}"></i>
                    <span class="owner-room-stat-label">{{ $label }}</span>
                </div>
                <div class="owner-room-stat-value">{{ $count }}</div>
            </div>
        @endforeach
    </div>

    {{-- Listing Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading">
            <div>
                <h2 class="owner-listing-title">All Listings</h2>
                <p class="owner-listing-sub">Click Edit to update details, pricing or availability.</p>
            </div>
            <span class="owner-listing-count">
                {{ $properties->total() }} {{ Str::plural('property', $properties->total()) }}
            </span>
        </div>

        @if($properties->count())
            <div class="owner-room-grid">
                @foreach($properties as $property)
                    @php
                        $st = $property->status;
                        $badgeClass = $st === 'active' ? 'badge-active' : ($st === 'pending' ? 'badge-pending' : ($st === 'booked' ? 'badge-booked' : 'badge-default'));
                        $label = $st === 'booked' ? 'Rented' : ucfirst($st);
                    @endphp
                    <article class="owner-room-card">
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-house"></i></div>
                            @if($property->photo_url)
                                <img src="{{ $property->photo_url }}" alt="{{ $property->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="owner-room-status-badge {{ $badgeClass }}">
                                <span class="badge-dot"></span>{{ $label }}
                            </span>
                        </div>
                        <div class="owner-room-body">
                            <div class="owner-room-meta">
                                <div style="min-width:0">
                                    <p class="owner-room-name">{{ $property->title }}</p>
                                    <p class="owner-room-loc"><i class="fas fa-location-dot"></i>{{ $property->city }}{{ $property->state ? ', '.$property->state : '' }}</p>
                                </div>
                                <div class="owner-room-price">
                                    <span class="owner-room-price-amt">&#8377;{{ number_format($property->rent) }}</span>
                                    <span class="owner-room-price-unit">per month</span>
                                </div>
                            </div>
                            <div class="owner-room-actions">
                                <a href="{{ route('agent.rooms.show', $property) }}" class="owner-room-btn owner-room-btn-outline">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('agent.rooms.edit', $property) }}" class="owner-room-btn owner-room-btn-indigo">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                                @if($property->status === 'active')
                                    <button type="button" onclick="markRoomRented({{ $property->id }})" class="owner-room-btn owner-room-btn-rose owner-room-btn-full">
                                        <i class="fas fa-key"></i> Mark as Rented
                                    </button>
                                @elseif($property->status === 'booked')
                                    <button type="button" onclick="makeRoomAvailable({{ $property->id }})" class="owner-room-btn owner-room-btn-green owner-room-btn-full">
                                        <i class="fas fa-rotate"></i> Make Available
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="agent-empty-state">
                <i class="fas fa-house-circle-xmark"></i>
                <h2>No properties listed yet</h2>
                <p>Add your first property and start receiving enquiries from tenants.</p>
                <a href="{{ route('agent.rooms.create') }}" class="agent-empty-btn">
                    <i class="fas fa-plus"></i> Add Your First Property
                </a>
            </div>
        @endif

        @if($properties->hasPages())
            <div style="margin-top:2rem">{{ $properties->links() }}</div>
        @endif
    </section>
</div>

@push('scripts')
<script>
const agentRoomCsrf = '{{ csrf_token() }}';
async function agentRoomPost(url, payload = {}) {
    const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': agentRoomCsrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) });
    const data = await response.json().catch(() => ({ success: false, message: 'Invalid server response' }));
    if (!response.ok) throw new Error(data.message || 'Request failed');
    return data;
}
async function markRoomRented(roomId) {
    const result = await Swal.fire({ title: 'Mark property as rented?', text: 'This property will stop appearing to property seekers.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, mark rented', confirmButtonColor: '#e11d48' });
    if (!result.isConfirmed) return;
    try { const data = await agentRoomPost(`{{ route('agent.rooms.markBooked', ':room') }}`.replace(':room', roomId)); await Swal.fire('Property rented', data.message, 'success'); location.reload(); }
    catch (error) { Swal.fire('Could not update property', error.message, 'error'); }
}
async function makeRoomAvailable(roomId) {
    const confirmation = await Swal.fire({ title: 'Make property available?', text: 'This property will be visible to users again.', icon: 'question', showCancelButton: true, confirmButtonText: 'Make available', confirmButtonColor: '#059669' });
    if (!confirmation.isConfirmed) return;
    try {
        const data = await agentRoomPost(`{{ route('agent.rooms.markAvailable', ':room') }}`.replace(':room', roomId), { payment_method: 'free' });
        await Swal.fire('Property available', data.message || 'Your property is visible to property seekers again.', 'success');
        location.reload();
    } catch (error) { Swal.fire('Could not publish property', error.message, 'error'); }
}
</script>
@endpush
@endsection
