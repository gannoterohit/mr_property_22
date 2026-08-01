@extends('layouts.auth')
@section('title', 'Login | ApnaNest')
@section('description', 'Sign in securely to manage your ApnaNest account, listings, wishlist and enquiries.')

@section('content')
<section class="auth-page">
    <div class="auth-shell auth-shell-login">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">Apna<span>Nest</span></a>
            <span class="auth-kicker"><i class="fas fa-shield-alt"></i> Password-free access</span>
            <h1>Welcome back to your rental workspace.</h1>
            <p>Access saved rooms, enquiries and your property dashboard with a secure email verification code.</p>
            <div class="auth-benefits">
                <div><i class="fas fa-check"></i><span><strong>One account</strong><small>For room seekers and property owners</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>No password to remember</strong><small>A fresh 6-digit code protects every login</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>Direct connections</strong><small>Manage listings, favourites and contact unlocks</small></span></div>
            </div>
        </aside>

        <div class="auth-panel">
            <div class="auth-panel-head"><span class="auth-step">Secure sign in</span><h2>Login to ApnaNest</h2><p>Enter your registered email address to receive a verification code.</p></div>
            @if(session('status'))<div class="auth-alert success"><i class="fas fa-check-circle"></i><span>{{ session('status') }}</span></div>@endif
            <div id="status-message" class="auth-alert hidden" role="alert"><i></i><span></span></div>

            <div id="email-step">
                <form id="email-form" novalidate>@csrf
                    <label class="auth-label" for="email">Email address</label>
                    <div class="auth-input-wrap"><i class="far fa-envelope"></i><input type="email" id="email" name="email" autocomplete="email" required placeholder="name@example.com"></div>
                    <button type="submit" id="send-otp-btn" class="auth-primary"><span>Send verification code</span><i class="fas fa-arrow-right"></i></button>
                </form>
                <div class="auth-note"><i class="fas fa-lock"></i>Your code is valid for 10 minutes and can be used only once.</div>
            </div>

            <div id="otp-step" class="hidden">
                <div class="otp-heading"><div class="otp-icon"><i class="fas fa-envelope-open-text"></i></div><h3>Check your email</h3><p>We sent a 6-digit code to <strong id="email-display"></strong></p></div>
                <form id="otp-form" novalidate>@csrf
                    <label class="auth-label" for="otp">Verification code</label>
                    <input class="otp-input" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" type="text" id="otp" name="otp" maxlength="6" required placeholder="000000">
                    <div class="otp-actions"><span>Expires in 10 minutes</span><button type="button" id="resend-otp-btn">Resend code</button></div>
                    <button type="submit" id="verify-otp-btn" class="auth-primary"><span>Verify and login</span><i class="fas fa-arrow-right"></i></button>
                    <button type="button" id="back-to-email-btn" class="auth-secondary"><i class="fas fa-arrow-left"></i>Change email address</button>
                </form>
            </div>

            <p class="auth-switch">New to ApnaNest? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </div>
</section>
@include('auth.partials.auth-styles')
@include('auth.partials.otp-script', ['mode' => 'login'])
@endsection
