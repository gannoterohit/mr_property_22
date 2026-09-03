@extends('layouts.auth')

@section('title', 'Admin Login | ApnaNest')
@section('description', 'Secure admin and staff login for ApnaNest.')

@section('content')
<section class="auth-page">
    <div class="auth-shell auth-shell-login">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">Apna<span>Nest</span></a>
            <span class="auth-kicker"><i class="fas fa-user-shield"></i> Admin access</span>
            <h1>Separate control room for admins and staff.</h1>
            <p>Use your admin email and password to manage listings, users, payments, support and operating cities.</p>
            <div class="auth-benefits">
                <div><i class="fas fa-check"></i><span><strong>Password login</strong><small>Admin and staff accounts do not use public OTP login</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>Role protected</strong><small>Only active admin accounts can enter the panel</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>Staff ready</strong><small>Team members login here with assigned permissions</small></span></div>
            </div>
        </aside>

        <div class="auth-panel">
            <div class="auth-panel-head">
                <span class="auth-step">Admin sign in</span>
                <h2>Login to admin panel</h2>
                <p>Enter your admin or staff credentials. User and owner accounts should continue using OTP login.</p>
            </div>

            @if(session('status'))
                <div class="auth-alert success"><i class="fas fa-check-circle"></i><span>{{ session('status') }}</span></div>
            @endif
            @if(isset($errors) && $errors->any())
                <div class="auth-alert error"><i class="fas fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}">
                @csrf
                <label class="auth-label" for="email">Admin email</label>
                <div class="auth-input-wrap">
                    <i class="far fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required placeholder="admin@example.com">
                </div>

                <label class="auth-label mt-4" for="password">Password</label>
                <div class="auth-input-wrap">
                    <i class="fas fa-key"></i>
                    <input type="password" id="password" name="password" autocomplete="current-password" required placeholder="Enter password">
                </div>

                <label class="mt-4 flex items-center gap-2 text-xs font-bold text-slate-600">
                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600">
                    Remember this admin session
                </label>

                <button type="submit" class="auth-primary"><span>Login to admin panel</span><i class="fas fa-arrow-right"></i></button>
            </form>

            <p class="auth-switch">User or owner? <a href="{{ route('login') }}">Use OTP login</a></p>
        </div>
    </div>
</section>
@include('auth.partials.auth-styles')
@endsection
