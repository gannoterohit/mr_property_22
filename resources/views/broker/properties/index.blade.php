@extends('layouts.broker')

@section('title', 'My Properties')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-rooms.css') }}">
@endpush

@section('broker-content')
@php $user = Auth::user(); @endphp
<div class="min-h-screen bg-slate-50">
    <header class="border-b border-slate-200 bg-white">
        <div class="owner-rooms-header-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div><p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Agent panel</p><h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-950">My Properties</h1><p class="mt-2 text-sm text-slate-500">View and manage all your property listings in one place.</p></div>
            <a href="{{ route('agent.rooms.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700"><i class="fas fa-plus"></i>Add New Property</a>
        </div>
    </header>
    <div class="owner-rooms-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="owner-room-stats">
            @foreach([['All properties','all'],['Active','active'],['Pending','pending'],['Rented','booked']] as $item)
                <div class="owner-room-stat"><p class="text-xs font-semibold text-slate-500">{{ $item[0] }}</p><p class="mt-2 text-2xl font-extrabold text-slate-950">{{ $roomCounts[$item[1]] }}</p></div>
            @endforeach
        </div>
        <section class="owner-listing-section">
            <div class="owner-listing-heading flex items-end justify-between gap-4">
                <div><h2 class="text-lg font-extrabold text-slate-950">Your listings</h2><p class="mt-1 text-sm text-slate-500">Manage property details, pricing and availability.</p></div>
                <span class="hidden sm:block text-xs font-bold text-slate-400">{{ $properties->total() }} {{ Str::plural('property', $properties->total()) }}</span>
            </div>
            @if($properties->count())
                <div class="owner-room-grid">
                    @foreach($properties as $property)
                        <article class="owner-room-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
                            <div class="owner-room-media">
                                <div class="owner-room-placeholder"><i class="fas fa-house text-3xl"></i></div>
                                @if($property->photo_url)
                                     <img src="{{ $property->photo_url }}" alt="{{ $property->title }}" width="400" height="300" loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="absolute right-3 top-3 z-10 rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase shadow-sm {{ $property->status === 'active' ? 'text-emerald-700' : ($property->status === 'pending' ? 'text-amber-700' : 'text-slate-700') }}">{{ $property->status }}</span>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0"><h2 class="truncate font-bold text-slate-950">{{ $property->title }}</h2><p class="mt-1 truncate text-xs text-slate-500"><i class="fas fa-location-dot mr-1 text-rose-400"></i>{{ $property->city }}{{ $property->state ? ', '.$property->state : '' }}</p></div>
                                    <p class="shrink-0 text-sm font-extrabold text-slate-950">&#8377;{{ number_format($property->rent) }}<span class="block text-right text-[10px] font-medium text-slate-400">per month</span></p>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3"><a href="{{ route('agent.rooms.show', $property) }}" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50"><i class="fas fa-eye"></i>View</a><a href="{{ route('agent.rooms.edit', $property) }}" class="flex items-center justify-center gap-2 rounded-xl bg-indigo-50 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100"><i class="fas fa-pen"></i>Edit</a></div>
                                @if($property->status === 'active')
                                    <button type="button" onclick="markRoomRented({{ $property->id }})" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-rose-50 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100"><i class="fas fa-key"></i>Mark as Rented</button>
                                @elseif($property->status === 'booked')
                                    <button type="button" onclick="makeRoomAvailable({{ $property->id }})" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white hover:bg-emerald-700"><i class="fas fa-rotate"></i>Make Available</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><i class="fas fa-house-circle-xmark text-4xl text-slate-300"></i><h2 class="mt-4 text-lg font-bold text-slate-900">No properties listed yet</h2><p class="mt-2 text-sm text-slate-500">Add your first property and start receiving enquiries.</p><a href="{{ route('agent.rooms.create') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">Add Your First Property</a></div>
            @endif
            @if($properties->hasPages())<div class="mt-8">{{ $properties->links() }}</div>@endif
        </section>
    </div>
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
