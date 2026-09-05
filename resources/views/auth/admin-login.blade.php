@php
    $primary = \App\Models\Setting::get('primary_color', '#4F46E5');
    $primary = ltrim($primary, '#');
    if (strlen($primary) === 3) { $primary = $primary[0].$primary[0].$primary[1].$primary[1].$primary[2].$primary[2]; }
    $pr = hexdec(substr($primary,0,2)); $pg = hexdec(substr($primary,2,2)); $pb = hexdec(substr($primary,4,2));
    $hex = fn($r,$g,$b) => sprintf('#%02x%02x%02x', max(0,min(255,$r)), max(0,min(255,$g)), max(0,min(255,$b)));
    $primaryHex = $hex($pr,$pg,$pb);
    $primaryLight = $hex((int)($pr+(255-$pr)*.94), (int)($pg+(255-$pg)*.94), (int)($pb+(255-$pb)*.94));
    $primaryMid   = $hex((int)($pr+(255-$pr)*.88), (int)($pg+(255-$pg)*.88), (int)($pb+(255-$pb)*.88));
    $websiteName = \App\Models\Setting::get('website_name', 'ApnaNest');
@endphp

@extends('layouts.auth')

@section('title', 'Secure Access | '.$websiteName)
@section('description', 'Admin and staff secure access portal.')

@push('styles')
<style>
    :root{--admin-primary:{{ $primaryHex }};--admin-primary-light:{{ $primaryLight }};--admin-primary-mid:{{ $primaryMid }};--admin-ink:#0f172a;--admin-muted:#64748b;--admin-border:#e2e8f0}
    *{box-sizing:border-box}
    .admin-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#f8fafc 0%,#eef2ff 50%,#f8fafc 100%);position:relative;overflow:hidden}
    .admin-page::before{content:"";position:absolute;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,var(--admin-primary-light) 0%,transparent 70%);top:-200px;right:-150px;opacity:.6;pointer-events:none}
    .admin-page::after{content:"";position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,var(--admin-primary-mid) 0%,transparent 70%);bottom:-150px;left:-100px;opacity:.5;pointer-events:none}
    .admin-card{width:100%;max-width:440px;background:#fff;border-radius:24px;box-shadow:0 25px 50px -20px rgba(15,23,42,.18),0 0 0 1px rgba(15,23,42,.04);position:relative;z-index:1;overflow:hidden}
    .admin-card-head{padding:36px 36px 0;text-align:center}
    .admin-brand{font-size:22px;font-weight:800;color:var(--admin-ink);letter-spacing:-.02em;margin-bottom:4px}
    .admin-brand span{color:var(--admin-primary)}
    .admin-card-head p{font-size:13px;color:var(--admin-muted);margin:0}
    .admin-steps{display:flex;align-items:center;justify-content:center;gap:8px;padding:24px 36px 0}
    .admin-step-dot{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;transition:all .3s ease;flex-shrink:0}
    .admin-step-dot.active{background:var(--admin-primary);color:#fff}
    .admin-step-dot.done{background:var(--admin-primary-light);color:var(--admin-primary)}
    .admin-step-dot.pending{background:#f1f5f9;color:#94a3b8}
    .admin-step-line{width:40px;height:2px;background:#e2e8f0;border-radius:1px;transition:background .3s ease}
    .admin-step-line.done{background:var(--admin-primary-light)}
    .admin-body{padding:28px 36px 36px}
    .admin-form-step{display:none;animation:adminFadeIn .35s ease}
    .admin-form-step.visible{display:block}
    @keyframes adminFadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
    .admin-label{display:block;font-size:12px;font-weight:700;color:var(--admin-ink);margin-bottom:6px;letter-spacing:.02em}
    .admin-input-wrap{position:relative;margin-bottom:14px}
    .admin-input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:13px;color:#94a3b8;pointer-events:none}
    .admin-input-wrap input{width:100%;padding:12px 14px 12px 40px;border:1.5px solid var(--admin-border);border-radius:12px;font-size:14px;color:var(--admin-ink);background:#f8fafc;transition:all .2s ease;outline:none;font-family:inherit}
    .admin-input-wrap input:focus{border-color:var(--admin-primary);background:#fff;box-shadow:0 0 0 3px var(--admin-primary-light)}
    .admin-input-wrap input::placeholder{color:#cbd5e1}
    .admin-btn{width:100%;padding:13px;border:none;border-radius:12px;background:var(--admin-primary);color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:all .2s ease;display:inline-flex;align-items:center;justify-content:center;gap:8px;letter-spacing:.01em}
    .admin-btn:hover{filter:brightness(1.08);transform:translateY(-1px)}
    .admin-btn:active{transform:translateY(0)}
    .admin-btn i{font-size:12px}
    .admin-alert{padding:12px 14px;border-radius:10px;font-size:12px;font-weight:600;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;line-height:1.4}
    .admin-alert.error{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
    .admin-alert.success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
    .admin-alert i{margin-top:1px;flex-shrink:0}
    .admin-footer{text-align:center;padding:0 36px 32px;font-size:12px;color:var(--admin-muted)}
    .admin-footer a{color:var(--admin-primary);text-decoration:none;font-weight:600}
    .admin-footer a:hover{text-decoration:underline}
    .admin-shield{width:48px;height:48px;border-radius:14px;background:var(--admin-primary-light);color:var(--admin-primary);display:inline-flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:12px}
    @media(max-width:480px){.admin-card-head,.admin-steps,.admin-body,.admin-footer{padding-left:24px;padding-right:24px}.admin-card{border-radius:20px}}
</style>
@endpush

@section('content')
<div class="admin-page">
    <div class="admin-card">
        <div class="admin-card-head">
            <div style="display:flex;justify-content:center"><div class="admin-shield"><i class="fas fa-shield-halved"></i></div></div>
            <div class="admin-brand">{{ $websiteName }} <span>Portal</span></div>
            <p>Restricted access — authorised personnel only</p>
        </div>

        <div class="admin-steps">
            <div class="admin-step-dot {{ !$passkeyValidated ? 'active' : 'done' }}">1</div>
            <div class="admin-step-line {{ $passkeyValidated ? 'done' : '' }}"></div>
            <div class="admin-step-dot {{ $passkeyValidated ? 'active' : 'pending' }}">2</div>
        </div>

        <div class="admin-body">
            @if(session('status'))
                <div class="admin-alert success"><i class="fas fa-check-circle"></i><span>{{ session('status') }}</span></div>
            @endif
            @if($errors->any())
                <div class="admin-alert error"><i class="fas fa-triangle-exclamation"></i><span>{{ $errors->first() }}</span></div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" id="portalForm">
                @csrf

                <div class="admin-form-step {{ !$passkeyValidated ? 'visible' : '' }}" id="stepPasskey">
                    <label class="admin-label" for="access_passkey">Security Passkey</label>
                    <div class="admin-input-wrap">
                        <i class="fas fa-key"></i>
                        <input type="password" id="access_passkey" name="access_passkey" placeholder="Enter your access passkey" required autocomplete="off">
                    </div>
                    <button type="submit" class="admin-btn"><span>Verify Access</span><i class="fas fa-arrow-right"></i></button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.login.submit') }}" id="portalForm2">
                @csrf
                <div class="admin-form-step {{ $passkeyValidated ? 'visible' : '' }}" id="stepLogin">
                    <label class="admin-label" for="email">Admin email</label>
                    <div class="admin-input-wrap">
                        <i class="far fa-envelope"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com" required autocomplete="email">
                    </div>

                    <label class="admin-label" for="password">Password</label>
                    <div class="admin-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
                    </div>

                    <label class="admin-label" style="display:flex;align-items:center;gap:8px;margin-bottom:16px;cursor:pointer;font-weight:500">
                        <input type="checkbox" name="remember" value="1" style="width:15px;height:15px;accent-color:var(--admin-primary);border-radius:4px">
                        <span style="font-size:13px;color:var(--admin-muted)">Remember this session</span>
                    </label>

                    <button type="submit" class="admin-btn"><span>Sign In</span><i class="fas fa-arrow-right-to-bracket"></i></button>
                </div>
            </form>
        </div>

        <div class="admin-footer">
            <a href="{{ route('home') }}"><i class="fas fa-arrow-left" style="font-size:10px;margin-right:4px"></i>Back to website</a>
        </div>
    </div>
</div>
@endsection
