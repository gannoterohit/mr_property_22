@extends('layouts.admin')

@section('title','Users')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-members-users.css') }}">
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-theme-text text-[10px] font-extrabold uppercase tracking-[.2em]">People management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">Platform Users</h1>
            <p class="mt-1 text-sm text-slate-500">Manage renter accounts, access, verification and benefits.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="users" />
            <a href="{{ route('admin.users.create') }}" class="admin-theme-bg inline-flex h-11 items-center gap-2 rounded-xl px-5 text-sm font-extrabold shadow-sm">
                <i class="fas fa-user-plus"></i>Add User
            </a>
        </div>
    </header>

    @include('admin.members.nav')

    <section class="people-kpis admin-kpis">
        @foreach([
            ['Total users',$memberStats['total'],'fa-users','admin-theme-text','admin-theme-soft'],
            ['Active',$memberStats['active'],'fa-circle-check','text-emerald-600','bg-emerald-50'],
            ['Blocked',$memberStats['blocked'],'fa-ban','text-amber-600','bg-amber-50'],
            ['Deleted',$memberStats['deleted'],'fa-trash-arrow-up','text-red-600','bg-red-50'],
        ] as [$label,$value,$icon,$tone,$bg])
            <div class="rounded-2xl border bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $bg }} {{ $tone }}"><i class="fas {{ $icon }}"></i></span>
                </div>
                <p class="mt-3 text-2xl font-extrabold {{ $tone }}">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <form class="people-filter rounded-2xl border bg-white p-4 shadow-sm">
        <div class="relative">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input name="search" value="{{ request('search') }}" placeholder="Search name, email or phone..." class="h-11 w-full rounded-xl border-slate-200 pl-10 text-sm">
        </div>
        <select name="status" class="h-11 rounded-xl border-slate-200 text-sm">
            <option value="">All accounts</option>
            <option value="active" @selected(request('status')==='active')>Active</option>
            <option value="blocked" @selected(request('status')==='blocked')>Blocked</option>
            <option value="deleted" @selected(request('status')==='deleted')>Deleted</option>
        </select>
        <div class="flex gap-2">
            <button class="h-11 rounded-xl bg-slate-900 px-5 text-xs font-extrabold text-white">Apply Filters</button>
            <a href="{{ route('admin.users') }}" class="inline-flex h-11 items-center rounded-xl border px-4 text-xs font-extrabold text-slate-600">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <div>
                <h2 class="text-sm font-extrabold">User Directory</h2>
                <p class="text-xs text-slate-500">{{ $users->total() }} accounts match the current filters</p>
            </div>
            <span class="admin-theme-soft rounded-full px-3 py-1.5 text-[10px] font-extrabold">Page {{ $users->currentPage() }} / {{ max(1,$users->lastPage()) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="people-table admin-table-base">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Verification</th>
                        <th class="px-4 py-3">Benefits</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex min-w-[230px] items-center gap-3">
                                    <span class="admin-theme-soft flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold">{{ strtoupper(substr($user->name,0,1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-slate-900">{{ $user->name }}</p>
                                        <p class="truncate text-xs text-slate-400">#{{ $user->id }} - {{ $user->email }}</p>
                                        <p class="truncate text-[10px] text-slate-400">{{ $user->phone ?: 'No phone number' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $user->trashed()||$user->is_blocked?'bg-red-50 text-red-700':'bg-emerald-50 text-emerald-700' }}">
                                    <i class="fas fa-circle text-[5px]"></i>{{ $user->trashed()?'Deleted':($user->is_blocked?'Blocked':'Active') }}
                                </span>
                                @if($user->block_reason)
                                    <p class="mt-1 max-w-[140px] truncate text-[10px] text-slate-400">{{ $user->block_reason }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-700">{{ ucfirst(str_replace('_',' ',$user->verification_status)) }}</span>
                                <p class="mt-2 text-[10px] {{ $user->email_verified_at?'text-emerald-600':'text-amber-600' }}">{{ $user->email_verified_at?'Email verified':'Email unverified' }}</p>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-xs font-bold text-slate-700">&#8377;{{ number_format($user->wallet_balance,2) }}</p>
                                <p class="text-[10px] text-slate-400">{{ $user->free_unlocks ?? 0 }} free unlocks</p>
                            </td>
                            <td class="px-4 py-4 text-xs text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-4">
                                @if($user->trashed())
                                    <form method="POST" action="{{ route('admin.members.restore',$user->id) }}">@csrf<button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">Restore</button></form>
                                @else
                                    <div class="flex justify-end gap-2">
                                        <x-admin.status-toggle
                                            :active="!$user->is_blocked"
                                            active-label="Active"
                                            inactive-label="Blocked"
                                            :action="route('admin.users.toggleBlock', $user)"
                                            :data-label="$user->name"
                                            method="POST"
                                        />
                                        <a href="{{ route('admin.users.detail',$user) }}#direct-msg-card" title="Send Direct Notification / SMS" class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition"><i class="fas fa-paper-plane"></i></a>
                                        <a href="{{ route('admin.members.index',['q'=>$user->email,'member_id'=>$user->id]) }}" title="Member 360" class="admin-theme-soft rounded-lg px-3 py-2 text-xs font-bold"><i class="fas fa-chart-pie"></i></a>
                                        <x-admin.action-icon variant="view" :href="route('admin.users.detail',$user)" />
                                        <x-admin.action-icon variant="edit" :href="route('admin.users.edit',$user)" />
                                        <form method="POST" action="{{ route('admin.users.destroy',$user) }}" class="admin-confirm" data-confirm-title="Delete {{ $user->name }}?" data-confirm-text="The account will be soft deleted and can be restored from the Deleted filter." data-confirm-button="Yes, delete account">
                                            @csrf
                                            @method('DELETE')
                                            <x-admin.action-icon variant="delete" type="submit" />
                                        </form>
                                    </div>

                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-14 text-center"><i class="fas fa-users-slash text-3xl text-slate-300"></i><p class="mt-3 text-sm font-bold text-slate-600">No users found</p><p class="text-xs text-slate-400">Try changing the filters.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="border-t p-4">{{ $users->withQueryString()->links() }}</div>@endif
    </section>
</div>
@endsection
