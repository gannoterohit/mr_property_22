@extends('layouts.admin')

@section('title','Property Listings')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-list.css') }}">

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
            <x-admin.data-actions dataset="rooms" />
            <x-admin.button :href="route('admin.rejection-reasons.index')">Rejection reasons</x-admin.button>
            <x-admin.button variant="primary" icon="fa-plus" :href="route('admin.rooms.create')">Add property</x-admin.button>
        </div>
    </header>

    @php
        $advancedOpen = request()->filled('moderation_status') || request()->filled('kyc') || request()->filled('property_category_id') || request()->filled('min_area_sqft') || request()->filled('max_area_sqft');
        $activeFilterCount = collect(['search','listing_status','status','city','property_type_id','moderation_status','kyc','property_category_id','min_area_sqft','max_area_sqft'])->filter(fn($key) => request()->filled($key))->count();
    @endphp
    <form method="GET" class="rounded-2xl border bg-white p-4 shadow-sm">
        <div class="room-filter-main">
            <input name="search" value="{{ request('search') }}" placeholder="Search title, city..." class="h-10 rounded-xl text-xs">
            <select name="listing_status" class="h-10 rounded-xl text-xs">
                <option value="">Approval</option>
                @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v)
                    <option value="{{ $k }}" @selected(request('listing_status')===$k)>{{ $v }}</option>
                @endforeach
            </select>
            <select name="status" class="h-10 rounded-xl text-xs">
                <option value="">Availability</option>
                <option value="active" @selected(request('status')==='active')>Active</option>
                <option value="booked" @selected(request('status')==='booked')>Rented / booked</option>
            </select>
            <select name="city" class="h-10 rounded-xl text-xs">
                <option value="">City</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" @selected(request('city')===$city)>{{ $city }}</option>
                @endforeach
            </select>
            <select name="property_type_id" class="h-10 rounded-xl text-xs">
                <option value="">Property type</option>
                @foreach($propertyTypes as $type)
                    <option value="{{ $type->id }}" @selected(request('property_type_id')==$type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <x-admin.button type="submit" variant="primary">Filter</x-admin.button>
                <x-admin.button :href="route('admin.all-rooms')">Reset</x-admin.button>
            </div>
        </div>

        <details class="mt-3 border-t pt-3" {{ $advancedOpen ? 'open' : '' }}>
            <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700">
                <i class="fas fa-sliders"></i>
                Advanced filters
                @if($activeFilterCount)
                    <span class="rounded-full admin-theme-soft px-2 py-0.5 text-[10px] admin-theme-text">{{ $activeFilterCount }} active</span>
                @endif
            </summary>
            <div class="room-filter-advanced mt-3">
                <select name="moderation_status" class="h-10 rounded-xl text-xs">
                    <option value="">Moderation</option>
                    @foreach(['normal'=>'Normal','suspended'=>'Suspended','reported'=>'Reported'] as $k=>$v)
                        <option value="{{ $k }}" @selected(request('moderation_status')===$k)>{{ ucfirst($v) }}</option>
                    @endforeach
                </select>
                <select name="kyc" class="h-10 rounded-xl text-xs">
                    <option value="">Owner KYC</option>
                    @foreach(['pending','under_review','verified','rejected'] as $v)
                        <option value="{{ $v }}" @selected(request('kyc')===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
                <select name="property_category_id" class="h-10 rounded-xl text-xs">
                    <option value="">Property category</option>
                    @foreach($propertyCategories as $category)
                        <option value="{{ $category->id }}" @selected(request('property_category_id')==$category->id)>{{ $category->propertyType?->name ? $category->propertyType->name.' - ' : '' }}{{ $category->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="min_area_sqft" value="{{ request('min_area_sqft') }}" placeholder="Min sq ft" class="h-10 rounded-xl text-xs">
                <input type="number" name="max_area_sqft" value="{{ request('max_area_sqft') }}" placeholder="Max sq ft" class="h-10 rounded-xl text-xs">
            </div>
        </details>
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
            <table class="room-table admin-table-base">
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
                                <div class="flex flex-col items-start gap-1.5">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold {{ $room->listing_status==='approved'?'bg-emerald-50 text-emerald-700':($room->listing_status==='rejected'?'bg-red-50 text-red-700':'bg-amber-50 text-amber-700') }}">{{ ucfirst($room->listing_status) }}</span>
                                    <x-admin.status-toggle
                                        :active="$room->status === 'active'"
                                        active-label="Active"
                                        inactive-label="Booked"
                                        form="toggle-room-{{ $room->id }}"
                                        title="Click to {{ $room->status === 'active' ? 'mark as booked' : 'mark as active' }}"
                                    />
                                </div>
                            </td>
                            <td class="px-4 text-xs font-bold text-slate-600">
                                <span class="block">{{ $room->propertyType?->name ?? 'N/A' }}</span>
                                @if($room->propertyCategory?->name)
                                    <span class="mt-1 block text-[10px] font-semibold text-slate-400">{{ $room->propertyCategory->name }}</span>
                                @endif
                                @if($room->area_sqft)
                                    <span class="mt-1 block text-[10px] font-semibold text-slate-500">{{ number_format((float)$room->area_sqft, 2) }} sqft</span>
                                @endif
                            </td>
                            <td class="px-4">
                                <div class="flex justify-end items-center gap-2">
                                    <x-admin.action-icon variant="view" :href="route('admin.rooms.show',$room)" />
                                    <x-admin.action-icon variant="edit" :href="route('admin.rooms.edit',$room)" />
                                    <x-admin.action-icon variant="delete" type="submit" form="delete-room-{{ $room->id }}" />
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
