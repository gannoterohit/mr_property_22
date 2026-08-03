@extends('layouts.auth')
@section('title', 'Login | ApnaNest')
@section('description', 'Sign in securely to manage your ApnaNest account, listings, wishlist and enquiries.')

@php
    $otpMode = \App\Models\Setting::get('otp_delivery', 'email');
@endphp

@section('content')
<section class="auth-page">
    <div class="auth-shell auth-shell-login">
        <aside class="auth-story">
            <a href="{{ route('home') }}" class="auth-brand">Apna<span>Nest</span></a>
            <span class="auth-kicker"><i class="fas fa-shield-alt"></i> Password-free access</span>
            <h1>Welcome back to your rental workspace.</h1>
            <p>Access saved rooms, enquiries and your property dashboard with a secure verification code.</p>
            <div class="auth-benefits">
                <div><i class="fas fa-check"></i><span><strong>One account</strong><small>For room seekers and property owners</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>No password to remember</strong><small>A fresh 6-digit code protects every login</small></span></div>
                <div><i class="fas fa-check"></i><span><strong>Direct connections</strong><small>Manage listings, favourites and contact unlocks</small></span></div>
            </div>
        </aside>

        <div class="auth-panel">
            <div class="auth-panel-head">
                <span class="auth-step">Secure sign in</span>
                <h2>Login to ApnaNest</h2>
                @if($otpMode === 'both')
                    <p>Enter your <strong>email</strong> or <strong>mobile number</strong> — OTP will be sent to both your email and phone.</p>
                @elseif($otpMode === 'phone')
                    <p>Enter your <strong>mobile number</strong> to receive a verification code via SMS.</p>
                @else
                    <p>Enter your registered <strong>email address</strong> to receive a verification code.</p>
                @endif
            </div>

            @if(session('status'))<div class="auth-alert success"><i class="fas fa-check-circle"></i><span>{{ session('status') }}</span></div>@endif
            <div id="status-message" class="auth-alert hidden" role="alert"><i></i><span></span></div>

            <div id="email-step">
                <form id="email-form" novalidate>@csrf

                    @if($otpMode === 'both')
                        {{-- Smart input: Email OR Phone --}}
                        <label class="auth-label" for="identifier">Email address or Mobile number</label>
                        <div class="auth-input-wrap">
                            <i class="far fa-user" id="identifier-icon"></i>
                            <input type="text" id="identifier" name="identifier"
                                   autocomplete="email tel"
                                   inputmode="email"
                                   required
                                   placeholder="email@example.com or 9876543210">
                        </div>
                        <p style="font-size:11px;color:#64748b;margin:6px 0 0;">OTP will be sent to <strong>both email & registered phone</strong> simultaneously.</p>

                    @elseif($otpMode === 'phone')
                        {{-- Phone only --}}
                        <label class="auth-label" for="identifier">Mobile number</label>
                        <div class="auth-input-wrap">
                            <i class="fas fa-mobile-screen-button"></i>
                            <input type="tel" id="identifier" name="identifier"
                                   autocomplete="tel"
                                   inputmode="numeric"
                                   required
                                   placeholder="9876543210">
                        </div>

                    @else
                        {{-- Email only (default) --}}
                        <label class="auth-label" for="email">Email address</label>
                        <div class="auth-input-wrap">
                            <i class="far fa-envelope"></i>
                            <input type="email" id="email" name="email"
                                   autocomplete="email"
                                   required
                                   placeholder="name@example.com">
                        </div>
                    @endif

                    <button type="submit" id="send-otp-btn" class="auth-primary">
                        <span>Send verification code</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
                <div class="auth-note"><i class="fas fa-lock"></i>Your code is valid for 10 minutes and can be used only once.</div>
            </div>

            <div id="otp-step" class="hidden">
                <div class="otp-heading">
                    <div class="otp-icon">
                        @if($otpMode === 'both')
                            <i class="fas fa-comment-dots"></i>
                        @elseif($otpMode === 'phone')
                            <i class="fas fa-mobile-screen-button"></i>
                        @else
                            <i class="fas fa-envelope-open-text"></i>
                        @endif
                    </div>
                    <h3>
                        @if($otpMode === 'both') OTP sent to email & phone
                        @elseif($otpMode === 'phone') Check your phone
                        @else Check your email
                        @endif
                    </h3>
                    <p id="otp-sent-desc">
                        @if($otpMode === 'both')
                            OTP sent to your email and registered mobile number.
                        @elseif($otpMode === 'phone')
                            OTP sent to your registered mobile number via SMS.
                        @else
                            We sent a 6-digit code to <strong id="email-display"></strong>
                        @endif
                    </p>
                </div>
                <form id="otp-form" novalidate>@csrf
                    <label class="auth-label" for="otp">Verification code</label>
                    <input class="otp-input" inputmode="numeric" pattern="[0-9]*"
                           autocomplete="one-time-code" type="text" id="otp" name="otp"
                           maxlength="6" required placeholder="000000">
                    <div class="otp-actions">
                        <span>Expires in 10 minutes</span>
                        <button type="button" id="resend-otp-btn">Resend code</button>
                    </div>
                    <button type="submit" id="verify-otp-btn" class="auth-primary">
                        <span>Verify and login</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="button" id="back-to-email-btn" class="auth-secondary">
                        <i class="fas fa-arrow-left"></i>
                        @if($otpMode === 'phone') Change mobile number
                        @elseif($otpMode === 'both') Change email / mobile
                        @else Change email address
                        @endif
                    </button>
                </form>
            </div>

            <p class="auth-switch">New to ApnaNest? <a href="{{ route('register') }}">Create an account</a></p>
        </div>
    </div>
</section>

@push('styles')
<style>
/* Auto-detect icon animation for smart identifier input */
#identifier { transition: padding-left .2s; }
</style>
@endpush

@push('scripts')
@if($otpMode === 'both')
<script>
// Auto-detect email vs phone and update icon + inputmode
const identifierInput = document.getElementById('identifier');
const identifierIcon  = document.getElementById('identifier-icon');
if (identifierInput) {
    identifierInput.addEventListener('input', function() {
        const val = this.value.trim();
        const isPhone = /^[0-9+\- ]{0,15}$/.test(val) && val.length > 0 && !val.includes('@');
        identifierIcon.className = isPhone ? 'fas fa-mobile-screen-button' : 'far fa-envelope';
        this.setAttribute('inputmode', isPhone ? 'numeric' : 'email');
    });
}
</script>
@endif
@endpush

@include('auth.partials.auth-styles')

{{-- Custom OTP script that supports identifier (email or phone) --}}
@if($otpMode !== 'email')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mode         = @json($otpMode);
    const status       = document.getElementById('status-message');
    const identInput   = document.getElementById('identifier') || document.getElementById('email');
    const otp          = document.getElementById('otp');

    const show = (message, type = 'error') => {
        status.className = 'auth-alert ' + type;
        status.querySelector('i').className = 'fas ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
        status.querySelector('span').textContent = message;
    };

    const loading = (button, on) => {
        if (on) { button.dataset.label = button.innerHTML; button.disabled = true; button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Please wait...</span>'; }
        else { button.disabled = false; if (button.dataset.label) button.innerHTML = button.dataset.label; }
    };

    const post = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(payload)
        });
        let data = {};
        try { data = await response.json(); } catch (e) {}
        if (!response.ok && !data.message) data.message = 'The request could not be completed.';
        return data;
    };

    const isEmail = (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
    const isPhone = (val) => /^[0-9+\-\s]{10,15}$/.test(val.replace(/\s/g, ''));

    const send = async (button) => {
        const val = identInput.value.trim();
        if (!val) { show('Please enter your email or mobile number.'); identInput.focus(); return false; }

        const payload = {};
        if (isEmail(val)) {
            payload.email = val;
        } else if (isPhone(val)) {
            payload.phone = val;
        } else {
            show('Enter a valid email address or 10-digit mobile number.'); identInput.focus(); return false;
        }

        loading(button, true);
        try {
            const data = await post(@json(route('send.otp')), payload);
            if (!data.success) { show(data.message || 'Could not send the code. Please try again.'); return false; }

            // Update sent description
            const sentDesc = document.getElementById('otp-sent-desc');
            if (sentDesc) {
                if (isEmail(val)) sentDesc.innerHTML = 'OTP sent to <strong>' + val + '</strong>' + (mode === 'both' ? ' and your registered mobile number.' : '.');
                else sentDesc.innerHTML = 'OTP sent to your mobile <strong>' + val + '</strong>' + (mode === 'both' ? ' and your registered email.' : '.');
            }

            document.getElementById('email-step').classList.add('hidden');
            document.getElementById('otp-step').classList.remove('hidden');
            otp.value = ''; otp.focus();
            show('Verification code sent successfully.', 'success');
            return true;
        } catch (e) { show('Connection error. Please try again.'); return false; }
        finally { loading(button, false); }
    };

    document.getElementById('email-form').addEventListener('submit', async e => { e.preventDefault(); await send(document.getElementById('send-otp-btn')); });

    document.getElementById('otp-form').addEventListener('submit', async e => {
        e.preventDefault();
        const code = otp.value.replace(/\D/g, '');
        if (code.length !== 6) { show('Enter the complete 6-digit verification code.'); otp.focus(); return; }
        const button = document.getElementById('verify-otp-btn');
        loading(button, true);
        const val = identInput.value.trim();
        const payload = { otp: code };
        if (isEmail(val)) payload.email = val; else payload.phone = val;
        try {
            const data = await post(@json(route('verify.login.otp')), payload);
            if (data.success) { show('Login successful. Redirecting...', 'success'); location.href = data.redirect || @json(route('dashboard')); return; }
            show(data.message || 'The verification code is invalid or expired.');
        } catch (e) { show('Verification failed. Please try again.'); }
        finally { loading(button, false); }
    });

    document.getElementById('resend-otp-btn').addEventListener('click', e => send(e.currentTarget));

    document.getElementById('back-to-email-btn').addEventListener('click', () => {
        document.getElementById('otp-step').classList.add('hidden');
        document.getElementById('email-step').classList.remove('hidden');
        status.classList.add('hidden');
        identInput.focus();
    });

    otp.addEventListener('input', () => otp.value = otp.value.replace(/\D/g, '').slice(0, 6));
});
</script>
@else
@include('auth.partials.otp-script', ['mode' => 'login'])
@endif
@endsection
