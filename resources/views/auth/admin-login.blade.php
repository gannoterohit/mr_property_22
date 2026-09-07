@php
    $websiteName = \App\Models\Setting::get('website_name', 'RoomRental');
    $navbarLogo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
    $primaryColor = \App\Models\Setting::get('primary_color', '#105024');
    if (!$primaryColor) {
        $primaryColor = '#105024';
    }
    // Compute RGB for rgba() usage
    [$pr, $pg, $pb] = sscanf(ltrim($primaryColor, '#'), '%02x%02x%02x');
    $primaryRgb = "$pr,$pg,$pb";
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
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        background: #f1f5f9 !important;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .portal-wrapper {
        min-height: 100vh;
        min-height: 100dvh;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        box-sizing: border-box;
        background:
            radial-gradient(circle at 12% 12%, rgba({{ $primaryRgb }}, 0.08) 0%, transparent 35%),
            radial-gradient(circle at 88% 88%, rgba({{ $primaryRgb }}, 0.07) 0%, transparent 40%),
            linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
    }

    /* Split Master Console Shell */
    .portal-console {
        width: min(1040px, 100%);
        min-height: 600px;
        display: grid;
        grid-template-columns: 0.95fr 1.05fr;
        background: #ffffff;
        border: 1px solid #dbe4f0;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 25px 70px -15px rgba(15, 23, 42, 0.12), 0 0 1px rgba(15, 23, 42, 0.08);
    }

    /* Left Side: Story & Security Showcase */
    .portal-story {
        position: relative;
        padding: 50px 45px;
        color: #ffffff;
        background: linear-gradient(145deg, color-mix(in srgb, var(--primary) 30%, #000) 0%, color-mix(in srgb, var(--primary) 50%, #000) 45%, var(--primary) 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .portal-story::before {
        content: "";
        position: absolute;
        width: 360px;
        height: 360px;
        border: 70px solid rgba(255, 255, 255, 0.04);
        border-radius: 50%;
        right: -140px;
        bottom: -120px;
        pointer-events: none;
    }

    .portal-story::after {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.08) 0%, transparent 60%);
        pointer-events: none;
    }

    .portal-story > * {
        position: relative;
        z-index: 2;
    }

    .portal-story-top {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .portal-story-brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: #ffffff;
    }

    .portal-story-logo {
        height: 42px;
        width: auto;
        max-width: 180px;
        object-fit: contain;
    }

    .portal-story-mark {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .portal-story-title {
        font-size: 24px;
        font-weight: 900;
        letter-spacing: -0.6px;
        margin: 0;
    }

    .portal-story-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: max-content;
        padding: 6px 13px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #dcfce7;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .portal-story-headline {
        margin: 0;
        font-size: 32px;
        line-height: 1.18;
        letter-spacing: -1px;
        font-weight: 850;
        color: #ffffff;
    }

    .portal-story-headline span {
        color: #86efac;
    }

    .portal-story-desc {
        color: #cbd5e1;
        line-height: 1.65;
        font-size: 14px;
        margin: 0;
    }

    .portal-features-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin-top: 10px;
    }

    .portal-feature-row {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .portal-feature-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #86efac;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }

    .portal-feature-text strong {
        display: block;
        font-size: 13.5px;
        color: #ffffff;
        font-weight: 700;
    }

    .portal-feature-text span {
        font-size: 11.5px;
        color: #94a3b8;
    }

    .portal-story-footer {
        padding-top: 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11px;
        color: #94a3b8;
    }

    .portal-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-weight: 700;
        color: #86efac;
    }

    .portal-pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.3);
        animation: portalPulse 2s infinite;
    }

    @keyframes portalPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }

    /* Right Side: Action Portal Form */
    .portal-panel {
        padding: 50px 48px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #ffffff;
    }

    .portal-panel-head {
        margin-bottom: 24px;
    }

    .portal-step-label {
        display: block;
        color: var(--primary);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .portal-panel-title {
        font-size: 28px;
        line-height: 1.2;
        margin: 0 0 8px;
        color: #0f172a;
        font-weight: 850;
        letter-spacing: -0.6px;
    }

    .portal-panel-desc {
        margin: 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.55;
    }

    /* Step tabs */
    .portal-step-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 5px;
        border-radius: 14px;
        margin-bottom: 22px;
    }

    .portal-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        text-align: center;
        user-select: none;
        transition: all 0.2s ease;
    }

    .portal-tab.is-active {
        background: #ffffff;
        color: var(--primary);
        border: 1px solid rgba(var(--primary-rgb), 0.4);
        box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.08);
    }

    .portal-tab.is-done {
        background: rgba(var(--primary-rgb), 0.08);
        color: var(--primary);
        border: 1px solid rgba(var(--primary-rgb), 0.3);
    }

    .portal-tab.is-pending {
        background: transparent;
        color: #94a3b8;
    }

    /* Alerts */
    .portal-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 12.5px;
        line-height: 1.45;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .portal-alert.is-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }

    .portal-alert.is-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #15803d;
    }

    .portal-alert i {
        margin-top: 2px;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* Fields */
    .portal-form-group {
        margin-bottom: 18px;
    }

    .portal-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #334155;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 8px;
    }

    .portal-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .portal-input-container i.field-icon {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .portal-input-field {
        width: 100%;
        height: 52px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 13px;
        padding: 0 46px 0 44px;
        color: #0f172a;
        font-size: 14.5px;
        font-weight: 600;
        box-sizing: border-box;
        outline: none;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .portal-input-field:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.12);
    }

    .portal-input-field:focus ~ i.field-icon {
        color: var(--primary);
    }

    .portal-input-field::placeholder {
        color: #94a3b8;
        font-weight: 500;
    }

    .portal-eye-btn {
        position: absolute;
        right: 12px;
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 14px;
        cursor: pointer;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .portal-eye-btn:hover {
        color: #0f172a;
        background: #f1f5f9;
    }

    .portal-checkbox-label {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 16px 0 22px;
        cursor: pointer;
        user-select: none;
    }

    .portal-checkbox-label input {
        width: 17px;
        height: 17px;
        accent-color: var(--primary);
        border-radius: 4px;
        cursor: pointer;
        margin: 0;
    }

    .portal-checkbox-label span {
        font-size: 13px;
        color: #475569;
        font-weight: 600;
    }

    /* Action Button */
    .portal-btn-primary {
        width: 100%;
        height: 52px;
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 60%, #000) 0%, var(--primary) 100%);
        border: none;
        border-radius: 13px;
        color: #ffffff;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.03em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        box-shadow: 0 10px 24px -5px rgba(var(--primary-rgb), 0.4);
        transition: all 0.2s ease;
        margin-top: 4px;
    }

    .portal-btn-primary:hover {
        background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 40%, #000) 0%, color-mix(in srgb, var(--primary) 60%, #000) 100%);
        transform: translateY(-1px);
        box-shadow: 0 14px 28px -5px rgba(var(--primary-rgb), 0.5);
    }

    .portal-btn-primary:active {
        transform: translateY(0);
    }

    .portal-btn-primary i {
        font-size: 13px;
        transition: transform 0.2s ease;
    }

    .portal-btn-primary:hover i {
        transform: translateX(3px);
    }

    /* Panel Footer */
    .portal-panel-footer {
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .portal-footer-link {
        color: #64748b;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }

    .portal-footer-link:hover {
        color: var(--primary);
    }

    .portal-reset-link {
        color: #059669;
        font-size: 12.5px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }

    .portal-reset-link:hover {
        text-decoration: underline;
    }

    /* Responsive Breakpoints */
    @media (max-width: 960px) {
        .portal-wrapper {
            padding: 24px 14px;
        }

        .portal-console {
            grid-template-columns: 1fr;
            max-width: 540px;
            min-height: auto;
            border-radius: 24px;
        }

        .portal-story {
            padding: 36px 32px 30px;
        }

        .portal-story-headline {
            font-size: 26px;
        }

        .portal-features-list {
            display: none;
        }

        .portal-story-footer {
            margin-top: 20px;
            padding-top: 16px;
        }

        .portal-panel {
            padding: 38px 32px 34px;
        }
    }

    @media (max-width: 480px) {
        .portal-wrapper {
            padding: 14px 10px;
        }

        .portal-console {
            border-radius: 20px;
        }

        .portal-story {
            padding: 28px 22px 24px;
        }

        .portal-panel {
            padding: 28px 20px 24px;
        }

        .portal-panel-title {
            font-size: 22px;
        }

        .portal-input-field {
            height: 48px;
            font-size: 13.5px;
        }

        .portal-btn-primary {
            height: 48px;
            font-size: 13.5px;
        }

        .portal-panel-footer {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="portal-wrapper">
    <div class="portal-console">

        <!-- LEFT SIDE: Security & System Presentation -->
        <aside class="portal-story">
            <div class="portal-story-top">
                <a href="{{ route('home') }}" class="portal-story-brand">
                    @if($navbarLogo)
                        <img src="{{ \App\Models\Setting::mediaUrl($navbarLogo) }}" alt="{{ $websiteName }}" class="portal-story-logo">
                    @else
                        <div class="portal-story-mark">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <span class="portal-story-title">{{ $websiteName }}</span>
                    @endif
                </a>

                <div>
                    <span class="portal-story-kicker"><i class="fas fa-lock"></i> Restricted Area</span>
                </div>

                <div>
                    <h1 class="portal-story-headline">
                        Administrative <span>Security Console</span>
                    </h1>
                    <p class="portal-story-desc" style="margin-top: 10px;">
                        Authorized staff access point for property verification, platform finance, user management, and core settings.
                    </p>
                </div>

                <div class="portal-features-list">
                    <div class="portal-feature-row">
                        <div class="portal-feature-icon"><i class="fas fa-key"></i></div>
                        <div class="portal-feature-text">
                            <strong>Dual-Layer Passkey Gateway</strong>
                            <span>System protected by environment master key</span>
                        </div>
                    </div>
                    <div class="portal-feature-row">
                        <div class="portal-feature-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="portal-feature-text">
                            <strong>Role-Based Staff Access</strong>
                            <span>Audited sessions with activity trace logging</span>
                        </div>
                    </div>
                    <div class="portal-feature-row">
                        <div class="portal-feature-icon"><i class="fas fa-lock-check"></i></div>
                        <div class="portal-feature-text">
                            <strong>Encrypted Communication</strong>
                            <span>256-Bit SSL secured administrative endpoint</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="portal-story-footer">
                <span class="portal-status-pill">
                    <span class="portal-pulse-dot"></span>
                    <span>System Operational</span>
                </span>
                <span>v2.4 Production</span>
            </div>
        </aside>

        <!-- RIGHT SIDE: Authentication Form Panel -->
        <main class="portal-panel">
            <div class="portal-panel-head">
                <span class="portal-step-label">
                    @if(!$passkeyValidated)
                        Step 1 of 2 · Gateway Verification
                    @else
                        Step 2 of 2 · Administrator Credentials
                    @endif
                </span>
                <h2 class="portal-panel-title">
                    @if(!$passkeyValidated)
                        Security Passkey
                    @else
                        Sign In to Dashboard
                    @endif
                </h2>
                <p class="portal-panel-desc">
                    @if(!$passkeyValidated)
                        Please enter your master security passkey to unlock administrator credentials.
                    @else
                        Security passkey verified. Enter your registered admin credentials to continue.
                    @endif
                </p>
            </div>

            <!-- Two-Step Progress Bar -->
            <div class="portal-step-tabs">
                <div class="portal-tab {{ $passkeyValidated ? 'is-done' : 'is-active' }}">
                    @if($passkeyValidated)
                        <i class="fas fa-circle-check"></i>
                        <span>1. Passkey Verified</span>
                    @else
                        <i class="fas fa-key"></i>
                        <span>1. Security Passkey</span>
                    @endif
                </div>
                <div class="portal-tab {{ $passkeyValidated ? 'is-active' : 'is-pending' }}">
                    <i class="fas fa-user-lock"></i>
                    <span>2. Admin Login</span>
                </div>
            </div>

            <!-- Flash Alerts -->
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

            <!-- STEP 1: Passkey Form -->
            @if(!$passkeyValidated)
                <form method="POST" action="{{ route('admin.login.submit') }}" id="portalPasskeyForm" autocomplete="off">
                    @csrf
                    <div class="portal-form-group">
                        <label class="portal-label" for="access_passkey">
                            <span>Master Security Passkey</span>
                            <span style="color: var(--primary); font-size: 10px; font-weight: 800;">REQUIRED</span>
                        </label>
                        <div class="portal-input-container">
                            <i class="fas fa-key field-icon"></i>
                            <input
                                type="password"
                                id="access_passkey"
                                name="access_passkey"
                                class="portal-input-field"
                                placeholder="Enter passkey (e.g. from .env)"
                                required
                                autofocus
                                autocomplete="off"
                            >
                            <button type="button" class="portal-eye-btn" onclick="togglePasswordVisibility('access_passkey', this)" aria-label="Show or hide passkey">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="portal-btn-primary" id="portalPasskeyBtn">
                        <span>Verify &amp; Unlock Gateway</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            @else
            <!-- STEP 2: Login Credentials Form -->
                <form method="POST" action="{{ route('admin.login.submit') }}" id="portalLoginForm">
                    @csrf

                    <!-- Admin Email -->
                    <div class="portal-form-group">
                        <label class="portal-label" for="email">Admin Email Address</label>
                        <div class="portal-input-container">
                            <i class="far fa-envelope field-icon"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="portal-input-field"
                                value="{{ old('email') }}"
                                placeholder="admin@example.com"
                                required
                                autofocus
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <!-- Admin Password -->
                    <div class="portal-form-group">
                        <label class="portal-label" for="password">Account Password</label>
                        <div class="portal-input-container">
                            <i class="fas fa-lock field-icon"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="portal-input-field"
                                placeholder="••••••••••••"
                                required
                                autocomplete="current-password"
                            >
                            <button type="button" class="portal-eye-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Show or hide password">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <label class="portal-checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember this admin session</span>
                    </label>

                    <!-- Submit Button -->
                    <button type="submit" class="portal-btn-primary" id="portalLoginBtn">
                        <span>Authenticate &amp; Open Dashboard</span>
                        <i class="fas fa-arrow-right-to-bracket"></i>
                    </button>
                </form>
            @endif

            <!-- Footer Links -->
            <div class="portal-panel-footer">
                <a href="{{ route('home') }}" class="portal-footer-link">
                    <i class="fas fa-arrow-left" style="font-size: 11px;"></i> Return to Website
                </a>

                @if($passkeyValidated)
                    <a href="{{ route('admin.login-access', ['reset_passkey' => 1]) }}" class="portal-reset-link">
                        <i class="fas fa-rotate-left"></i> Re-enter Passkey
                    </a>
                @else
                    <span style="font-size: 11.5px; color: #94a3b8; font-weight: 600;">
                        <i class="fas fa-shield-check" style="color: var(--primary); margin-right: 4px;"></i> Protected Gateway
                    </span>
                @endif
            </div>

        </main>

    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, btn) {
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

    document.addEventListener('DOMContentLoaded', function () {
        const pForm = document.getElementById('portalPasskeyForm');
        if (pForm) {
            pForm.addEventListener('submit', function () {
                const btn = document.getElementById('portalPasskeyBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.75';
                    btn.innerHTML = '<span>Verifying passkey...</span><i class="fas fa-spinner fa-spin"></i>';
                }
            });
        }

        const lForm = document.getElementById('portalLoginForm');
        if (lForm) {
            lForm.addEventListener('submit', function () {
                const btn = document.getElementById('portalLoginBtn');
                if (btn) {
                    btn.disabled = true;
                    btn.style.opacity = '0.75';
                    btn.innerHTML = '<span>Authenticating...</span><i class="fas fa-spinner fa-spin"></i>';
                }
            });
        }
    });
</script>
@endsection
