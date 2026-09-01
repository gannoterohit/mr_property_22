@extends('layouts.auth')
@section('title', 'Create Account | ApnaNest')
@section('description', 'Create an ApnaNest account to find a room, list your property, or register as a broker.')

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
            <a href="{{ route('home') }}" class="auth-brand">
                @php $authLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo'); @endphp
                @if($authLogo)
                    <img src="{{ asset('storage/' . $authLogo) }}" alt="{{ \App\Models\Setting::get('website_name', 'ApnaNest') }}" class="auth-brand-img">
                @else
                    Apna<span>Nest</span>
                @endif
            </a>
            <span class="auth-kicker"><i class="fas fa-home"></i> Built for renters, owners and brokers</span>
            <h1>Your next room, tenant, or deal starts here.</h1>
            <p>Create one secure account. Choose how you want to use ApnaNest — find a room, list a property, or manage listings as a broker.</p>
            <div class="auth-benefits">
                <div><i class="fas fa-check"></i><span><strong>Browse verified listings</strong><small>Compare rooms, locations and monthly rent</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>List and manage properties</strong><small>Owners and brokers control listings from one workspace</small></span></div>
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
                        <label class="role-card"><input type="radio" name="role" value="user" {{ request('role') !== 'owner' && request('role') !== 'broker' ? 'checked' : '' }}><span><i class="fas fa-search"></i><span><b>Find a room</b><small>Browse and contact owners</small></span></span></label>
                        <label class="role-card"><input type="radio" name="role" value="owner" {{ request('role') === 'owner' ? 'checked' : '' }}><span><i class="fas fa-building"></i><span><b>List a property</b><small>Manage rooms and enquiries</small></span></span></label>
                        <label class="role-card"><input type="radio" name="role" value="broker" {{ request('role') === 'broker' ? 'checked' : '' }}><span><i class="fas fa-handshake"></i><span><b>Broker / Agent</b><small>List multiple properties and manage leads</small></span></span></label>
                    </div></div>
                    <div id="broker-fields" class="hidden space-y-4">
                        <div class="auth-grid">
                            <div class="auth-field-group"><label class="auth-label" for="agency_name">Agency Name <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="text" id="agency_name" name="agency_name" placeholder="Your agency name"></div>
                            <div class="auth-field-group"><label class="auth-label" for="broker_license">RERA / License No. <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="text" id="broker_license" name="broker_license" placeholder="License / RERA number"></div>
                        </div>
                        <div class="auth-field-group"><label class="auth-label" for="agency_address">Agency Address <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="text" id="agency_address" name="agency_address" placeholder="123, MG Road, Indore, MP"></div>
                        <div class="auth-field-group"><label class="auth-label" for="agency_gst">GST Number <span style="font-weight:500;color:#94a3b8">(optional)</span></label><input class="auth-field" type="text" id="agency_gst" name="agency_gst" placeholder="23AABCU9603R1ZX"></div>
                        <div style="background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.18);border-radius:12px;padding:12px 14px;font-size:.8rem;color:#94a3b8;">
                            <i class="fas fa-info-circle" style="color:#818cf8;margin-right:6px;"></i>
                            Your broker account will require <strong style="color:#cbd5e1;">admin approval</strong> before you can list properties. You'll see your status after logging in.
                        </div>
                    </div>
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

                    @php
                        $googleEnabled = \App\Models\Setting::isEnabled('google_login_enabled', false);
                        $facebookEnabled = \App\Models\Setting::isEnabled('facebook_login_enabled', false);
                    @endphp

                    @if($googleEnabled || $facebookEnabled)
                        <div class="auth-divider"><span>or sign up with</span></div>
                        <div class="auth-social">
                            @if($googleEnabled)
                                <a href="{{ route('social.redirect', 'google') }}" class="auth-social-btn auth-social-google">
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    <span>Google</span>
                                </a>
                            @endif
                            @if($facebookEnabled)
                                <a href="{{ route('social.redirect', 'facebook') }}" class="auth-social-btn auth-social-facebook">
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    <span>Facebook</span>
                                </a>
                            @endif
                        </div>
                    @endif
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
<script>
document.querySelectorAll('input[name="role"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const brokerFields = document.getElementById('broker-fields');
        if (brokerFields) {
            brokerFields.classList.toggle('hidden', this.value !== 'broker');
        }
    });
});
</script>
@endsection
