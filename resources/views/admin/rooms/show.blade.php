@extends('layouts.admin')

@section('title', 'Property Details')

@push('styles')
<style>
    .admin-room-show-grid { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:20px; align-items:start; }
    .admin-room-gallery { display:grid; grid-template-columns:2fr 1fr; gap:10px; }
    .admin-room-gallery img { width:100%; height:100%; object-fit:cover; border-radius:14px; border:1px solid #e2e8f0; background:#f8fafc; }
    .admin-room-gallery-main { min-height:330px; }
    .admin-room-gallery-side { display:grid; gap:10px; }
    .admin-room-gallery-side img { min-height:160px; }
    .admin-room-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .admin-room-kpi { border:1px solid #e2e8f0; border-radius:14px; background:#fff; padding:14px; }
    .admin-room-kpi span { display:block; color:#64748b; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
    .admin-room-kpi strong { display:block; margin-top:6px; color:#0f172a; font-size:15px; font-weight:900; }
    .admin-room-aside { position:sticky; top:86px; }
    @media(max-width:1199px){ .admin-room-show-grid{grid-template-columns:1fr}.admin-room-aside{position:static} }
    @media(max-width:767px){ .admin-room-gallery,.admin-room-kpis{grid-template-columns:1fr}.admin-room-gallery-main{min-height:240px}.admin-room-gallery-side{grid-template-columns:1fr 1fr}.admin-room-gallery-side img{min-height:110px} }
</style>
@endpush

@section('admin-content')
@php
    $photos = collect($room->photo_urls ?? [])->filter()->values();
    $mainPhoto = $photos->first() ?: asset('storage/default-room.jpg');
    $approvalClass = match($room->listing_status) {
        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'rejected' => 'bg-red-50 text-red-700 border-red-100',
        default => 'bg-amber-50 text-amber-700 border-amber-100',
    };
    $moderationClass = $room->moderation_status === 'normal'
        ? 'bg-slate-100 text-slate-700 border-slate-200'
        : 'bg-red-50 text-red-700 border-red-100';
    $currentAmenities = is_array($room->amenities) ? $room->amenities : (json_decode($room->amenities, true) ?? []);
    $currentLandmarks = is_array($room->landmarks) ? $room->landmarks : (json_decode($room->landmarks, true) ?? []);
@endphp

<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.all-rooms') }}" class="admin-theme-text text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>All listings</a>
            <p class="admin-theme-text mt-3 text-[10px] font-extrabold uppercase tracking-[.2em]">Property management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $room->title }}</h1>
            <p class="text-sm text-slate-500">Internal property review, owner details and publishing controls.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('rooms.show', $room) }}" target="_blank" class="rounded-xl border bg-white px-4 py-2.5 text-xs font-bold text-slate-700"><i class="fas fa-up-right-from-square mr-2"></i>Public preview</a>
            <a href="{{ route('admin.rooms.edit', $room) }}" class="admin-theme-bg rounded-xl px-4 py-2.5 text-xs font-bold"><i class="fas fa-pen mr-2"></i>Edit property</a>
        </div>
    </header>

    <div class="admin-room-show-grid">
        <main class="min-w-0 space-y-5">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="admin-room-gallery">
                    <img class="admin-room-gallery-main" src="{{ $mainPhoto }}" alt="{{ $room->title }}" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">
                    <div class="admin-room-gallery-side">
                        @foreach($photos->skip(1)->take(2) as $photo)
                            <img src="{{ $photo }}" alt="{{ $room->title }} photo {{ $loop->iteration + 1 }}" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">
                        @endforeach
                        @if($photos->count() < 2)
                            <div class="flex min-h-[160px] items-center justify-center rounded-2xl border bg-slate-50 text-slate-300"><i class="fas fa-image text-2xl"></i></div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="admin-room-kpis">
                <div class="admin-room-kpi"><span>Rent</span><strong>&#8377;{{ number_format((float) $room->rent) }}/mo</strong></div>
                <div class="admin-room-kpi"><span>Deposit</span><strong>&#8377;{{ number_format((float) $room->deposit) }}</strong></div>
                <div class="admin-room-kpi"><span>Property type</span><strong>{{ $room->roomTypeLabel() }}</strong></div>
                <div class="admin-room-kpi"><span>Availability</span><strong>{{ ucfirst($room->status) }}</strong></div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-950">Listing information</h2>
                        <p class="text-xs text-slate-500">Content visible to users on public room pages.</p>
                    </div>
                    @if($room->is_featured)
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700">Featured</span>
                    @endif
                </div>
                <dl class="grid gap-4 md:grid-cols-2">
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Address</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->address ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">City</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->city ?: 'Not provided' }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Furnishing</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->furnishingTypeLabel() }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Preferred tenant</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->tenantTypeLabel() }}</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Listing type</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ ucfirst($room->listing_type ?? 'owner') }} @if($room->listing_type === 'broker') - Broker fee &#8377;{{ number_format((float) $room->broker_fee) }} @endif</dd></div>
                    <div><dt class="text-[10px] font-bold uppercase text-slate-400">Available from</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $room->availability_from ? \Carbon\Carbon::parse($room->availability_from)->format('d M Y') : 'Not specified' }}</dd></div>
                </dl>
                <div class="mt-5 border-t pt-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Description</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $room->description ?: 'No description added.' }}</p>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Amenities</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse($currentAmenities as $amenity)
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-bold text-slate-600">{{ $amenity }}</span>
                        @empty
                            <p class="text-sm text-slate-500">No amenities listed.</p>
                        @endforelse
                    </div>
                </div>
                <div class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Nearby landmarks</h2>
                    <div class="mt-4 space-y-2">
                        @forelse($currentLandmarks as $landmark)
                            <p class="rounded-xl bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600"><i class="fas fa-location-dot mr-2 text-slate-400"></i>{{ $landmark }}</p>
                        @empty
                            <p class="text-sm text-slate-500">No landmarks added.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <aside class="admin-room-aside space-y-4">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Review status</h2>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2 {{ $approvalClass }}"><span class="text-xs font-bold">Approval</span><strong class="text-xs uppercase">{{ $room->listing_status }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border px-3 py-2 {{ $moderationClass }}"><span class="text-xs font-bold">Moderation</span><strong class="text-xs uppercase">{{ $room->moderation_status }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Listing fee</span><strong class="text-xs uppercase">{{ $room->listing_fee_paid ? 'Paid' : 'Unpaid' }}</strong></div>
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-slate-600"><span class="text-xs font-bold">Expires</span><strong class="text-xs">{{ $room->expires_at?->format('d M Y') ?? 'Not set' }}</strong></div>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Owner</h2>
                <div class="mt-4 flex items-center gap-3">
                    <div class="admin-theme-soft flex h-11 w-11 items-center justify-center rounded-xl text-sm font-extrabold">{{ strtoupper(substr($room->owner?->name ?? 'O', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <a href="{{ $room->owner ? route('admin.owners.detail', $room->owner) : '#' }}" class="admin-theme-text block truncate text-sm font-extrabold">{{ $room->owner?->name ?? 'Unknown owner' }}</a>
                        <p class="truncate text-xs text-slate-500">{{ $room->owner?->email ?? 'Email unavailable' }}</p>
                    </div>
                </div>
                <dl class="mt-4 space-y-3 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Phone</dt><dd class="font-bold text-slate-700">{{ $room->owner?->phone ?? 'Not provided' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">KYC</dt><dd class="font-bold text-slate-700">{{ ucfirst(str_replace('_', ' ', $room->owner?->verification_status ?? 'unknown')) }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Created</dt><dd class="font-bold text-slate-700">{{ $room->created_at->format('d M Y') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">Updated</dt><dd class="font-bold text-slate-700">{{ $room->updated_at->format('d M Y, h:i A') }}</dd></div>
                </dl>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold text-slate-950">Actions</h2>
                <div class="mt-4 grid gap-2">
                    @if($room->listing_status !== 'approved')
                        <form method="POST" action="{{ route('admin.rooms.approve', $room) }}" class="admin-room-json-form">
                            @csrf
                            <button class="w-full rounded-xl bg-emerald-600 py-3 text-sm font-bold text-white"><i class="fas fa-check mr-2"></i>Approve listing</button>
                        </form>
                    @endif
                    <a href="{{ route('admin.rooms.edit', $room) }}" class="flex h-11 items-center justify-center rounded-xl border bg-white text-sm font-bold text-slate-700"><i class="fas fa-pen mr-2"></i>Edit listing</a>
                    <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}" class="admin-confirm" data-confirm-title="Delete this property listing?" data-confirm-text="This listing and its related data will be permanently removed." data-confirm-button="Yes, delete listing">
                        @csrf @method('DELETE')
                        <button class="w-full rounded-xl bg-red-50 py-3 text-sm font-bold text-red-700"><i class="fas fa-trash mr-2"></i>Delete listing</button>
                    </form>
                </div>
            </section>

            @if($room->listing_status !== 'rejected')
                <section class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-950">Reject listing</h2>
                    <form method="POST" action="{{ route('admin.rooms.reject', $room) }}" class="admin-room-json-form mt-4 space-y-3">
                        @csrf
                        <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                            @foreach($rejectionReasons as $reason)
                                <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 p-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                    <input type="checkbox" name="reasons[]" value="{{ $reason->id }}" class="mt-0.5 rounded">
                                    <span>{{ $reason->reason }}</span>
                                </label>
                            @endforeach
                        </div>
                        <textarea name="customReason" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="Optional custom reason"></textarea>
                        <button class="w-full rounded-xl bg-red-600 py-3 text-sm font-bold text-white"><i class="fas fa-ban mr-2"></i>Reject listing</button>
                    </form>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.admin-room-json-form').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"], button:not([type])');
        const original = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' },
                body: new FormData(form)
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Request failed');
            toastr.success(data.message || 'Listing updated.', 'Success');
            setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            toastr.error(error.message, 'Error');
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    });
});
</script>
@endpush
