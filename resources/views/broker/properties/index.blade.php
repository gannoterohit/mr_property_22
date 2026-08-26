@extends('layouts.broker')

@section('title', 'My Properties')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="agent-page-header">
        <div>
            <h2>Your Properties</h2>
            <p>Manage your listings, pricing, and availability.</p>
        </div>
        <a href="{{ route('agent.rooms.create') }}" class="agent-page-header-action">
            <i class="fas fa-plus"></i> Add Property
        </a>
    </div>

    {{-- Stat Tiles --}}
    <div class="owner-room-stats">
        @foreach([
            ['All Properties', $roomCounts['all'],     'fa-building',     'text-indigo-600'],
            ['Active',         $roomCounts['active'],  'fa-circle-check', 'text-emerald-600'],
            ['Pending',        $roomCounts['pending'], 'fa-clock',        'text-amber-600'],
            ['Rented',         $roomCounts['booked'],  'fa-key',          'text-rose-600'],
        ] as $item)
            <div class="owner-room-stat">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas {{ $item[2] }} {{ $item[3] }} text-sm"></i>
                    <p class="text-xs font-semibold text-slate-500">{{ $item[0] }}</p>
                </div>
                <p class="text-2xl font-extrabold text-slate-950">{{ $item[1] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Listing Grid --}}
    <section class="owner-listing-section">
        <div class="owner-listing-heading flex items-end justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-950">All Listings</h2>
                <p class="mt-0.5 text-xs text-slate-500">Click a property to view or edit its details.</p>
            </div>
            <span class="hidden sm:block text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                {{ $properties->total() }} {{ Str::plural('property', $properties->total()) }}
            </span>
        </div>

        @if($properties->count())
            <div class="owner-room-grid">
                @foreach($properties as $property)
                    <article class="owner-room-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="owner-room-media">
                            <div class="owner-room-placeholder"><i class="fas fa-house text-3xl"></i></div>
                            @if($property->photo_url)
                                <img src="{{ $property->photo_url }}" alt="{{ $property->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                            @endif
                            <span class="absolute right-3 top-3 z-10 rounded-full bg-white/90 backdrop-blur-sm px-2.5 py-1 text-[10px] font-extrabold uppercase shadow-sm
                                {{ $property->status === 'active' ? 'text-emerald-700' : ($property->status === 'pending' ? 'text-amber-700' : ($property->status === 'booked' ? 'text-rose-700' : 'text-slate-700')) }}">
                                <span class="inline-block w-1.5 h-1.5 rounded-full mr-1 {{ $property->status === 'active' ? 'bg-emerald-500' : ($property->status === 'pending' ? 'bg-amber-500' : ($property->status === 'booked' ? 'bg-rose-500' : 'bg-slate-400')) }}"></span>
                                {{ $property->status === 'booked' ? 'Rented' : ucfirst($property->status) }}
                            </span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="min-w-0">
                                    <h2 class="truncate font-bold text-slate-950">{{ $property->title }}</h2>
                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        <i class="fas fa-location-dot mr-1 text-rose-400"></i>
                                        {{ $property->city }}{{ $property->state ? ', '.$property->state : '' }}
                                    </p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-extrabold text-slate-950">&#8377;{{ number_format($property->rent) }}</p>
                                    <span class="text-[10px] font-medium text-slate-400">per month</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <a href="{{ route('agent.rooms.show', $property) }}" class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                                    <i class="fas fa-eye text-slate-400"></i> View
                                </a>
                                <a href="{{ route('agent.rooms.edit', $property) }}" class="flex items-center justify-center gap-1.5 rounded-xl bg-indigo-50 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition">
                                    <i class="fas fa-pen"></i> Edit
                                </a>
                            </div>

                            @if($property->status === 'active')
                                <button type="button" onclick="markRoomRented({{ $property->id }})"
                                    class="mt-2.5 flex w-full items-center justify-center gap-2 rounded-xl bg-rose-50 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition">
                                    <i class="fas fa-key"></i> Mark as Rented
                                </button>
                            @elseif($property->status === 'booked')
                                <button type="button" onclick="makeRoomAvailable({{ $property->id }})"
                                    class="mt-2.5 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white hover:bg-emerald-700 transition">
                                    <i class="fas fa-rotate"></i> Make Available
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="agent-empty-state">
                <i class="fas fa-house-circle-xmark"></i>
                <h2>No properties listed yet</h2>
                <p>Add your first property and start receiving enquiries from tenants.</p>
                <a href="{{ route('agent.rooms.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700 transition shadow-sm shadow-indigo-100">
                    <i class="fas fa-plus"></i> Add Your First Property
                </a>
            </div>
        @endif

        @if($properties->hasPages())
            <div class="mt-8">{{ $properties->links() }}</div>
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
