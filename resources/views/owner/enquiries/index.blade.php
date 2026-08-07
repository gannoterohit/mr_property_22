@extends('layouts.owner')

@section('title', 'Room Enquiries - ' . \App\Models\Setting::get('website_name', 'ApnaNest'))

@section('content')
<div class="owner-workspace min-h-screen bg-slate-50">
    @include('owner.partials.sidebar', ['active' => 'enquiries'])

    <main class="flex-1 min-w-0 pb-24 lg:pb-12">
        <header class="border-b border-slate-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 py-7 sm:px-6 lg:px-8">
                <p class="text-[10px] font-extrabold uppercase tracking-[.18em] text-indigo-600">Customer interest</p>
                <div class="mt-1 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div><h1 class="text-2xl font-extrabold text-slate-950">Property Enquiries</h1><p class="mt-2 text-sm text-slate-500">See when a property seeker unlocks contact details for one of your listings.</p></div>
                    <a href="{{ route('owner.rooms') }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700"><i class="fas fa-building mr-2 text-indigo-600"></i>My Properties</a>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 py-7 sm:px-6 lg:px-8">
            <section class="enquiry-stats">
                @foreach([
                    ['Total unlocks', $stats['total'], 'fa-address-card', 'text-indigo-600 bg-indigo-50'],
                    ['Unlocked today', $stats['today'], 'fa-calendar-day', 'text-emerald-600 bg-emerald-50'],
                    ['Properties receiving interest', $stats['rooms'], 'fa-house-circle-check', 'text-amber-600 bg-amber-50'],
                ] as [$label,$value,$icon,$tone])
                    <article><div><p>{{ $label }}</p><strong>{{ number_format($value) }}</strong></div><span class="{{ $tone }}"><i class="fas {{ $icon }}"></i></span></article>
                @endforeach
            </section>

            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4"><div><h2 class="text-sm font-extrabold text-slate-950">Contact unlock activity</h2><p class="mt-1 text-xs text-slate-500">{{ $enquiries->total() }} verified unlock {{ Str::plural('event', $enquiries->total()) }}</p></div><span class="rounded-full bg-indigo-50 px-3 py-1 text-[10px] font-bold text-indigo-700">Page {{ $enquiries->currentPage() }}</span></div>

                <div class="divide-y divide-slate-100">
                    @forelse($enquiries as $enquiry)
                        @php
                            $room = $enquiry->room;
                            $gateway = ucfirst(str_replace('_', ' ', $enquiry->payment?->gateway ?? 'free'));
                            $statusLabel = $room?->status === 'booked' ? 'Rented' : ucfirst($room?->status ?? 'Removed');
                        @endphp
                        <article class="enquiry-row">
                            <a href="{{ $room ? route('rooms.show', $room) : '#' }}" class="enquiry-room-image">
                                @if($room)<img src="{{ $room->photo_url }}" alt="{{ $room->title }}" loading="lazy" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">@else<i class="fas fa-house-circle-xmark"></i>@endif
                            </a>
                            <div class="min-w-0">
                                <p class="text-[9px] font-extrabold uppercase tracking-wider text-indigo-600">Contact unlocked</p>
                                <h3 class="mt-1 truncate text-sm font-extrabold text-slate-900">{{ $room?->title ?? 'Deleted room' }}</h3>
                                <p class="mt-1 truncate text-xs text-slate-500"><i class="fas fa-location-dot mr-1 text-rose-400"></i>{{ $room?->city ?: 'Location unavailable' }}</p>
                            </div>
                            <div class="enquiry-seeker"><span>{{ strtoupper(substr($enquiry->user?->name ?? 'U',0,1)) }}</span><div><small>Property seeker</small><strong>{{ $enquiry->user?->name ?? 'Deleted user' }}</strong></div></div>
                            <div><small class="enquiry-label">Unlock method</small><p class="mt-1 text-xs font-bold text-slate-700">{{ $gateway }}</p></div>
                            <div><small class="enquiry-label">Date & time</small><p class="mt-1 text-xs font-bold text-slate-700">{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('d M Y') }}</p><p class="text-[10px] text-slate-400">{{ ($enquiry->unlocked_at ?? $enquiry->created_at)->format('h:i A') }}</p></div>
                            <div class="flex items-center justify-end gap-2"><span class="rounded-full px-2.5 py-1 text-[9px] font-extrabold {{ $room?->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $statusLabel }}</span>@if($room)<a href="{{ route('rooms.show',$room) }}" class="flex h-9 w-9 items-center justify-center rounded-lg border text-indigo-600" title="View room"><i class="fas fa-arrow-right text-xs"></i></a>@endif</div>
                        </article>
                    @empty
                        <div class="px-6 py-16 text-center"><span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-xl text-indigo-500"><i class="fas fa-address-card"></i></span><h3 class="mt-4 text-base font-extrabold text-slate-900">No enquiries yet</h3><p class="mx-auto mt-2 max-w-md text-sm text-slate-500">When a property seeker unlocks your contact details, the activity will appear here.</p><a href="{{ route('owner.rooms') }}" class="mt-5 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-xs font-bold text-white">Review my listings</a></div>
                    @endforelse
                </div>

                @if($enquiries->hasPages())<div class="border-t border-slate-100 p-4">{{ $enquiries->links() }}</div>@endif
            </section>
        </div>
    </main>
</div>

<style>
.enquiry-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.enquiry-stats article{display:flex;align-items:center;justify-content:space-between;min-height:92px;padding:17px;border:1px solid #e2e8f0;border-radius:15px;background:#fff}.enquiry-stats p{color:#64748b;font-size:10px;font-weight:700}.enquiry-stats strong{display:block;margin-top:5px;color:#0f172a;font-size:23px}.enquiry-stats span{display:grid;place-items:center;width:42px;height:42px;border-radius:12px}.enquiry-row{display:grid;grid-template-columns:68px minmax(170px,1.4fr) minmax(145px,.8fr) 110px 120px 120px;gap:14px;align-items:center;padding:14px 18px}.enquiry-row:hover{background:#f8fafc}.enquiry-room-image{display:grid;place-items:center;width:68px;height:58px;overflow:hidden;border-radius:10px;background:#f1f5f9;color:#94a3b8}.enquiry-room-image img{width:100%;height:100%;object-fit:cover}.enquiry-seeker{display:flex;align-items:center;gap:9px;min-width:0}.enquiry-seeker>span{display:grid;place-items:center;width:34px;height:34px;flex:none;border-radius:10px;background:#eef2ff;color:#4f46e5;font-size:10px;font-weight:900}.enquiry-seeker div{display:grid;min-width:0}.enquiry-seeker small,.enquiry-label{color:#94a3b8;font-size:8px;font-weight:800;text-transform:uppercase}.enquiry-seeker strong{overflow:hidden;color:#334155;font-size:11px;text-overflow:ellipsis;white-space:nowrap}@media(max-width:1100px){.enquiry-row{grid-template-columns:68px minmax(0,1fr) 150px 120px}.enquiry-row>div:nth-child(4),.enquiry-row>div:nth-child(5){display:none}}@media(max-width:700px){.enquiry-stats{grid-template-columns:1fr}.enquiry-row{grid-template-columns:58px minmax(0,1fr) auto;padding:12px}.enquiry-room-image{width:58px;height:54px}.enquiry-seeker{grid-column:2}.enquiry-row>div:nth-child(4),.enquiry-row>div:nth-child(5){display:none}.enquiry-row>div:last-child{grid-column:3;grid-row:1/3}.enquiry-row>div:last-child>span{display:none}}
</style>
@endsection
