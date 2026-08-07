@extends('layouts.admin')

@section('title','User Details')

@section('admin-content')
<div class="px-5 pt-5 lg:px-6 lg:pt-6">@include('admin.members.nav')</div>

<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.users') }}" class="admin-theme-text text-xs font-bold"><i class="fas fa-arrow-left mr-1"></i>All users</a>
            <div class="mt-3 flex items-center gap-3">
                <div class="admin-theme-bg flex h-12 w-12 items-center justify-center rounded-2xl text-lg font-extrabold">{{ strtoupper(substr($user->name,0,1)) }}</div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-950">{{ $user->name }}</h1>
                    <p class="text-sm text-slate-500">{{ $user->email }} - {{ $user->phone ?: 'No phone number' }}</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full px-3 py-2 text-xs font-bold {{ $user->is_blocked?'bg-red-50 text-red-700':'bg-emerald-50 text-emerald-700' }}">{{ $user->is_blocked?'Blocked account':'Active account' }}</span>
            <button onclick="document.getElementById('direct-msg-card').scrollIntoView({behavior:'smooth'})" class="rounded-xl border bg-indigo-50 px-4 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"><i class="fas fa-paper-plane mr-1"></i>Send Message</button>
            <a href="{{ route('admin.users.edit',$user) }}" class="rounded-xl border bg-white px-4 py-2 text-xs font-bold text-slate-700"><i class="fas fa-pen mr-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.users.destroy',$user) }}" class="admin-confirm" data-confirm-title="Delete {{ $user->name }}?" data-confirm-text="The account will be removed but can be restored later." data-confirm-button="Yes, delete account">
                @csrf @method('DELETE')
                <button class="rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-700"><i class="fas fa-trash mr-1"></i>Delete</button>
            </form>
        </div>

    </header>

    <section class="admin-detail-stats">
        @foreach([
            ['Payments',$user->payments->count(),'fa-credit-card','text-emerald-600'],
            ['Subscriptions',$user->subscriptions->count(),'fa-id-badge','text-slate-600'],
            ['Contact unlocks',$user->enquiries->where('unlocked',true)->count(),'fa-lock-open','admin-theme-text'],
            ['Complaints',$user->complaints->count(),'fa-shield-halved','text-amber-600'],
        ] as [$label,$value,$icon,$tone])
            <div class="flex items-center gap-4 rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-50 {{ $tone }}"><i class="fas {{ $icon }}"></i></div>
                <div><p class="text-[10px] font-bold uppercase text-slate-400">{{ $label }}</p><p class="text-2xl font-extrabold">{{ $value }}</p></div>
            </div>
        @endforeach
    </section>

    <div class="admin-detail-workspace">
        <main class="min-w-0 space-y-5">
            <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-5 py-4">
                    <h2 class="text-sm font-extrabold">Payment & subscription history</h2>
                    <p class="text-xs text-slate-500">Latest account transactions</p>
                </div>
                <div class="divide-y">
                    @forelse($user->payments->sortByDesc('created_at')->take(10) as $payment)
                        <div class="flex justify-between gap-3 px-5 py-4">
                            <div><strong class="text-sm">{{ ucfirst($payment->type) }}</strong><p class="text-xs text-slate-400">{{ $payment->created_at->format('d M Y, h:i A') }}</p></div>
                            <div class="text-right"><strong class="text-sm">&#8377;{{ number_format($payment->amount,2) }}</strong><p class="text-[10px] font-bold {{ $payment->status==='completed'?'text-emerald-600':'text-amber-600' }}">{{ ucfirst($payment->status) }}</p></div>
                        </div>
                    @empty
                        <p class="p-10 text-center text-sm text-slate-500">No payments found.</p>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
                <div class="border-b px-5 py-4">
                    <h2 class="text-sm font-extrabold">Contact unlock history</h2>
                    <p class="text-xs text-slate-500">Properties this user enquired about</p>
                </div>
                <div class="divide-y">
                    @forelse($user->enquiries->sortByDesc('created_at')->take(10) as $enquiry)
                        <div class="flex items-center justify-between gap-3 px-5 py-4">
                            <div><strong class="text-sm">{{ $enquiry->room?->title ?? 'Deleted property' }}</strong><p class="text-xs text-slate-400">{{ $enquiry->created_at->format('d M Y, h:i A') }}</p></div>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $enquiry->unlocked?'bg-emerald-50 text-emerald-700':'bg-amber-50 text-amber-700' }}">{{ $enquiry->unlocked?'Unlocked':'Pending' }}</span>
                        </div>
                    @empty
                        <p class="p-10 text-center text-sm text-slate-500">No contact unlock activity.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="space-y-4">
            @include('admin.members.partials.direct-message-card', ['targetUser' => $user])

            <form method="POST" action="{{ route('admin.members.notes',$user) }}" class="rounded-2xl border bg-white p-5 shadow-sm">

                @csrf @method('PUT')
                <div class="flex items-center gap-3">
                    <div class="admin-theme-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas fa-user-gear"></i></div>
                    <div><h2 class="text-sm font-extrabold">Account management</h2><p class="text-[10px] text-slate-500">Internal administrative information</p></div>
                </div>
                <label class="mt-5 block text-xs font-bold">Verification status</label>
                <select name="verification_status" class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm">
                    @foreach(['pending','under_review','verified','rejected'] as $v)
                        <option value="{{ $v }}" @selected($user->verification_status===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>
                    @endforeach
                </select>
                <label class="mt-5 block text-xs font-bold">Internal notes</label>
                <textarea name="admin_notes" rows="8" class="mt-2 w-full rounded-xl border-slate-200 text-sm" placeholder="Account checks, support notes or risk information...">{{ $user->admin_notes }}</textarea>
                <button class="admin-theme-bg mt-4 w-full rounded-xl py-3 text-sm font-bold"><i class="fas fa-save mr-2"></i>Save account review</button>
            </form>

            @if($user->block_reason)
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5"><h3 class="text-sm font-extrabold text-red-700"><i class="fas fa-ban mr-2"></i>Block reason</h3><p class="mt-2 text-sm text-red-700">{{ $user->block_reason }}</p></div>
            @endif

            <div class="rounded-2xl border bg-white p-5 text-xs text-slate-500 shadow-sm">
                <h3 class="font-extrabold text-slate-800">Account timeline</h3>
                <div class="mt-3 space-y-3">
                    <p><i class="fas fa-calendar mr-2 w-4 text-slate-400"></i>Joined {{ $user->created_at->format('d M Y') }}</p>
                    <p><i class="fas fa-envelope mr-2 w-4 text-slate-400"></i>{{ $user->email_verified_at?'Email verified':'Email not verified' }}</p>
                    <p><i class="fas fa-history mr-2 w-4 text-slate-400"></i>{{ $user->adminActivities->count() }} recorded admin actions</p>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
