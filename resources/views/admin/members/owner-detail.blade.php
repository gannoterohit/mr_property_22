@extends('layouts.admin')

@section('title','Owner Details')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    @include('admin.members.nav')

    <header class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.owners') }}" class="admin-theme-text text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>All owners</a>
            <div class="mt-3 flex items-center gap-3">
                <div class="admin-theme-bg flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-extrabold">{{ strtoupper(substr($owner->name,0,1)) }}</div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-950">{{ $owner->name }}</h1>
                    <p class="text-sm text-slate-500">{{ $owner->email }} - {{ $owner->phone ?: 'No phone number' }}</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-2 text-xs font-bold {{ $owner->is_blocked?'bg-red-50 text-red-700':'bg-emerald-50 text-emerald-700' }}">{{ $owner->is_blocked?'Blocked account':'Active account' }}</span>
            <span class="admin-theme-soft rounded-full px-3 py-2 text-xs font-bold">KYC: {{ ucfirst(str_replace('_',' ',$owner->verification_status)) }}</span>
            <button onclick="document.getElementById('direct-msg-card').scrollIntoView({behavior:'smooth'})" class="rounded-xl border bg-indigo-50 px-4 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"><i class="fas fa-paper-plane mr-1"></i>Send Message</button>
            <a href="{{ route('admin.owners.edit',$owner) }}" class="rounded-xl border bg-white px-4 py-2 text-xs font-bold text-slate-700"><i class="fas fa-pen mr-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.owners.destroy',$owner) }}" class="admin-confirm" data-confirm-title="Delete {{ $owner->name }}?" data-confirm-text="Listings will be retained and the account can be restored later." data-confirm-button="Yes, delete owner">
                @csrf @method('DELETE')
                <button class="rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-700"><i class="fas fa-trash mr-1"></i>Delete</button>
            </form>
        </div>

    </header>

    <section class="admin-detail-stats">
        @foreach([
            ['Listings',$owner->rooms->count(),'fa-building','admin-theme-text'],
            ['Payments',$owner->payments->count(),'fa-credit-card','text-emerald-600'],
            ['Subscriptions',$owner->subscriptions->count(),'fa-id-badge','text-slate-600'],
            ['Complaints',$owner->complaints->count(),'fa-shield-halved','text-amber-600'],
        ] as [$label,$value,$icon,$tone])
            <div class="flex items-center gap-4 rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-50 {{ $tone }}"><i class="fas {{ $icon }}"></i></div>
                <div><p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p><p class="text-2xl font-extrabold">{{ $value }}</p></div>
            </div>
        @endforeach
    </section>

    <div class="admin-detail-workspace">
        <main class="min-w-0 space-y-5">
            <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="flex items-center justify-between border-b px-5 py-4">
                    <div><h2 class="text-sm font-extrabold">Property history</h2><p class="text-xs text-slate-500">Listings submitted by this owner</p></div>
                    <span class="text-xs font-bold text-slate-500">{{ $rooms->total() }} total</span>
                </div>
                <div class="grid gap-3 p-5 md:grid-cols-2">
                    @forelse($rooms as $room)
                        <a href="{{ route('admin.rooms.show',$room) }}" class="admin-theme-hover-card group flex min-w-0 gap-3 rounded-xl border p-3 transition">
                            <div class="h-16 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                @if($room->photo_url)
                                    <img src="{{ $room->photo_url }}" class="h-full w-full object-cover" alt="{{ $room->title }}">
                                @else
                                    <div class="flex h-full items-center justify-center text-slate-300"><i class="fas fa-building"></i></div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <strong class="block truncate text-sm">{{ $room->title }}</strong>
                                <p class="mt-1 truncate text-xs text-slate-400">{{ $room->city ?: 'Location unavailable' }}</p>
                                <div class="mt-2 flex gap-2">
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[9px] font-bold">{{ ucfirst($room->listing_status) }}</span>
                                    <span class="rounded-full px-2 py-1 text-[9px] font-bold {{ $room->moderation_status==='normal'?'bg-emerald-50 text-emerald-700':'bg-red-50 text-red-700' }}">{{ ucfirst($room->moderation_status) }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <p class="col-span-2 py-8 text-center text-sm text-slate-500">No properties submitted.</p>
                    @endforelse
                </div>
                @if($rooms->hasPages())<div class="border-t p-4">{{ $rooms->links() }}</div>@endif
            </section>

            <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="text-sm font-extrabold">Payment history</h2><p class="text-xs text-slate-500">Latest transactions and subscription payments</p></div>
                <div class="divide-y">
                    @forelse($owner->payments->sortByDesc('created_at')->take(10) as $payment)
                        <div class="flex items-center justify-between gap-3 px-5 py-4">
                            <div><strong class="text-sm">{{ ucfirst($payment->type) }}</strong><p class="text-xs text-slate-400">{{ $payment->created_at->format('d M Y, h:i A') }}</p></div>
                            <div class="text-right"><strong class="text-sm">&#8377;{{ number_format($payment->amount,2) }}</strong><p class="text-[10px] font-bold {{ $payment->status==='completed'?'text-emerald-600':'text-amber-600' }}">{{ ucfirst($payment->status) }}</p></div>
                        </div>
                    @empty
                        <p class="p-10 text-center text-sm text-slate-500">No payment activity.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            @include('admin.members.partials.direct-message-card', ['targetUser' => $owner])

            <form method="POST" action="{{ route('admin.members.notes',$owner) }}" class="rounded-2xl border bg-white p-5 shadow-sm">

                @csrf @method('PUT')
                <div class="flex items-center gap-3">
                    <div class="admin-theme-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas fa-user-check"></i></div>
                    <div><h2 class="text-sm font-extrabold">KYC & internal notes</h2><p class="text-[10px] text-slate-500">Only administrators can see these notes</p></div>
                </div>
                <label class="mt-5 block text-xs font-bold text-slate-700">Verification status</label>
                <select name="verification_status" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">
                    @foreach(['pending','under_review','verified','rejected'] as $v)
                        <option value="{{ $v }}" @selected($owner->verification_status===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
                <div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-500"><i class="fas fa-clock mr-1"></i>Last verified: <strong class="text-slate-700">{{ $owner->verified_at?->format('d M Y, h:i A') ?? 'Never' }}</strong></div>
                <label class="mt-5 block text-xs font-bold text-slate-700">Internal notes</label>
                <textarea name="admin_notes" rows="7" class="mt-2 w-full rounded-xl border-slate-200 text-sm" placeholder="KYC checks, follow-up notes or risk information...">{{ $owner->admin_notes }}</textarea>
                <button class="admin-theme-bg mt-4 w-full rounded-xl py-3 text-sm font-bold shadow-sm"><i class="fas fa-save mr-2"></i>Save owner review</button>
            </form>

            @if($owner->block_reason)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5"><div class="flex items-center gap-2 text-sm font-extrabold text-red-700"><i class="fas fa-ban"></i>Block reason</div><p class="mt-2 text-sm leading-6 text-red-700">{{ $owner->block_reason }}</p></div>
            @endif

            <div class="rounded-2xl border bg-white p-5 text-xs text-slate-500 shadow-sm">
                <h3 class="font-extrabold text-slate-800">Account timeline</h3>
                <div class="mt-3 space-y-3">
                    <p><i class="fas fa-calendar mr-2 w-4 text-slate-400"></i>Joined {{ $owner->created_at->format('d M Y') }}</p>
                    <p><i class="fas fa-envelope mr-2 w-4 text-slate-400"></i>{{ $owner->email_verified_at?'Email verified':'Email not verified' }}</p>
                    <p><i class="fas fa-history mr-2 w-4 text-slate-400"></i>{{ $owner->adminActivities->count() }} recorded admin actions</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
