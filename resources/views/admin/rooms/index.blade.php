@extends('layouts.admin')

@section('title','Property Listings')


@push('styles')
<style>
    .room-filter{display:grid!important;grid-template-columns:minmax(190px,1fr) repeat(5,150px) auto!important;gap:8px}
    .room-table{min-width:1240px;width:100%}
    .room-table th,.room-table td{text-align:left!important;vertical-align:middle!important}
    .room-table th:last-child,.room-table td:last-child{text-align:right!important}
    @media(max-width:1279px){.room-filter{grid-template-columns:repeat(3,1fr)!important}}
    @media(max-width:767px){.room-filter{grid-template-columns:1fr!important}}
</style>
@endpush

@section('admin-content')
<div class="space-y-4 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="admin-theme-text text-[10px] font-extrabold uppercase tracking-[.2em]">Property management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Property Listings</h1>
            <p class="text-sm text-slate-500">Review, approve, reject and moderate every listing.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.rejection-reasons.index') }}" class="rounded-xl border bg-white px-4 py-3 text-xs font-bold text-slate-700">Rejection reasons</a>
            <a href="{{ route('admin.rooms.create') }}" class="admin-theme-bg rounded-xl px-4 py-3 text-xs font-bold"><i class="fas fa-plus mr-2"></i>Add property</a>
        </div>
    </header>

    <form method="GET" class="room-filter rounded-2xl border bg-white p-4 shadow-sm">
        <input name="search" value="{{ request('search') }}" placeholder="Title or city" class="h-10 rounded-xl text-xs">
        <select name="listing_status" class="h-10 rounded-xl text-xs">
            <option value="">All approval states</option>
            @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v)
                <option value="{{ $k }}" @selected(request('listing_status')===$k)>{{ $v }}</option>
            @endforeach
        </select>
        <select name="moderation_status" class="h-10 rounded-xl text-xs">
            <option value="">All moderation</option>
            @foreach(['normal'=>'Normal','suspended'=>'Suspended','reported'=>'Reported'] as $k=>$v)
                <option value="{{ $k }}" @selected(request('moderation_status')===$k)>{{ ucfirst($v) }}</option>
            @endforeach
        </select>
        <select name="status" class="h-10 rounded-xl text-xs">
            <option value="">Availability</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="booked" @selected(request('status')==='booked')>Rented / booked</option>
        </select>
        <select name="kyc" class="h-10 rounded-xl text-xs">
            <option value="">Owner KYC</option>
            @foreach(['pending','under_review','verified','rejected'] as $v)
                <option value="{{ $v }}" @selected(request('kyc')===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
            @endforeach
        </select>
        <select name="city" class="h-10 rounded-xl text-xs">
            <option value="">All cities</option>
            @foreach($cities as $city)
                <option value="{{ $city }}" @selected(request('city')===$city)>{{ $city }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="rounded-xl bg-slate-900 px-4 text-xs font-bold text-white">Filter</button>
            <a href="{{ route('admin.all-rooms') }}" class="flex h-10 items-center rounded-xl border px-3 text-xs font-bold text-slate-700">Reset</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.rooms.bulk') }}" class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        @csrf
        <div class="flex flex-wrap items-center justify-between gap-3 border-b bg-slate-50 px-5 py-3">
            <p class="text-xs font-bold text-slate-700">{{ $allrooms->total() }} listings - Showing {{ $allrooms->firstItem()??0 }}-{{ $allrooms->lastItem()??0 }}</p>
            <div class="flex gap-2">
                <select name="action" required class="h-9 rounded-lg text-xs">
                    <option value="">Bulk action</option>
                    <option value="approve">Approve</option>
                    <option value="suspend">Suspend</option>
                    <option value="activate">Activate</option>
                    <option value="mark_reported">Mark reported</option>
                </select>
                <button class="admin-theme-bg rounded-lg px-4 text-xs font-bold">Apply</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="room-table">
                <thead>
                    <tr>
                        <th><input id="selectAllRooms" type="checkbox"></th>
                        <th>Property</th>
                        <th>Owner / KYC</th>
                        <th>Location & rent</th>
                        <th>Approval / Status</th>
                        <th>Property type</th>
                        <th class="text-right w-[190px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($allrooms as $room)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4"><input type="checkbox" name="room_ids[]" value="{{ $room->id }}" class="room-check rounded"></td>
                            <td class="px-4">
                                <div class="flex min-w-[220px] items-center gap-3">
                                    <div class="h-12 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                        @if($room->photo_url)
                                            <img src="{{ $room->photo_url }}" class="h-full w-full object-cover" alt="{{ $room->title }}">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.rooms.show',$room) }}" class="admin-theme-text block max-w-[220px] truncate text-xs font-bold">{{ $room->title }}</a>
                                        <p class="text-[10px] text-slate-400">#{{ $room->id }} - {{ $room->roomTypeOption?->label??'Property' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4">
                                <a href="{{ route('admin.owners.detail',$room->owner) }}" class="admin-theme-hover-text text-xs font-bold text-slate-800">{{ $room->owner->name }}</a>
                                <p class="text-[10px] {{ $room->owner->verification_status==='verified'?'text-emerald-600':'text-amber-600' }}">{{ ucfirst(str_replace('_',' ',$room->owner->verification_status)) }}</p>
                            </td>
                            <td class="px-4">
                                <p class="text-xs">{{ $room->city }}</p>
                                <p class="text-xs font-bold">&#8377;{{ number_format($room->rent) }}/mo</p>
                            </td>
                            <td class="px-4">
                                <div class="flex flex-col gap-2">
                                    <span class="rounded-full px-2 py-1 text-[10px] font-bold {{ $room->listing_status==='approved'?'bg-emerald-50 text-emerald-700':($room->listing_status==='rejected'?'bg-red-50 text-red-700':'bg-amber-50 text-amber-700') }}">{{ ucfirst($room->listing_status) }}</span>
                                    <button type="submit" form="toggle-room-{{ $room->id }}" class="inline-flex h-8 w-[104px] items-center justify-start gap-2 rounded-full border px-2 text-[10px] font-bold transition-colors {{ $room->status === 'active' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}" title="Click to {{ $room->status === 'active' ? 'mark as booked' : 'mark as active' }}">
                                        <span class="relative inline-flex h-4 w-7 shrink-0 rounded-full transition-colors {{ $room->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}">
                                            <span class="absolute left-0.5 top-0.5 h-3 w-3 rounded-full bg-white shadow-sm transition-transform duration-200 {{ $room->status === 'active' ? 'translate-x-3' : 'translate-x-0' }}"></span>
                                        </span>
                                        <span class="inline-block w-12 text-left">{{ $room->status === 'active' ? 'Active' : 'Booked' }}</span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 text-xs font-bold text-slate-600">
                                {{ $room->propertyType?->name ?? 'N/A' }}
                            </td>
                            <td class="px-4">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.rooms.show',$room) }}" class="h-8 px-2.5 inline-flex items-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-800 hover:text-white text-[10px] font-bold transition"><i class="fas fa-eye mr-1"></i>View</a>
                                    <a href="{{ route('admin.rooms.edit',$room) }}" class="h-8 px-2.5 inline-flex items-center rounded-lg bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-800 hover:text-white text-[10px] font-bold transition"><i class="fas fa-pen mr-1"></i>Edit</a>
                                    <button type="submit" form="delete-room-{{ $room->id }}" class="inline-flex h-8 items-center rounded-lg border border-red-100 bg-white px-2.5 text-[10px] font-bold text-red-600 transition hover:bg-red-600 hover:text-white">
                                        <i class="fas fa-trash-can mr-1"></i>Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-12 text-center text-sm text-slate-500">No listings match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($allrooms->hasPages())
            <div class="border-t p-4">{{ $allrooms->links() }}</div>
        @endif
    </form>

    @foreach($allrooms as $room)
        <form id="toggle-room-{{ $room->id }}" action="{{ route('admin.rooms.toggle-status', $room) }}" method="POST" class="hidden toggle-room-status" data-label="{{ $room->title }}" data-active="{{ $room->status === 'active' ? '1' : '0' }}">
            @csrf
            @method('PATCH')
        </form>
        <form id="delete-room-{{ $room->id }}" action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="hidden delete-room-option" data-label="{{ $room->title }}">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const csrf=document.querySelector('meta[name="csrf-token"]').content;
document.getElementById('selectAllRooms')?.addEventListener('change',e=>document.querySelectorAll('.room-check').forEach(c=>c.checked=e.target.checked));

document.querySelectorAll('.toggle-room-status').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const isActive = form.dataset.active === '1';
        const action = isActive ? 'Mark as booked' : 'Mark as active';
        const result = await Swal.fire({
            title: `${action}?`,
            text: form.dataset.label,
            icon: isActive ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action.toLowerCase()}`,
            cancelButtonText: 'Cancel',
            confirmButtonColor: isActive ? '#dc2626' : '#059669',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});

document.querySelectorAll('.delete-room-option').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const result = await Swal.fire({
            title: `Delete ${form.dataset.label}?`,
            text: 'This permanently removes the property and its related listing data. This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete permanently',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc2626',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endpush
