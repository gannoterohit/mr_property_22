@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'Profile Settings - ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@push('styles')
<link rel="stylesheet" href="{{ asset('css/account-profile-edit.css') }}">
@endpush
@php $contentSection = $user->role === 'owner' ? 'owner-content' : ($user->role === 'broker' ? 'broker-content' : ($user->role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    @if(session('status') === 'profile-updated')
        <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800"><i class="fas fa-circle-check"></i>Profile updated successfully.</div>
    @endif

    <div class="profile-layout">
        <div class="profile-stack">
            <section class="profile-card">
                <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fas fa-user"></i></span>
                    <div><h2 class="font-bold text-slate-950">Personal Information</h2><p class="text-xs text-slate-500">Update your photo, name and email address.</p></div>
                </div>
                @include('account.profile.partials.update-profile-information-form')
            </section>

            <section class="profile-card">
                <div class="mb-6 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600"><i class="fas fa-lock"></i></span>
                    <div><h2 class="font-bold text-slate-950">Password & Security</h2><p class="text-xs text-slate-500">Use a strong password to protect your account.</p></div>
                </div>
                @include('account.profile.partials.update-password-form')
            </section>
        </div>

        <aside class="profile-stack">
            <section class="profile-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Account overview</p>
                <div class="mt-4 flex items-center gap-4">
                    <img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('assets/images/default-avatar.svg') }}" width="200" height="200" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'" alt="{{ $user->name }} profile" class="h-14 w-14 rounded-full object-cover ring-4 ring-slate-50 bg-indigo-50">
                    <div class="min-w-0"><h2 class="truncate font-bold text-slate-950">{{ $user->name }}</h2><p class="truncate text-xs text-slate-500">{{ $user->email }}</p><span class="mt-1 inline-block text-[10px] font-bold uppercase text-indigo-600">{{ $user->role }}</span></div>
                </div>
                <div class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                    <div class="flex items-center justify-between gap-3"><span class="text-slate-500">Email status</span><span class="font-bold {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $user->email_verified_at ? 'Verified' : 'Not verified' }}</span></div>
                    <div class="flex items-center justify-between gap-3"><span class="text-slate-500">Account type</span><span class="font-bold text-slate-800">{{ ucfirst($user->role) }}</span></div>
                </div>
            </section>

            <section class="profile-card profile-danger">
                <div class="mb-4 flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"><i class="fas fa-triangle-exclamation"></i></span><div><h2 class="font-bold text-slate-950">Danger Zone</h2><p class="text-xs text-slate-500">Permanent account actions.</p></div></div>
                @include('account.profile.partials.delete-user-form')
            </section>
        </aside>
    </div>
</div>
@endsection
