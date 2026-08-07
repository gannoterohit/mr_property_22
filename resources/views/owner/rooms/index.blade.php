@extends('layouts.owner')

@section('title', 'My Properties - ' . \App\Models\Setting::get('website_name', 'RoomRental'))

@push('styles')
<style>
    .owner-rooms-page { background: #f8fafc; }
    .owner-rooms-header-inner { padding-top: 2rem !important; padding-bottom: 2rem !important; }
    .owner-rooms-content { padding-top: 2rem !important; padding-bottom: 3.5rem !important; }
    .owner-room-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 2rem !important;
    }
    .owner-room-stat {
        min-height: 88px;
        padding: 1rem 1.125rem !important;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
    }
    .owner-listing-section { padding-top: .25rem; }
    .owner-listing-heading { margin-bottom: 1.25rem !important; }
    .owner-room-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
        align-items: start;
    }
    .owner-room-card { min-width: 0; height: auto !important; align-self: start; }
    .owner-room-media {
        position: relative;
        width: 100%;
        height: 220px !important;
        min-height: 220px;
        max-height: 220px;
        overflow: hidden;
        background: linear-gradient(135deg, #eef2ff, #f8fafc);
    }
    .owner-room-media img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .owner-room-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #94a3b8; }
    @media (max-width: 1279px) { .owner-room-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767px) {
        .owner-rooms-header-inner { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
        .owner-rooms-content { padding-top: 1.5rem !important; padding-bottom: 2.5rem !important; }
        .owner-room-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1.5rem !important; }
        .owner-room-stat { min-height: 78px; padding: .875rem !important; }
        .owner-room-grid { grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .owner-room-media { height: 200px !important; min-height: 200px; max-height: 200px; }
    }
</style>
@endpush

@section('content')
<div class="owner-workspace owner-rooms-page min-h-screen flex bg-slate-50">
    @include('owner.partials.sidebar', ['active' => 'rooms'])
    <main class="flex-1 min-w-0 pb-24 lg:pb-12">
        <header class="border-b border-slate-200 bg-white">
            <div class="owner-rooms-header-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                <div><p class="text-xs font-bold uppercase tracking-[.18em] text-indigo-600">Property management</p><h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-950">My Properties</h1><p class="mt-2 text-sm text-slate-500">View and manage all your property listings in one place.</p></div>
                <a href="{{ route('rooms.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700"><i class="fas fa-plus"></i>Add New Property</a>
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
                    <span class="hidden sm:block text-xs font-bold text-slate-400">{{ $myRooms->total() }} {{ Str::plural('property', $myRooms->total()) }}</span>
                </div>
            @if($myRooms->count())
                <div class="owner-room-grid">
                    @foreach($myRooms as $room)
                        <article class="owner-room-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition">
                            <div class="owner-room-media">
                                <div class="owner-room-placeholder"><i class="fas fa-house text-3xl"></i></div>
                                @if($room->photo_url)
                                    <img src="{{ $room->photo_url }}" alt="{{ $room->title }}" loading="lazy" onerror="this.style.display='none'">
                                @endif
                                <span class="absolute right-3 top-3 z-10 rounded-full bg-white px-2.5 py-1 text-[10px] font-extrabold uppercase shadow-sm {{ $room->status === 'active' ? 'text-emerald-700' : ($room->status === 'pending' ? 'text-amber-700' : 'text-slate-700') }}">{{ $room->status }}</span>
                            </div>
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0"><h2 class="truncate font-bold text-slate-950">{{ $room->title }}</h2><p class="mt-1 truncate text-xs text-slate-500"><i class="fas fa-location-dot mr-1 text-rose-400"></i>{{ $room->city }}{{ $room->state ? ', '.$room->state : '' }}</p></div>
                                    <p class="shrink-0 text-sm font-extrabold text-slate-950">&#8377;{{ number_format($room->rent) }}<span class="block text-right text-[10px] font-medium text-slate-400">per month</span></p>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3"><a href="{{ route('rooms.show', $room) }}" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50"><i class="fas fa-eye"></i>View</a><a href="{{ route('rooms.edit', $room) }}" class="flex items-center justify-center gap-2 rounded-xl bg-indigo-50 py-2.5 text-xs font-bold text-indigo-700 hover:bg-indigo-100"><i class="fas fa-pen"></i>Edit</a></div>
                                @if($room->status === 'active')
                                    <button type="button" onclick="markRoomRented({{ $room->id }})" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-rose-50 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100"><i class="fas fa-key"></i>Mark as Rented</button>
                                @elseif($room->status === 'booked')
                                    <button type="button" onclick="makeRoomAvailable({{ $room->id }})" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-xs font-bold text-white hover:bg-emerald-700"><i class="fas fa-rotate"></i>Make Available</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"><i class="fas fa-house-circle-xmark text-4xl text-slate-300"></i><h2 class="mt-4 text-lg font-bold text-slate-900">No properties listed yet</h2><p class="mt-2 text-sm text-slate-500">Add your first property and start receiving enquiries.</p><a href="{{ route('rooms.create') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white">Add Your First Property</a></div>
            @endif
            @if($myRooms->hasPages())<div class="mt-8">{{ $myRooms->links() }}</div>@endif
            </section>
        </div>
    </main>
</div>

@push('scripts')
<script>
const ownerRoomCsrf = '{{ csrf_token() }}';
const ownerRazorpayKey = '{{ \App\Models\Setting::get("razorpay_key", "") }}';
const listingFeeEnabled = @json(filter_var(\App\Models\Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN));

async function ownerRoomPost(url, payload = {}) {
    const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': ownerRoomCsrf, 'Accept': 'application/json' }, body: JSON.stringify(payload) });
    const data = await response.json().catch(() => ({ success: false, message: 'Invalid server response' }));
    if (!response.ok) throw new Error(data.message || 'Request failed');
    return data;
}

async function markRoomRented(roomId) {
    const result = await Swal.fire({ title: 'Mark property as rented?', text: 'This property will stop appearing to property seekers.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, mark rented', confirmButtonColor: '#e11d48' });
    if (!result.isConfirmed) return;
    try { const data = await ownerRoomPost(`{{ route('rooms.markBooked', ':room') }}`.replace(':room', roomId)); await Swal.fire('Property rented', data.message, 'success'); location.reload(); }
    catch (error) { Swal.fire('Could not update property', error.message, 'error'); }
}

async function makeRoomAvailable(roomId) {
    if (!listingFeeEnabled) {
        const confirmation = await Swal.fire({ title: 'Make property available?', text: 'The listing fee is currently disabled, so no payment or plan credit will be used.', icon: 'question', showCancelButton: true, confirmButtonText: 'Make available', confirmButtonColor: '#059669' });
        if (!confirmation.isConfirmed) return;
        try {
            const data = await ownerRoomPost(`{{ route('rooms.markAvailable', ':room') }}`.replace(':room', roomId), { payment_method: 'free' });
            await Swal.fire('Property available', data.message || 'Your property is visible to property seekers again.', 'success');
            location.reload();
            } catch (error) { Swal.fire('Could not publish property', error.message, 'error'); }
        const data = await ownerRoomPost(`{{ route('rooms.markAvailable', ':room') }}`.replace(':room', roomId), { payment_method: paymentMethod });
        if (data.payment_id) return startAvailabilityPayment(data.payment_id, data.amount, roomId);
        await Swal.fire('Property available', data.message || 'Your property is visible to users again.', 'success');
        location.reload();
    } catch (error) { Swal.fire('Could not publish property', error.message, 'error'); }
}

async function startAvailabilityPayment(paymentId, amount, roomId) {
    try {
        const RazorpayClient = await loadRazorpaySDK();
        const order = await ownerRoomPost('{{ route('razorpay.createOrder') }}', { payment_id: paymentId });
        if (!order.success || !order.order_id) throw new Error(order.message || 'Payment order could not be created');
        new RazorpayClient({ key: ownerRazorpayKey, amount: order.amount * 100, currency: 'INR', name: '{{ \App\Models\Setting::get("website_name", "RoomRental") }}', description: 'Property listing activation', order_id: order.order_id,
            handler: async function (response) {
                try {
                    const verified = await ownerRoomPost('{{ route('razorpay.verify') }}', { razorpay_payment_id: response.razorpay_payment_id, razorpay_order_id: response.razorpay_order_id || order.order_id, razorpay_signature: response.razorpay_signature, payment_id: paymentId, type: 'listing', reference_id: roomId });
                    if (verified.status !== 'success') throw new Error(verified.message || 'Payment verification failed');
                    await Swal.fire('Payment successful', 'Property is available to users again.', 'success'); location.reload();
                } catch (error) { Swal.fire('Verification failed', error.message, 'error'); }
            }, prefill: { name: '{{ Auth::user()->name }}', email: '{{ Auth::user()->email }}' }, theme: { color: '#4f46e5' }
        }).open();
    } catch (error) { Swal.fire('Payment could not start', error.message, 'error'); }
}
</script>
@endpush
@endsection
