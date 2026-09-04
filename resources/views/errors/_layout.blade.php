@php
    $status = $status ?? 404;
    $configs = [
        '403' => ['code'=>'403','title'=>'Restricted area.','subtitle'=>'Access denied.','description'=>'You don\'t have permission to view this page. Sign in with the right account, or head back home.','icon'=>'lock'],
        '404' => ['code'=>'404','title'=>'Page not found.','subtitle'=>'Lost in space.','description'=>'The page you\'re looking for isn\'t here. It may have been moved, renamed, or it never existed.','icon'=>'compass'],
        '419' => ['code'=>'419','title'=>'Session expired.','subtitle'=>'Time\'s up.','description'=>'For your security this page timed out. Refresh the page and try your action again.','icon'=>'clock'],
        '500' => ['code'=>'500','title'=>'Server error.','subtitle'=>'Something went wrong.','description'=>'A hiccup on our end. Our team has been notified — please try again in a moment.','icon'=>'wrench'],
        '503' => ['code'=>'503','title'=>'Be right back.','subtitle'=>'Tuning the engine.','description'=>'We\'re running a quick bit of maintenance. Refresh in a minute and we should be good to go.','icon'=>'tools'],
    ];
    $cfg = $configs[$status] ?? $configs['404'];
    $websiteName = \App\Models\Setting::get('website_name', 'ApnaNest');

    $primary = \App\Models\Setting::get('primary_color', '#4F46E5');
    $primary = ltrim($primary, '#');
    if (strlen($primary) === 3) { $primary = $primary[0].$primary[0].$primary[1].$primary[1].$primary[2].$primary[2]; }
    $pr = hexdec(substr($primary,0,2)); $pg = hexdec(substr($primary,2,2)); $pb = hexdec(substr($primary,4,2));
    $hex = fn($r,$g,$b) => sprintf('#%02x%02x%02x', max(0,min(255,$r)), max(0,min(255,$g)), max(0,min(255,$b)));
    $mix = fn($r,$g,$b,$amt) => $hex((int)($r+(255-$r)*$amt), (int)($g+(255-$g)*$amt), (int)($b+(255-$b)*$amt));
    $primaryHex = $hex($pr,$pg,$pb);
    $pillBg     = $mix($pr,$pg,$pb,.92);
    $pillBorder = $mix($pr,$pg,$pb,.80);
    $pillText   = $mix($pr,$pg,$pb,.30);
    $iconFill   = $mix($pr,$pg,$pb,.95);
    $iconStroke = $hex($pr,$pg,$pb);
    $iconDeep   = $mix($pr,$pg,$pb,.60);
    $glow       = $hex($pr,$pg,$pb);
@endphp

@extends('layouts.public')
@section('title', $cfg['title'].' · '.$websiteName)
@section('description', $cfg['description'])

@push('styles')
<style>
    :root{--err-primary:{{ $primaryHex }};--err-pill-bg:{{ $pillBg }};--err-pill-text:{{ $pillText }};--err-pill-border:{{ $pillBorder }};--err-icon-fill:{{ $iconFill }};--err-icon-stroke:{{ $iconStroke }};--err-icon-deep:{{ $iconDeep }};--err-glow:{{ $glow }}}
    .err-page{min-height:calc(100vh - 200px);display:flex;align-items:center;justify-content:center;padding:48px 20px;background:#fbfbfd;contain:layout paint}
    .err-wrap{width:100%;max-width:720px;text-align:center}
    .err-illu{width:104px;height:104px;margin:0 auto 20px;display:flex;align-items:center;justify-content:center;position:relative;color:var(--err-icon-stroke)}
    .err-illu::before{content:"";position:absolute;inset:0;border-radius:50%;z-index:-1;background:radial-gradient(circle,color-mix(in srgb,var(--err-glow) 12%,transparent),transparent 70%)}
    .err-illu svg{width:88px;height:88px}
    .err-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:11px;font-weight:600;letter-spacing:.04em;margin-bottom:20px;background:var(--err-pill-bg);color:var(--err-pill-text);border:1px solid var(--err-pill-border)}
    .err-pill i{font-size:9px}
    .err-code{font-size:clamp(96px,18vw,168px);line-height:.95;font-weight:800;letter-spacing:-.06em;background:linear-gradient(180deg,#1d1d1f 0%,var(--err-primary) 130%);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;margin:0;user-select:none}
    .err-title{font-size:clamp(22px,3.2vw,30px);line-height:1.2;font-weight:700;letter-spacing:-.02em;color:#1d1d1f;margin:12px 0 6px}
    .err-subtitle{font-size:clamp(14px,1.6vw,16px);font-weight:500;color:#6e6e73;margin:0 0 10px}
    .err-desc{font-size:14px;line-height:1.55;color:#86868b;max-width:460px;margin:0 auto 28px}
    .err-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-bottom:36px}
    .err-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:980px;font-size:13px;font-weight:600;letter-spacing:.01em;text-decoration:none;cursor:pointer;transition:all .2s ease;border:none;line-height:1.2}
    .err-btn i{font-size:11px}
    .err-btn-primary{background:var(--err-primary);color:#fff}
    .err-btn-primary:hover{filter:brightness(1.1);color:#fff;transform:translateY(-1px)}
    .err-btn-ghost{background:transparent;color:#1d1d1f;border:1px solid #d2d2d7}
    .err-btn-ghost:hover{background:#f5f5f7;color:#1d1d1f}
    .err-divider{width:36px;height:1px;background:#d2d2d7;margin:0 auto 18px}
    .err-quick{display:flex;justify-content:center;flex-wrap:wrap;gap:6px 18px;font-size:12px}
    .err-quick a{color:#6e6e73;text-decoration:none;font-weight:500;transition:color .2s ease;display:inline-flex;align-items:center;gap:5px}
    .err-quick a:hover{color:var(--err-primary)}
    .err-quick a i{font-size:9px;opacity:.6;transition:transform .2s ease}
    .err-quick a:hover i{transform:translateX(2px)}
    @keyframes errFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
    .err-float{animation:errFloat 5s ease-in-out infinite;will-change:transform}

    @media (max-width:1024px){.err-page{min-height:calc(100vh - 180px);padding:40px 20px}.err-illu{width:96px;height:96px;margin-bottom:18px}.err-illu svg{width:80px;height:80px}.err-pill{margin-bottom:16px}.err-actions{margin-bottom:28px}}
    @media (max-width:640px){.err-page{padding:32px 16px;min-height:calc(100vh - 160px)}.err-wrap{max-width:100%}.err-illu{width:80px;height:80px;margin-bottom:14px}.err-illu svg{width:64px;height:64px}.err-pill{margin-bottom:12px;padding:4px 10px;font-size:10px}.err-title{margin:8px 0 4px}.err-desc{margin-bottom:20px;font-size:13px}.err-actions{margin-bottom:24px;gap:6px}.err-btn{padding:9px 16px;font-size:12px}.err-divider{margin-bottom:14px}.err-quick{gap:4px 14px;font-size:11px}}
    @media (max-width:380px){.err-actions{flex-direction:column;width:100%}.err-btn{width:100%;justify-content:center}}
    @media (prefers-reduced-motion:reduce){.err-float{animation:none}}
</style>
@endpush

@section('content')
<div class="err-page">
    <div class="err-wrap">
        <div class="err-illu err-float">
            @if($cfg['icon']==='compass')<svg viewBox="0 0 88 88" fill="none"><circle cx="44" cy="44" r="38" stroke="currentColor" stroke-opacity=".35" stroke-width="1.5"/><circle cx="44" cy="44" r="30" stroke="currentColor" stroke-opacity=".2" stroke-width="1" stroke-dasharray="2 3"/><circle cx="44" cy="44" r="22" fill="currentColor" fill-opacity=".08" stroke="currentColor" stroke-opacity=".5" stroke-width="1"/><polygon points="44,24 50,44 44,64 38,44" fill="currentColor" stroke="currentColor" stroke-width="1" stroke-linejoin="round"/><polygon points="44,24 50,44 44,44" fill="currentColor" fill-opacity=".7"/><circle cx="44" cy="44" r="3" fill="#fff"/><text x="44" y="16" text-anchor="middle" fill="currentColor" fill-opacity=".5" font-size="7" font-weight="700">N</text></svg>
            @elseif($cfg['icon']==='lock')<svg viewBox="0 0 88 88" fill="none"><rect x="20" y="42" width="48" height="34" rx="7" fill="currentColor" fill-opacity=".12" stroke="currentColor" stroke-width="1.5"/><path d="M30 42 V32 a14 14 0 0 1 28 0 V42" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/><circle cx="44" cy="58" r="3.5" fill="currentColor"/><rect x="42.5" y="58" width="3" height="7" fill="currentColor"/></svg>
            @elseif($cfg['icon']==='clock')<svg viewBox="0 0 88 88" fill="none"><circle cx="44" cy="44" r="36" fill="currentColor" fill-opacity=".08" stroke="currentColor" stroke-width="1.5"/>@for($i=0;$i<12;$i++)<line x1="44" y1="14" x2="44" y2="18" stroke="currentColor" stroke-width="1" transform="rotate({{ $i*30 }} 44 44)"/>@endfor<line x1="44" y1="44" x2="44" y2="26" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="44" y1="44" x2="60" y2="44" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="44" cy="44" r="2.5" fill="currentColor"/></svg>
            @elseif($cfg['icon']==='wrench')<svg viewBox="0 0 88 88" fill="none"><path d="M56 16 a12 12 0 1 0 0 24 l-22 22 a5 5 0 0 0 7 7 l22 -22 a12 12 0 0 0 24 0 a12 12 0 0 0 -19 -9 l-7 7" stroke="currentColor" stroke-width="1.8" fill="currentColor" fill-opacity=".12" stroke-linecap="round" stroke-linejoin="round"/></svg>
            @else<svg viewBox="0 0 88 88" fill="none"><rect x="22" y="32" width="44" height="30" rx="5" fill="currentColor" fill-opacity=".08" stroke="currentColor" stroke-opacity=".4" stroke-width="1.5"/><circle cx="44" cy="47" r="7" fill="none" stroke="currentColor" stroke-opacity=".6" stroke-width="1.8"/><line x1="49" y1="52" x2="56" y2="59" stroke="currentColor" stroke-opacity=".6" stroke-width="1.8" stroke-linecap="round"/></svg>
            @endif
        </div>

        <span class="err-pill"><i class="fas fa-circle-info"></i>Error {{ $cfg['code'] }}</span>
        <h1 class="err-code">{{ $cfg['code'] }}</h1>
        <h2 class="err-title">{{ $cfg['title'] }}</h2>
        <p class="err-subtitle">{{ $cfg['subtitle'] }}</p>
        <p class="err-desc">{{ $cfg['description'] }}</p>

        <div class="err-actions">
            <a href="{{ url('/') }}" class="err-btn err-btn-primary"><i class="fas fa-arrow-left"></i>Back to Home</a>
            <a href="{{ route('pages.contact') }}" class="err-btn err-btn-ghost"><i class="fas fa-life-ring"></i>Get Support</a>
            @if($status==='419' || $status==='503')
                <button type="button" onclick="window.location.reload()" class="err-btn err-btn-ghost"><i class="fas fa-rotate-right"></i>Try Again</button>
            @endif
        </div>

        <div class="err-divider"></div>
        <div class="err-quick">
            <a href="{{ route('rooms.index') }}"><i class="fas fa-chevron-right"></i>Browse Properties</a>
            <a href="{{ route('pages.how-it-works') }}"><i class="fas fa-chevron-right"></i>How It Works</a>
            <a href="{{ route('pages.faq') }}"><i class="fas fa-chevron-right"></i>FAQ</a>
            <a href="{{ route('blogs.index') }}"><i class="fas fa-chevron-right"></i>Blog</a>
        </div>
    </div>
</div>
@endsection
