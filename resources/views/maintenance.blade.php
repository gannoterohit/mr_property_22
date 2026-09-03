<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title }} - {{ \App\Models\Setting::get('website_name', 'ApnaNest') }}</title>
    <style>
        * { box-sizing:border-box; }
        :root { --primary:{{ \App\Models\Setting::get('primary_color', '#4f46e5') }}; --secondary:{{ \App\Models\Setting::get('secondary_color', '#10B981') }}; --ink:#0f172a; --muted:#64748b; }
        body { margin:0; min-height:100vh; color:var(--ink); background:#f8fafc; font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .page { position:relative; min-height:100vh; display:grid; place-items:center; overflow:hidden; padding:40px 20px; }
        .grid { position:absolute; inset:0; opacity:.42; background-image:linear-gradient(#e2e8f0 1px,transparent 1px),linear-gradient(90deg,#e2e8f0 1px,transparent 1px); background-size:42px 42px; mask-image:linear-gradient(to bottom,black,transparent 82%); }
        .glow { position:absolute; width:500px; height:500px; border-radius:999px; filter:blur(100px); opacity:.17; pointer-events:none; }
        .glow.one { top:-260px; left:calc(50% - 380px); background:var(--primary); }
        .glow.two { right:-260px; bottom:-300px; background:var(--secondary); }
        .shell { position:relative; width:min(100%,940px); overflow:hidden; border:1px solid #e2e8f0; border-radius:30px; background:rgba(255,255,255,.94); box-shadow:0 30px 80px -34px rgba(15,23,42,.35); backdrop-filter:blur(12px); }
        .top { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:22px 28px; border-bottom:1px solid #eef2f7; }
        .brand { display:flex; align-items:center; gap:12px; color:var(--ink); text-decoration:none; font-size:18px; font-weight:850; }
        .brand-mark { width:42px; height:42px; display:grid; place-items:center; overflow:hidden; border:1px solid #e2e8f0; border-radius:13px; background:#fff; box-shadow:0 4px 12px rgba(15,23,42,.06); }
        .brand-mark img { width:100%; height:100%; padding:5px; object-fit:contain; }
        .brand-mark svg { width:21px; height:21px; color:var(--primary); }
        .secure { display:flex; align-items:center; gap:8px; color:#64748b; font-size:12px; font-weight:700; }
        .secure svg { width:16px; height:16px; color:var(--secondary); }
        .content { display:grid; grid-template-columns:minmax(0,1.18fr) minmax(300px,.82fr); align-items:center; gap:44px; padding:54px 58px 58px; }
        .eyebrow { display:inline-flex; align-items:center; gap:8px; padding:7px 11px; border:1px solid #fde68a; border-radius:999px; color:#a16207; background:#fffbeb; font-size:11px; line-height:1; font-weight:850; letter-spacing:.09em; text-transform:uppercase; }
        .eyebrow span { width:7px; height:7px; border-radius:99px; background:#f59e0b; box-shadow:0 0 0 5px rgba(245,158,11,.12); }
        h1 { max-width:570px; margin:19px 0 0; font-size:clamp(31px,4.4vw,50px); line-height:1.08; letter-spacing:-.04em; }
        .message { max-width:570px; margin:18px 0 0; color:var(--muted); font-size:16px; line-height:1.75; }
        .reopen { display:flex; align-items:center; gap:12px; width:fit-content; margin-top:23px; padding:12px 15px; border:1px solid #e0e7ff; border-radius:14px; background:#eef2ff; }
        .reopen svg { width:20px; height:20px; color:var(--primary); }
        .reopen small { display:block; color:var(--primary); font-size:9px; font-weight:850; letter-spacing:.1em; text-transform:uppercase; }
        .reopen strong { display:block; margin-top:2px; color:#312e81; font-size:13px; }
        .actions { display:flex; flex-wrap:wrap; gap:11px; margin-top:29px; }
        .actions form { margin:0; }
        .button { min-height:46px; display:inline-flex; align-items:center; justify-content:center; gap:9px; padding:0 19px; border:1px solid transparent; border-radius:13px; font-size:13px; font-weight:800; text-decoration:none; cursor:pointer; transition:.18s ease; }
        .button svg { width:16px; height:16px; }
        .button.primary { color:#fff; background:var(--primary); box-shadow:0 10px 24px -12px var(--primary); }
        .button.primary:hover { transform:translateY(-1px); filter:brightness(.94); }
        .button.secondary { color:#334155; border-color:#dbe3ed; background:#fff; }
        .button.secondary:hover { border-color:#c7d2fe; color:#4338ca; background:#f8faff; }
        .visual { position:relative; min-height:330px; display:grid; place-items:center; }
        .visual-bg { position:absolute; width:292px; height:292px; border-radius:50%; background:linear-gradient(145deg,#eef2ff,#fff7ed); }
        .toolbox { position:relative; width:230px; padding:25px 23px 22px; border:1px solid rgba(255,255,255,.8); border-radius:28px; background:rgba(255,255,255,.8); box-shadow:0 24px 50px -25px rgba(15,23,42,.28); backdrop-filter:blur(10px); }
        .tool-icon { width:82px; height:82px; display:grid; place-items:center; margin:0 auto; border-radius:25px; color:#fff; background:var(--primary); box-shadow:0 18px 30px -17px var(--primary); transform:rotate(-3deg); }
        .tool-icon svg { width:40px; height:40px; }
        .bars { margin-top:25px; display:grid; gap:10px; }
        .bar { height:9px; border-radius:99px; background:#e8edf4; overflow:hidden; }
        .bar:before { content:""; display:block; width:var(--w); height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--primary),#818cf8); }
        .bubble { position:absolute; display:flex; align-items:center; gap:8px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:13px; background:#fff; box-shadow:0 14px 30px -20px rgba(15,23,42,.35); color:#334155; font-size:10px; font-weight:800; }
        .bubble svg { width:15px; height:15px; color:var(--secondary); }
        .bubble.one { top:43px; right:-19px; }
        .bubble.two { left:-25px; bottom:55px; }
        .footer { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:17px 28px; border-top:1px solid #eef2f7; color:#94a3b8; background:#fbfdff; font-size:11px; }
        .footer a { color:#475569; font-weight:750; text-decoration:none; }
        @media(max-width:760px){ .page{padding:16px}.shell{border-radius:23px}.top{padding:17px 18px}.secure{display:none}.content{grid-template-columns:1fr; gap:25px; padding:34px 24px 37px}.visual{min-height:260px; order:-1}.visual-bg{width:235px;height:235px}.toolbox{width:190px;padding:20px}.tool-icon{width:68px;height:68px}.bubble.one{right:-8px}.bubble.two{left:-8px}.actions{display:grid}.actions form,.button{width:100%}.footer{align-items:flex-start;flex-direction:column;padding:15px 19px;gap:6px} }
    </style>
</head>
<body>
@php
    $siteName = \App\Models\Setting::get('website_name', 'ApnaNest');
    $logo = \App\Models\Setting::get('navbar_logo') ?: \App\Models\Setting::get('website_logo');
    $supportEmail = \App\Models\Setting::get('contact_email');
@endphp
<main class="page">
    <div class="grid"></div><div class="glow one"></div><div class="glow two"></div>
    <section class="shell">
        <header class="top">
            <a class="brand" href="{{ url('/') }}"><span class="brand-mark">@if($logo)<img src="{{ \App\Models\Setting::mediaUrl($logo) }}" alt="{{ $siteName }}">@else<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/></svg>@endif</span><span>{{ $siteName }}</span></a>
            <span class="secure"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>System status notice</span>
        </header>
        <div class="content">
            <div>
                <span class="eyebrow"><span></span>Temporarily unavailable</span>
                <h1>{{ $title }}</h1>
                <p class="message">{{ $message }}</p>
                @if($reopeningAt)<div class="reopen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><div><small>Expected reopening</small><strong>{{ \Carbon\Carbon::parse($reopeningAt)->format('d M Y, h:i A') }}</strong></div></div>@endif
                <div class="actions"><button type="button" onclick="window.location.reload()" class="button primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12a8 8 0 1 1-2.34-5.66L20 8"/><path d="M20 3v5h-5"/></svg>Check again</button><a href="{{ route('admin.login-access') }}" class="button secondary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M14 3h6v18h-6"/></svg>Admin login</a></div>
            </div>
            <div class="visual" aria-hidden="true"><div class="visual-bg"></div><div class="toolbox"><div class="tool-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m14.7 6.3 3-3a2.1 2.1 0 0 1 3 3l-3 3"/><path d="m9.3 17.7-3 3a2.1 2.1 0 0 1-3-3l3-3M8 16l8-8M15 15l4 4M5 5l4 4"/></svg></div><div class="bars"><span class="bar" style="--w:82%"></span><span class="bar" style="--w:61%"></span><span class="bar" style="--w:72%"></span></div><span class="bubble one"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>Upgrading safely</span><span class="bubble two"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5"/><path d="M12 16h.01"/></svg>Back shortly</span></div></div>
        </div>
        <footer class="footer"><span>Thank you for your patience while we improve {{ $siteName }}.</span>@if($supportEmail)<span>Need help? <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></span>@endif</footer>
    </section>
</main>
</body>
</html>
