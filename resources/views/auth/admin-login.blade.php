@php
    $websiteName = \App\Models\Setting::get('website_name', 'ApnaNest');
    $websiteLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
    $primaryColor = \App\Models\Setting::get('primary_color', '#4F46E5');
@endphp

@extends('layouts.base')

@section('title', 'Admin Security Gate | ' . $websiteName)
@section('description', 'Restricted access portal for authorized administration personnel only.')

@section('layout-top-banner')
@endsection

@section('layout-navigation')
@endsection

@section('layout-loading')
@endsection

@section('layout-footer')
@endsection

@section('layout-bottom-navigation')
@endsection

@section('layout-popup')
@endsection

@push('styles')
<style>
    /* Reset and lock portal atmosphere */
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background: #090d16 !important;
        color: #f1f5f9;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .portal-screen {
        min-height: 100vh;
        min-height: 100dvh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(ellipse 70% 50% at 50% -10%, rgba(79, 70, 229, 0.28), transparent 70%),
            radial-gradient(ellipse 60% 40% at 100% 100%, rgba(37, 99, 235, 0.16), transparent 60%),
            radial-gradient(ellipse 50% 35% at 0% 100%, rgba(147, 51, 234, 0.12), transparent 50%),
            #090d16;
    }

    /* Ambient decorative rings */
    .portal-screen::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 760px;
        height: 760px;
        border: 1px dashed rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .portal-card {
        width: 100%;
        max-width: 440px;
        background: rgba(15, 23, 42, 0.88);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 24px;
        box-shadow:
            0 25px 60px -15px rgba(0, 0, 0, 0.7),
            0 0 0 1px rgba(255, 255, 255, 0.05),
            0 0 40px rgba(79, 70, 229, 0.15);
        padding: 36px 30px 30px;
        box-sizing: border-box;
        position: relative;
        z-index: 2;
        animation: portalFadeIn 0.35s ease-out;
    }

    @keyframes portalFadeIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .portal-badge-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 16px;
    }

    .portal-shield-badge {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.25) 0%, rgba(99, 102, 241, 0.1) 100%);
        border: 1px solid rgba(129, 140, 248, 0.35);
        color: #818cf8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        box-shadow: 0 8px 24px -4px rgba(79, 70, 229, 0.4);
    }

    .portal-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .portal-brand {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 4px;
        text-decoration: none;
    }

    .portal-brand span {
        color: #818cf8;
    }

    .portal-subtitle {
        color: #94a3b8;
        font-size: 12.5px;
        margin: 0;
        font-weight: 500;
        line-height: 1.5;
    }

    /* Steps indicator bar */
    .portal-steps-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        background: rgba(2, 6, 23, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.06);
        padding: 5px;
        border-radius: 14px;
        margin-bottom: 24px;
    }

    .portal-step-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 8px 10px;
        border-radius: 10px;
        font-size: 11.5px;
        font-weight: 700;
        transition: all 0.2s ease;
        text-align: center;
        user-select: none;
    }

    .portal-step-item.is-active {
        background: rgba(79, 70, 229, 0.3);
        border: 1px solid rgba(129, 140, 248, 0.4);
        color: #e0e7ff;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
    }

    .portal-step-item.is-done {
        background: rgba(16, 185, 129, 0.15);
        border: 1px solid rgba(16, 185, 129, 0.3);
        color: #6ee7b7;
    }

    .portal-step-item.is-pending {
        color: #64748b;
        background: transparent;
    }

    /* Alerts */
    .portal-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: 12px;
        line-height: 1.45;
        margin-bottom: 18px;
        font-weight: 600;
        animation: portalAlertIn 0.2s ease;
    }

    @keyframes portalAlertIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .portal-alert.is-error {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #fca5a5;
    }

    .portal-alert.is-success {
        background: rgba(16, 185, 129, 0.12);
        border: 1px solid rgba(16, 185, 129, 0.35);
        color: #6ee7b7;
    }

    .portal-alert i {
        margin-top: 1.5px;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Form controls */
    .portal-field-group {
        margin-bottom: 16px;
    }

    .portal-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 7px;
    }

    .portal-input-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .portal-input-icon {
        position: absolute;
        left: 14px;
        color: #64748b;
        font-size: 13px;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .portal-input {
        width: 100%;
        height: 48px;
        background: rgba(2, 6, 23, 0.65);
        border: 1.5px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 0 42px 0 40px;
        color: #f8fafc;
        font-size: 14px;
        font-weight: 600;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .portal-input:focus {
        border-color: #6366f1;
        background: rgba(2, 6, 23, 0.9);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.22);
    }

    .portal-input:focus ~ .portal-input-icon {
        color: #818cf8;
    }

    .portal-input::placeholder {
        color: #475569;
        font-weight: 500;
    }

    .portal-toggle-visibility {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: #64748b;
        font-size: 13px;
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: color 0.2s ease;
    }

    .portal-toggle-visibility:hover {
        color: #cbd5e1;
    }

    .portal-checkbox-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 14px 0 20px;
        cursor: pointer;
        user-select: none;
    }

    .portal-checkbox-row input {
        width: 16px;
        height: 16px;
        accent-color: #6366f1;
        border-radius: 4px;
        cursor: pointer;
        margin: 0;
    }

    .portal-checkbox-row span {
        font-size: 12.5px;
        color: #94a3b8;
        font-weight: 500;
    }

    /* Submit Button */
    .portal-submit-btn {
        width: 100%;
        height: 48px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 12px;
        color: #ffffff;
        font-size: 13.5px;
        font-weight: 800;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 10px 22px -5px rgba(79, 70, 229, 0.45);
        transition: all 0.2s ease;
        margin-top: 6px;
    }

    .portal-submit-btn:hover {
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
        transform: translateY(-1px);
        box-shadow: 0 14px 26px -6px rgba(79, 70, 229, 0.6);
    }

    .portal-submit-btn:active {
        transform: translateY(0);
    }

    .portal-submit-btn i {
        font-size: 12px;
        transition: transform 0.2s ease;
    }

    .portal-submit-btn:hover i {
        transform: translateX(2px);
    }

    /* Footer Links */
    .portal-card-footer {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-align: center;
    }

    .portal-link {
        color: #94a3b8;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }

    .portal-link:hover {
        color: #e0e7ff;
    }

    .portal-meta-note {
        font-size: 10.5px;
        color: #64748b;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .portal-meta-note i {
        font-size: 9px;
        color: #10b981;
    }

    /* Mobile Responsive Optimizations */
    @media (max-width: 480px) {
        .portal-screen {
            padding: 16px 12px;
        }

        .portal-card {
            padding: 28px 20px 22px;
            border-radius: 20px;
        }

        .portal-brand {
            font-size: 18px;
        }

        .portal-steps-bar {
            margin-bottom: 18px;
        }

        .portal-step-item {
            font-size: 11px;
            padding: 7px 6px;
        }

        .portal-input {
            height: 46px;
            font-size: 13.5px;
        }

        .portal-submit-btn {
            height: 46px;
            font-size: 13px;
        }
    }
</style>
@endpush

@section('content')
<main class="portal-screen">
    <div class="portal-card">

        <!-- Top Shield Icon -->
        <div class="portal-badge-wrap">
            <div class="portal-shield-badge">
                <i class="fas fa-shield-halved"></i>
            </div>
        </div>

        <!-- Header -->
        <div class="portal-header">
            <a href="{{ route('home') }}" class="portal-brand">
                {{ $websiteName }} <span>Portal</span>
            </a>
            <p class="portal-subtitle">Restricted area — 2-factor verified staff access</p>
        </div>

        <!-- Visual Step Tracker -->
        <div class="portal-steps-bar">
            <div class="portal-step-item {{ $passkeyValidated ? 'is-done' : 'is-active' }}">
                @if($passkeyValidated)
                    <i class="fas fa-circle-check"></i>
                @else
                    <i class="fas fa-key"></i>
                @endif
                <span>1. Passkey</span>
            </div>
            <div class="portal-step-item {{ $passkeyValidated ? 'is-active' : 'is-pending' }}">
                <i class="fas fa-user-lock"></i>
                <span>2. Login</span>
            </div>
        </div>

        <!-- Flash Status or Errors -->
        @if(session('status'))
            <div class="portal-alert is-success" role="status">
                <i class="fas fa-circle-check"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="portal-alert is-error" role="alert">
                <i class="fas fa-triangle-exclamation"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- STEP 1: Security Passkey Verification -->
        @if(!$passkeyValidated)
            <form method="POST" action="{{ route('admin.login.submit') }}" id="portalPasskeyForm" autocomplete="off">
                @csrf
                <div class="portal-field-group">
                    <label class="portal-label" for="access_passkey">
                        <span>Security Passkey</span>
                        <span style="color: #818cf8; font-size: 10px; font-weight: 700;">REQUIRED</span>
                    </label>
                    <div class="portal-input-box">
                        <i class="fas fa-key portal-input-icon"></i>
                        <input
                            type="password"
                            id="access_passkey"
                            name="access_passkey"
                            class="portal-input"
                            placeholder="Enter gateway passkey..."
                            required
                            autofocus
                            autocomplete="off"
                        >
                        <button type="button" class="portal-toggle-visibility" onclick="toggleSecretVisibility('access_passkey', this)" aria-label="Toggle passkey visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="portal-submit-btn" id="portalPasskeySubmit">
                    <span>Verify Security Passkey</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        @else
        <!-- STEP 2: Administrator Credentials -->
            <form method="POST" action="{{ route('admin.login.submit') }}" id="portalLoginForm">
                @csrf

                <!-- Admin Email -->
                <div class="portal-field-group">
                    <label class="portal-label" for="email">Admin Email</label>
                    <div class="portal-input-box">
                        <i class="far fa-envelope portal-input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="portal-input"
                            value="{{ old('email') }}"
                            placeholder="admin@example.com"
                            required
                            autofocus
                            autocomplete="email"
                        >
                    </div>
                </div>

                <!-- Admin Password -->
                <div class="portal-field-group">
                    <label class="portal-label" for="password">Password</label>
                    <div class="portal-input-box">
                        <i class="fas fa-lock portal-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="portal-input"
                            placeholder="••••••••••••"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="portal-toggle-visibility" onclick="toggleSecretVisibility('password', this)" aria-label="Toggle password visibility">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember session -->
                <label class="portal-checkbox-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep admin session active</span>
                </label>

                <!-- Submit Button -->
                <button type="submit" class="portal-submit-btn" id="portalLoginSubmit">
                    <span>Sign In to Dashboard</span>
                    <i class="fas fa-arrow-right-to-bracket"></i>
                </button>
            </form>
        @endif

        <!-- Card Footer -->
        <div class="portal-card-footer">
            @if($passkeyValidated)
                <a href="{{ route('admin.login-access', ['reset_passkey' => 1]) }}" class="portal-link" style="color: #a5b4fc;">
                    <i class="fas fa-rotate-left" style="font-size: 11px;"></i> Re-enter security passkey
                </a>
            @endif

            <a href="{{ route('home') }}" class="portal-link">
                <i class="fas fa-arrow-left" style="font-size: 10px;"></i> Return to public website
            </a>

            <div class="portal-meta-note">
                <i class="fas fa-shield-check"></i>
                <span>256-Bit SSL Encrypted &amp; Monitored</span>
            </div>
        </div>

    </div>
</main>

<script>
    function toggleSecretVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    }

    // Handle form submit state to give immediate visual feedback
    document.addEventListener('DOMContentLoaded', function () {
        const pForm = document.getElementById('portalPasskeyForm');
        if (pForm) {
            pForm.addEventListener('submit', function () {
                const btn = document.getElementById('portalPasskeySubmit');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.innerHTML = '<span>Verifying passkey...</span><i class="fas fa-spinner fa-spin"></i>';
                }
            });
        }

        const lForm = document.getElementById('portalLoginForm');
        if (lForm) {
            lForm.addEventListener('submit', function () {
                const btn = document.getElementById('portalLoginSubmit');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.7';
                    btn.innerHTML = '<span>Signing in...</span><i class="fas fa-spinner fa-spin"></i>';
                }
            });
        }
    });
</script>
@endsection
