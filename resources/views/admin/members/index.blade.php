@extends('layouts.admin')

@section('title', 'Member 360')

@push('styles')
<style>
    .member-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
    .member-search-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center}
    #memberSearchInput{padding-left:2.85rem!important;padding-right:1rem!important;line-height:1.25rem!important}
    #memberSearchButton{min-width:170px;white-space:nowrap}
    .history-panel{display:none}.history-panel.active{display:block}
    .member-search-panel{border:1px solid #e2e8f0;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.04)}
    .member-search-button{background:#0f172a;color:#fff}.member-search-button:hover{background:#1e293b}
    @media(max-width:1199px){.member-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(max-width:639px){.member-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.member-search-row{grid-template-columns:1fr}#memberSearchButton{width:100%;min-width:0}}
</style>
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header>
        <p class="admin-theme-text text-[10px] font-extrabold uppercase tracking-[.2em]">People intelligence</p>
        <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Member 360 Search</h1>
        <p class="mt-1 text-sm text-slate-500">Search any user or owner and review their complete available account history.</p>
    </header>

    @include('admin.members.nav')

    <form method="GET" action="{{ route('admin.members.index') }}" class="member-search-panel rounded-2xl p-4 sm:p-6">
        <label class="text-xs font-extrabold text-slate-700">Search by name, email, phone, member ID or referral code</label>
        <div class="member-search-row mt-2">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-11 items-center justify-center text-slate-400"><i class="fas fa-magnifying-glass text-sm"></i></span>
                <input id="memberSearchInput" name="q" value="{{ $term }}" autofocus autocomplete="off" placeholder="Name, email, phone, member ID or referral code" class="h-12 w-full rounded-xl border-slate-200 bg-white text-sm shadow-sm">
            </div>
            <button id="memberSearchButton" class="member-search-button inline-flex h-12 items-center justify-center gap-2 rounded-xl px-6 text-sm font-extrabold transition"><i class="fas fa-search"></i><span>Search Member</span></button>
        </div>
    </form>

    @if($term !== '' && !$member)
        <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
            <div class="border-b px-5 py-4">
                <h2 class="text-sm font-extrabold">Search results</h2>
                <p class="text-xs text-slate-500">{{ $matches->count() }} matching accounts for "{{ $term }}"</p>
            </div>
            <div class="divide-y">
                @forelse($matches as $result)
                    <a href="{{ route('admin.members.index',['q'=>$term,'member_id'=>$result->id]) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 transition hover:bg-slate-50">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="admin-theme-soft flex h-11 w-11 shrink-0 items-center justify-center rounded-xl font-extrabold">{{ strtoupper(substr($result->name,0,1)) }}</span>
                            <div class="min-w-0"><p class="truncate text-sm font-extrabold text-slate-900">{{ $result->name }}</p><p class="truncate text-xs text-slate-500">#{{ $result->id }} - {{ $result->email }} - {{ $result->phone ?: 'No phone' }}</p></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600">{{ $result->role }}</span>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $result->trashed() || $result->is_blocked ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $result->trashed() ? 'Deleted' : ($result->is_blocked ? 'Blocked' : 'Active') }}</span>
                            <i class="fas fa-chevron-right text-xs text-slate-300"></i>
                        </div>
                    </a>
                @empty
                    <div class="py-14 text-center"><i class="fas fa-user-slash text-3xl text-slate-300"></i><p class="mt-3 text-sm font-bold text-slate-600">No matching member found</p><p class="mt-1 text-xs text-slate-400">Try another email, phone or member ID.</p></div>
                @endforelse
            </div>
        </section>
    @endif

    @if($member)
        @php
            $isDeleted = $member->trashed();
            $detailRoute = $member->role === 'owner' ? 'admin.owners.detail' : 'admin.users.detail';
            $editRoute = $member->role === 'owner' ? 'admin.owners.edit' : 'admin.users.edit';
            $toggleRoute = $member->role === 'owner' ? 'admin.owners.toggleBlock' : 'admin.users.toggleBlock';
            $destroyRoute = $member->role === 'owner' ? 'admin.owners.destroy' : 'admin.users.destroy';
        @endphp

        <section class="rounded-2xl border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex min-w-0 items-center gap-4">
                    <span class="admin-theme-bg flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-extrabold">{{ strtoupper(substr($member->name,0,1)) }}</span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="text-xl font-extrabold text-slate-950">{{ $member->name }}</h2><span class="admin-theme-soft rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase">{{ $member->role }}</span></div>
                        <p class="mt-1 text-xs text-slate-500">Member #{{ $member->id }} - {{ $member->email }} - {{ $member->phone ?: 'No phone' }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $isDeleted || $member->is_blocked ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $isDeleted ? 'Deleted' : ($member->is_blocked ? 'Blocked' : 'Active') }}</span>
                            <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700">KYC: {{ ucfirst(str_replace('_',' ',$member->verification_status)) }}</span>
                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700">Joined {{ $member->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($isDeleted)
                        <form method="POST" action="{{ route('admin.members.restore',$member->id) }}">@csrf<button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white"><i class="fas fa-rotate-left mr-1"></i>Restore</button></form>
                    @else
                        <a href="{{ route($editRoute,$member) }}" class="rounded-xl border bg-white px-4 py-2.5 text-xs font-bold text-slate-700"><i class="fas fa-pen mr-1"></i>Edit account</a>
                        <form method="POST" action="{{ route($toggleRoute,$member) }}">@csrf<input type="hidden" name="block_reason" value="Blocked from Member 360 by administrator"><button class="rounded-xl px-4 py-2.5 text-xs font-bold {{ $member->is_blocked ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $member->is_blocked ? 'Unblock' : 'Block' }}</button></form>
                        <form method="POST" action="{{ route($destroyRoute,$member) }}" class="admin-confirm" data-confirm-title="Delete {{ $member->name }}?" data-confirm-text="This account can be restored later." data-confirm-button="Yes, delete account">@csrf @method('DELETE')<button class="rounded-xl bg-red-50 px-4 py-2.5 text-xs font-bold text-red-700"><i class="fas fa-trash mr-1"></i>Delete</button></form>
                    @endif
                </div>
            </div>
        </section>

        <section class="member-kpis">
            @foreach([
                ['Listings',$member->rooms_count,'fa-building','admin-theme-text'],
                ['Payments',$member->payments_count,'fa-credit-card','text-emerald-600'],
                ['Subscriptions',$member->subscriptions_count,'fa-id-card','text-slate-600'],
                ['Unlocks',$member->enquiries_count,'fa-lock-open','admin-theme-text'],
                ['Complaints',$member->complaints_count,'fa-shield-halved','text-amber-600'],
                ['Referrals',$member->referrals_count,'fa-user-plus','text-pink-600'],
            ] as [$label,$value,$icon,$tone])
                <div class="rounded-2xl border bg-white p-4 shadow-sm"><div class="flex items-center justify-between"><p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p><i class="fas {{ $icon }} {{ $tone }}"></i></div><p class="mt-2 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p></div>
            @endforeach
        </section>

        <section class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <nav class="flex gap-1 overflow-x-auto border-b bg-slate-50 p-2">
                    @foreach([
                        'payments'=>'Payments','listings'=>'Listings','subscriptions'=>'Plans','enquiries'=>'Unlocks',
                        'complaints'=>'Complaints','wishlists'=>'Wishlist','alerts'=>'City Alerts',
                        'referrals'=>'Referrals','activities'=>'Admin Log'
                    ] as $key=>$label)
                        <button type="button" data-history-tab="{{ $key }}" class="history-tab min-w-max rounded-lg px-3 py-2 text-[11px] font-bold {{ $loop->first ? 'admin-theme-bg' : 'text-slate-600 hover:bg-white' }}">{{ $label }}</button>
                    @endforeach
                </nav>

                @php
                    $panels = [
                        'payments' => $history['payments'], 'listings' => $history['rooms'], 'subscriptions' => $history['subscriptions'],
                        'enquiries' => $history['enquiries'], 'complaints' => $history['complaints'],
                        'wishlists' => $history['wishlists'], 'alerts' => $history['city_alerts'], 'referrals' => $history['referrals'],
                        'activities' => $history['activities'],
                    ];
                @endphp
                @foreach($panels as $panelKey=>$records)
                    <div data-history-panel="{{ $panelKey }}" class="history-panel {{ $loop->first ? 'active' : '' }}">
                        <div class="flex items-center justify-between border-b px-5 py-3"><p class="text-xs font-extrabold">{{ ucfirst($panelKey) }} history</p><span class="text-[10px] text-slate-400">Latest {{ $records->count() }} records</span></div>
                        <div class="divide-y">
                            @forelse($records as $record)
                                <div class="flex items-center justify-between gap-4 px-5 py-4">
                                    <div class="min-w-0">
                                        @switch($panelKey)
                                            @case('payments') <p class="truncate text-sm font-bold">{{ ucfirst($record->type) }} - &#8377;{{ number_format($record->amount,2) }}</p><p class="text-xs text-slate-400">{{ $record->gateway ?: 'Manual' }} - {{ $record->transaction_id ?: $record->reference_id ?: 'No reference' }}</p> @break
                                            @case('listings') <p class="truncate text-sm font-bold">{{ $record->title }}</p><p class="text-xs text-slate-400">{{ $record->city }} - &#8377;{{ number_format($record->rent) }}/month</p> @break
                                            @case('subscriptions') <p class="truncate text-sm font-bold">{{ $record->plan?->name ?? 'Deleted plan' }}</p><p class="text-xs text-slate-400">{{ $record->start_date?->format('d M Y') }} to {{ $record->end_date?->format('d M Y') }}</p> @break
                                            @case('enquiries') <p class="truncate text-sm font-bold">{{ $record->room?->title ?? 'Deleted room' }}</p><p class="text-xs text-slate-400">{{ $record->unlocked ? 'Contact unlocked' : 'Pending unlock' }}</p> @break
                                            @case('complaints') <p class="truncate text-sm font-bold">{{ $record->ticket_number }} - {{ $record->subject }}</p><p class="text-xs text-slate-400">{{ ucfirst(str_replace('_',' ',$record->category)) }} - {{ $record->against_user_id === $member->id ? 'Against member' : 'Raised by member' }}</p> @break
                                            @case('wishlists') <p class="truncate text-sm font-bold">{{ $record->room?->title ?? 'Deleted room' }}</p><p class="text-xs text-slate-400">{{ $record->room?->city ?? 'Location unavailable' }}</p> @break
                                            @case('alerts') <p class="truncate text-sm font-bold">{{ $record->city }}</p><p class="text-xs text-slate-400">City availability alert</p> @break
                                            @case('referrals') <p class="truncate text-sm font-bold">{{ $record->name }}</p><p class="text-xs text-slate-400">{{ $record->email }} - {{ ucfirst($record->role) }}</p> @break
                                            @case('activities') <p class="truncate text-sm font-bold">{{ $record->description ?: $record->action }}</p><p class="text-xs text-slate-400">By {{ $record->actor?->name ?? 'System' }}</p> @break
                                        @endswitch
                                    </div>
                                    <div class="shrink-0 text-right"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ ucfirst(str_replace('_',' ',$record->status ?? $record->listing_status ?? 'recorded')) }}</span><p class="mt-1 text-[10px] text-slate-400">{{ $record->created_at?->format('d M Y, h:i A') }}</p></div>
                                </div>
                            @empty
                                <div class="py-14 text-center"><i class="fas fa-box-open text-2xl text-slate-300"></i><p class="mt-2 text-xs font-bold text-slate-500">No {{ strtolower($panelKey) }} history found.</p></div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-extrabold">Account snapshot</h3>
                    <div class="mt-4 space-y-3 text-xs text-slate-500">
                        <p class="flex justify-between gap-3"><span>Wallet balance</span><strong class="text-slate-800">&#8377;{{ number_format($member->wallet_balance,2) }}</strong></p>
                        <p class="flex justify-between gap-3"><span>Free unlocks</span><strong class="text-slate-800">{{ $member->free_unlocks ?? 0 }}</strong></p>
                        <p class="flex justify-between gap-3"><span>Email</span><strong class="{{ $member->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $member->email_verified_at ? 'Verified' : 'Unverified' }}</strong></p>
                        <p class="flex justify-between gap-3"><span>Referral code</span><strong class="text-slate-800">{{ $member->referral_code ?: '-' }}</strong></p>
                        <p class="flex justify-between gap-3"><span>Last updated</span><strong class="text-slate-800">{{ $member->updated_at->format('d M Y') }}</strong></p>
                    </div>
                </section>
                <section class="rounded-2xl border bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-extrabold">Internal admin notes</h3>
                    <p class="mt-3 whitespace-pre-line text-xs leading-6 text-slate-500">{{ $member->admin_notes ?: 'No internal notes recorded.' }}</p>
                </section>
                @if($member->block_reason)<section class="rounded-2xl border border-red-200 bg-red-50 p-5"><h3 class="text-sm font-extrabold text-red-700">Block reason</h3><p class="mt-2 text-xs leading-5 text-red-600">{{ $member->block_reason }}</p></section>@endif
            </aside>
        </section>
    @elseif($term === '')
        <section class="rounded-2xl border border-dashed bg-white py-20 text-center">
            <span class="admin-theme-soft mx-auto flex h-16 w-16 items-center justify-center rounded-2xl text-2xl"><i class="fas fa-user-magnifying-glass"></i></span>
            <h2 class="mt-4 text-lg font-extrabold text-slate-800">Search a member to begin</h2>
            <p class="mt-1 text-sm text-slate-500">Their complete available activity will appear here.</p>
        </section>
    @endif
</div>

<script>
document.querySelectorAll('[data-history-tab]').forEach(function(button){
    button.addEventListener('click',function(){
        document.querySelectorAll('[data-history-tab]').forEach(function(item){item.className='history-tab min-w-max rounded-lg px-3 py-2 text-[11px] font-bold text-slate-600 hover:bg-white';});
        document.querySelectorAll('[data-history-panel]').forEach(function(panel){panel.classList.remove('active');});
        button.className='history-tab admin-theme-bg min-w-max rounded-lg px-3 py-2 text-[11px] font-bold';
        document.querySelector('[data-history-panel="'+button.dataset.historyTab+'"]')?.classList.add('active');
    });
});
</script>
@endsection
