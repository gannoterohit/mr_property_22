@extends('layouts.auth')
@section('title', 'Create Account | ApnaNest')
@section('description', 'Create an ApnaNest account to find a room or list your property and connect directly.')

@section('content')
@php
    try {
        $publishedCmsSlugs = \App\Models\CmsPage::published()->pluck('slug')->flip();
    } catch (\Throwable $exception) {
        $publishedCmsSlugs = collect();
    }
    $termsLive = $publishedCmsSlugs->has('terms-and-conditions');
    $privacyLive = $publishedCmsSlugs->has('privacy-policy');
@endphp
<section class="auth-page">
    <div class="auth-shell auth-shell-register">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">Apna<span>Nest</span></a>
            <span class="auth-kicker"><i class="fas fa-home"></i> Built for renters and owners</span>
            <h1>Your next room or tenant starts here.</h1>
            <p>Create one secure account, choose how you want to use ApnaNest and connect without unnecessary middlemen.</p>
            <div class="auth-benefits">
                <div><i class="fas fa-check"></i><span><strong>Browse verified listings</strong><small>Compare rooms, locations and monthly rent</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>List and manage properties</strong><small>Owners control listings from one workspace</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>Secure email verification</strong><small>No password is stored or required</small></span></div>
            </div>
        </aside>

        <div class="auth-panel">
            <div class="auth-panel-head"><span class="auth-step">Create your account</span><h2>Join ApnaNest</h2><p>Tell us a little about yourself. We will verify your email before creating the account.</p></div>
            <div id="status-message" class="auth-alert hidden" role="alert"><i></i><span></span></div>

            <div id="details-step">
                <form id="details-form" novalidate>@csrf
                    <div class="auth-grid">
                        <div class="auth-field-group"><label class="auth-label" for="name">Full name</label><input class="auth-field" type="text" id="name" name="name" autocomplete="name" required placeholder="Your full name"></div>
                        <div class="auth-field-group"><label class="auth-label" for="phone">Phone number <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="tel" id="phone" name="phone" autocomplete="tel" placeholder="+91 98765 43210"></div>
                    </div>
                    <div class="auth-field-group"><label class="auth-label" for="email">Email address</label><div class="auth-input-wrap"><i class="far fa-envelope"></i><input type="email" id="email" name="email" autocomplete="email" required placeholder="name@example.com"></div></div>
                    <div class="auth-field-group"><label class="auth-label">How will you use ApnaNest?</label><div class="role-options">
                        <label class="role-card"><input type="radio" name="role" value="user" {{ request('role') !== 'owner' ? 'checked' : '' }}><span><i class="fas fa-search"></i><span><b>Find a room</b><small>Browse and contact owners</small></span></span></label>
                        <label class="role-card"><input type="radio" name="role" value="owner" {{ request('role') === 'owner' ? 'checked' : '' }}><span><i class="fas fa-building"></i><span><b>List a property</b><small>Manage rooms and enquiries</small></span></span></label>
                    </div></div>
                    <div class="auth-field-group"><label class="auth-label" for="referral_code_input">Referral code <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="text" id="referral_code_input" name="referral_code" autocomplete="off" value="{{ old('referral_code', session('referral_code')) }}" placeholder="Enter referral code"></div>
                    <label class="terms-row">
                        <input type="checkbox" id="terms_checkbox" required>
                        <span>
                            I agree to the
                            @if($termsLive)<a href="{{ route('pages.terms') }}" target="_blank">Terms and Conditions</a>@else<span>Terms and Conditions</span>@endif
                            and acknowledge the
                            @if($privacyLive)<a href="{{ route('pages.privacy') }}" target="_blank">Privacy Policy</a>@else<span>Privacy Policy</span>@endif.
                        </span>
                    </label>
                    <button type="submit" id="send-otp-btn" class="auth-primary"><span>Continue to email verification</span><i class="fas fa-arrow-right"></i></button>
                </form>
            </div>

            <div id="otp-step" class="hidden">
                <div class="otp-heading"><div class="otp-icon"><i class="fas fa-envelope-open-text"></i></div><h3>Verify your email</h3><p>Enter the code sent to <strong id="email-display"></strong></p></div>
                <form id="otp-form" novalidate>@csrf
                    <label class="auth-label" for="otp">Verification code</label><input class="otp-input" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" type="text" id="otp" name="otp" maxlength="6" required placeholder="000000">
                    <div class="otp-actions"><span>Expires in 10 minutes</span><button type="button" id="resend-otp-btn">Resend code</button></div>
                    <button type="submit" id="verify-otp-btn" class="auth-primary"><span>Verify and create account</span><i class="fas fa-arrow-right"></i></button>
                    <button type="button" id="back-to-details-btn" class="auth-secondary"><i class="fas fa-arrow-left"></i>Edit account details</button>
                </form>
            </div>
            <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Login instead</a></p>
        </div>
    </div>
</section>
@include('auth.partials.auth-styles')
@include('auth.partials.otp-script', ['mode' => 'register'])
@endsection
