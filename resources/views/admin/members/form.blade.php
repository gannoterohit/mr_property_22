@extends('layouts.admin')

@php
    $editing = $member->exists;
    $isOwner = $memberRole === 'owner';
    $label = $isOwner ? 'Owner' : 'User';
    $indexRoute = $isOwner ? route('admin.owners') : route('admin.users');
    $action = $editing
        ? ($isOwner ? route('admin.owners.update', $member) : route('admin.users.update', $member))
        : ($isOwner ? route('admin.owners.store') : route('admin.users.store'));
@endphp

@section('title', ($editing ? 'Edit ' : 'Add ') . $label)

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-members-form.css') }}">
@endpush

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ $indexRoute }}" class="text-xs font-bold admin-theme-text"><i class="fas fa-arrow-left mr-1"></i>{{ $label }} directory</a>
            <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">People management</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-950">{{ $editing ? 'Edit' : 'Add new' }} {{ strtolower($label) }}</h1>
            <p class="mt-1 text-sm text-slate-500">Manage identity, access, verification and account benefits.</p>
        </div>
        @if($editing)
            <x-admin.action-icon variant="view" :href="$isOwner ? route('admin.owners.detail',$member) : route('admin.users.detail',$member)" title="View profile" />
        @endif
    </header>

    @include('admin.members.nav')

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-bold text-red-700"><i class="fas fa-triangle-exclamation mr-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $action }}" class="member-form-grid">
        @csrf
        @if($editing) @method('PUT') @endif

        <main class="space-y-4">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 border-b pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-address-card"></i></span>
                    <div><h2 class="text-sm font-extrabold">Personal information</h2><p class="text-xs text-slate-500">Primary contact and login identity.</p></div>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><label class="text-xs font-bold text-slate-700">Full name *</label><input name="name" value="{{ old('name',$member->name) }}" required class="member-field mt-1.5" placeholder="Full name"></div>
                    <div><label class="text-xs font-bold text-slate-700">Phone number</label><input name="phone" value="{{ old('phone',$member->phone) }}" class="member-field mt-1.5" placeholder="+91 98765 43210"></div>
                    <div class="sm:col-span-2"><label class="text-xs font-bold text-slate-700">Email address *</label><input type="email" name="email" value="{{ old('email',$member->email) }}" required class="member-field mt-1.5" placeholder="name@example.com"></div>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 border-b pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl admin-theme-soft"><i class="fas fa-key"></i></span>
                    <div><h2 class="text-sm font-extrabold">Passwordless login</h2><p class="text-xs text-slate-500">No password is required for this account.</p></div>
                </div>
                <div class="mt-5 rounded-xl border border-slate-200 admin-theme-soft p-4">
                    <p class="text-xs font-extrabold admin-theme-text"><i class="fas fa-envelope-circle-check mr-2"></i>Email OTP authentication</p>
                    <p class="mt-1 text-xs leading-5 admin-theme-text">The {{ strtolower($label) }} will sign in with the email address above and a fresh one-time OTP.</p>
                </div>
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 border-b pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-wallet"></i></span>
                    <div><h2 class="text-sm font-extrabold">Account benefits</h2><p class="text-xs text-slate-500">Credits and complimentary contact unlocks.</p></div>
                </div>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div><label class="text-xs font-bold text-slate-700">Wallet balance (&#8377;)</label><input type="number" step="0.01" min="0" name="wallet_balance" value="{{ old('wallet_balance',$member->wallet_balance ?? 0) }}" required class="member-field mt-1.5"></div>
                    <div><label class="text-xs font-bold text-slate-700">Free unlocks</label><input type="number" min="0" name="free_unlocks" value="{{ old('free_unlocks',$member->free_unlocks ?? 0) }}" required class="member-field mt-1.5"></div>
                </div>
            </section>
        </main>

        <aside class="space-y-4 lg:sticky lg:top-24">
            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <h2 class="text-sm font-extrabold">Verification & access</h2>
                <label class="mt-4 block text-xs font-bold text-slate-700">KYC status</label>
                <select name="verification_status" class="member-field mt-1.5">
                    @foreach(['pending','under_review','verified','rejected'] as $status)
                        <option value="{{ $status }}" @selected(old('verification_status',$member->verification_status ?? ($editing ? 'pending' : 'verified')) === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                    @endforeach
                </select>
                <div class="mt-4 space-y-3">
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border p-3">
                        <div><p class="text-xs font-extrabold">Email verified</p><p class="mt-0.5 text-[10px] text-slate-500">Allow verified account features.</p></div>
                        <input type="checkbox" name="email_verified" value="1" @checked(old('email_verified',$member->email_verified_at !== null || !$editing)) class="mt-1 rounded admin-theme-text">
                    </label>
                    <label class="flex cursor-pointer items-start justify-between gap-3 rounded-xl border border-red-100 bg-red-50/40 p-3">
                        <div><p class="text-xs font-extrabold text-red-700">Block account</p><p class="mt-0.5 text-[10px] text-red-500">Prevent login and account access.</p></div>
                        <input type="checkbox" name="is_blocked" value="1" @checked(old('is_blocked',$member->is_blocked)) class="mt-1 rounded text-red-600">
                    </label>
                </div>
                <label class="mt-4 block text-xs font-bold text-slate-700">Block reason</label>
                <input name="block_reason" value="{{ old('block_reason',$member->block_reason) }}" class="member-field mt-1.5" placeholder="Only used when blocked">
            </section>

            <section class="rounded-2xl border bg-white p-5 shadow-sm">
                <label class="text-xs font-bold text-slate-700">Internal admin notes</label>
                <textarea name="admin_notes" rows="6" maxlength="5000" class="mt-2 w-full rounded-xl border-slate-200 text-sm" placeholder="Support, verification or risk notes...">{{ old('admin_notes',$member->admin_notes) }}</textarea>
            </section>

            <div class="grid grid-cols-[auto_1fr] gap-2">
                <a href="{{ $indexRoute }}" class="inline-flex h-12 items-center justify-center rounded-xl border bg-white px-4 text-xs font-extrabold text-slate-600">Cancel</a>
                <button class="inline-flex h-12 items-center justify-center gap-2 rounded-xl admin-theme-bg px-5 text-sm font-extrabold text-white shadow-sm"><i class="fas fa-floppy-disk"></i>{{ $editing ? 'Save changes' : 'Create '.$label }}</button>
            </div>
        </aside>
    </form>
</div>
@endsection
