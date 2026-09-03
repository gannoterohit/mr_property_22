@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'Profile Settings - ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@push('styles')
<link rel="stylesheet" href="{{ asset('css/account-profile-edit.css') }}">
@endpush
@php $contentSection = $user->role === 'owner' ? 'owner-content' : ($user->role === 'broker' ? 'broker-content' : ($user->role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6">
    <!-- Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="w-10 h-10 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition shadow-sm shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Profile Settings</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-500 mt-0.5">Manage your personal information, security password and account settings.</p>
            </div>
        </div>
    </div>

    @if(session('status') === 'profile-updated')
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-3.5 text-sm font-bold text-emerald-800 shadow-sm">
            <i class="fas fa-circle-check text-emerald-600 text-lg"></i>
            <span>Profile updated successfully.</span>
        </div>
    @endif

    <div class="profile-layout">
        <div class="profile-stack">
            <section class="profile-card space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 text-base font-bold shrink-0">
                        <i class="fas fa-user"></i>
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Personal Information</h2>
                        <p class="text-xs text-slate-500">Update your photo, full name and email address.</p>
                    </div>
                </div>
                @include('account.profile.partials.update-profile-information-form')
            </section>

            <section class="profile-card space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600 text-base font-bold shrink-0">
                        <i class="fas fa-lock"></i>
                    </span>
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Password & Security</h2>
                        <p class="text-xs text-slate-500">Use a strong password to keep your account secure.</p>
                    </div>
                </div>
                @include('account.profile.partials.update-password-form')
            </section>
        </div>

        <aside class="profile-stack">
            <section class="profile-card">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Account Overview</p>
                <div class="mt-4 flex items-center gap-4">
                    @if($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" width="200" height="200" alt="{{ $user->name }} profile" class="h-14 w-14 rounded-2xl object-cover ring-2 ring-indigo-100 bg-indigo-50 shadow-sm shrink-0">@else<div class="h-14 w-14 rounded-2xl ring-2 ring-indigo-100 bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">{{ $user->name }} profile</span></div>@endif
                    <div class="min-w-0">
                        <h2 class="truncate font-extrabold text-slate-900 text-base">{{ $user->name }}</h2>
                        <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        <span class="mt-1 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-[10px] font-bold uppercase tracking-wider">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
                <div class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-xs sm:text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-slate-500 font-medium">Email Status</span>
                        <span class="font-bold {{ $user->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                        <span class="text-slate-500 font-medium">Account Role</span>
                        <span class="font-bold text-slate-800">{{ ucfirst($user->role) }}</span>
                    </div>
                    @if($user->role === 'owner' || $user->role === 'broker')
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <span class="text-slate-500 font-medium">KYC Verification</span>
                            @php
                                $status = $user->role === 'broker' ? ($user->broker_verification_status ?? 'pending') : ($user->verification_status ?? 'pending');
                            @endphp
                            @if($status === 'approved')
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                    <i class="fas fa-circle-check text-[10px]"></i> Verified
                                </span>
                            @elseif($status === 'rejected')
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-bold text-rose-700">
                                    <i class="fas fa-circle-xmark text-[10px]"></i> Rejected
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700">
                                    <i class="fas fa-clock text-[10px]"></i> Pending Review
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            <section class="profile-card profile-danger">
                <div class="mb-4 flex items-center gap-3 border-b border-red-100 pb-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-red-600 text-base font-bold shrink-0">
                        <i class="fas fa-triangle-exclamation"></i>
                    </span>
                    <div>
                        <h2 class="font-extrabold text-slate-900 text-base">Danger Zone</h2>
                        <p class="text-xs text-slate-500">Permanent account actions.</p>
                    </div>
                </div>
                @include('account.profile.partials.delete-user-form')
            </section>
        </aside>
    </div>
</div>
@endsection
