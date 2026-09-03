@extends('layouts.public')

@section('title', ($title ?? 'How It Works') . ' - ' . \App\Models\Setting::get('website_name', 'ApnaNest'))
@section('description', $metaDescription ?: 'Learn how users find properties and unlock owner contacts, and how property owners list and manage properties.')

@push('styles')
<style>
    .hiw-cms-page{background:#fff;color:#0f172a}.hiw-cms-hero{background:linear-gradient(135deg,#0f172a 0%,rgba(var(--primary-rgb),.9) 62%,rgba(var(--secondary-rgb),.82) 100%);color:#fff}.hiw-cms-wrap{width:min(1180px,calc(100% - 32px));margin:0 auto}.hiw-cms-hero-inner{display:grid;grid-template-columns:1.1fr .9fr;gap:42px;align-items:center;padding:58px 0}.hiw-cms-eyebrow{display:inline-flex;align-items:center;gap:8px;border-radius:999px;background:rgba(255,255,255,.1);padding:7px 12px;color:rgba(255,255,255,.82);font-size:12px;font-weight:800}.hiw-cms-hero h1{margin:20px 0 14px;font-size:48px;line-height:1.05;font-weight:900;letter-spacing:-1px}.hiw-cms-hero h1 span{color:var(--secondary)}.hiw-cms-hero p{max-width:620px;color:#cbd5e1;font-size:16px;line-height:1.75}.hiw-cms-actions{display:flex;flex-wrap:wrap;gap:12px;margin-top:26px}.hiw-cms-actions a{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:13px 18px;font-size:13px;font-weight:850;text-decoration:none}.hiw-cms-actions a:first-child{background:var(--primary);color:#fff}.hiw-cms-actions a:last-child{background:#fff;color:#0f172a}.hiw-cms-feature-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.hiw-cms-feature{border:1px solid rgba(255,255,255,.12);border-radius:16px;background:rgba(255,255,255,.08);padding:18px}.hiw-cms-feature i{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:rgba(var(--primary-rgb),.22);color:#fff}.hiw-cms-feature strong{display:block;margin-top:12px;color:#fff;font-size:14px}.hiw-cms-section{padding:48px 0}.hiw-cms-section.is-soft{background:#f8fafc;border-top:1px solid #e2e8f0;border-bottom:1px solid #e2e8f0}.hiw-cms-section-head{max-width:720px;margin:0 auto 28px;text-align:center}.hiw-cms-section-head span,.hiw-cms-kicker{color:var(--primary);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.12em}.hiw-cms-section h2{margin:8px 0 8px;color:#0f172a;font-size:30px;line-height:1.2;font-weight:900}.hiw-cms-section-head p{color:#64748b;font-size:14px;line-height:1.7}.hiw-cms-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.hiw-cms-card{border:1px solid #e2e8f0;border-radius:16px;background:#fff;padding:22px;box-shadow:0 8px 24px rgba(15,23,42,.04)}.hiw-cms-card.step{border-top:3px solid var(--primary)}.hiw-cms-card.owner{border-top:3px solid var(--secondary)}.hiw-cms-card small{color:var(--primary);font-size:11px;font-weight:900;text-transform:uppercase}.hiw-cms-card i{display:grid;place-items:center;width:42px;height:42px;border-radius:12px;background:rgba(var(--primary-rgb),.1);color:var(--primary)}.hiw-cms-card.owner i{background:rgba(var(--secondary-rgb),.12);color:var(--secondary)}.hiw-cms-card h3{margin:16px 0 8px;color:#0f172a;font-size:17px;font-weight:850}.hiw-cms-card p{color:#64748b;font-size:14px;line-height:1.7}.hiw-cms-owner{display:grid;grid-template-columns:340px 1fr;gap:28px;align-items:start}.hiw-cms-owner-copy p{color:#64748b;font-size:14px;line-height:1.7}.hiw-cms-owner-copy a{display:inline-flex;align-items:center;gap:8px;margin-top:16px;color:var(--primary);font-size:13px;font-weight:850;text-decoration:none}.hiw-cms-alert{display:flex;gap:18px;align-items:center;border:1px solid #fcd34d;border-radius:18px;background:#fffbeb;padding:22px}.hiw-cms-alert i{display:grid;place-items:center;flex:none;width:48px;height:48px;border-radius:14px;background:#fef3c7;color:#b45309}.hiw-cms-alert h2{margin:0 0 5px;font-size:20px}.hiw-cms-alert p{margin:0;color:#475569;font-size:14px;line-height:1.7}.hiw-cms-alert a{margin-left:auto;flex:none;border-radius:12px;background:#d97706;color:#fff;padding:12px 16px;font-size:13px;font-weight:850;text-decoration:none}@media(max-width:899px){.hiw-cms-hero-inner,.hiw-cms-owner{grid-template-columns:1fr}.hiw-cms-hero h1{font-size:38px}.hiw-cms-grid{grid-template-columns:1fr}.hiw-cms-alert{align-items:flex-start;flex-direction:column}.hiw-cms-alert a{margin-left:0}}@media(max-width:599px){.hiw-cms-wrap{width:calc(100% - 24px)}.hiw-cms-hero-inner{padding:42px 0}.hiw-cms-hero h1{font-size:32px}.hiw-cms-feature-grid{grid-template-columns:1fr}.hiw-cms-section{padding:38px 0}.hiw-cms-section h2{font-size:25px}}
</style>
@endpush

@section('content')
<main class="hiw-cms-page">
    <section class="hiw-cms-hero">
        <div class="hiw-cms-wrap hiw-cms-hero-inner">
            <div>
                <span class="hiw-cms-eyebrow"><i class="fas fa-route"></i>{{ \App\Models\Setting::get('hiw_hero_eyebrow', 'Simple and transparent process') }}</span>
                <h1>{{ \App\Models\Setting::get('hiw_hero_title', 'Find the right property.') }}<br><span>{{ \App\Models\Setting::get('hiw_hero_highlight', 'Connect directly.') }}</span></h1>
                <p>{{ \App\Models\Setting::get('hiw_hero_description', '') }}</p>
                <div class="hiw-cms-actions">
                    <a href="{{ route('rooms.index') }}"><i class="fas fa-search"></i>{{ \App\Models\Setting::get('hiw_primary_button_label', 'Browse Properties') }}</a>
                    <a href="{{ route('register', ['role' => 'owner']) }}"><i class="fas fa-plus"></i>{{ \App\Models\Setting::get('hiw_secondary_button_label', 'List a Property') }}</a>
                </div>
            </div>
            <div class="hiw-cms-feature-grid">
                @foreach(($items['hero_feature'] ?? collect()) as $item)
                    <div class="hiw-cms-feature"><i class="fas {{ $item->icon }}"></i><strong>{{ $item->title }}</strong></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="hiw-cms-section">
        <div class="hiw-cms-wrap">
            <div class="hiw-cms-section-head">
                <span>{{ \App\Models\Setting::get('hiw_seeker_eyebrow', 'For property seekers') }}</span>
                <h2>{{ \App\Models\Setting::get('hiw_seeker_title', 'From search to owner contact') }}</h2>
                <p>{{ \App\Models\Setting::get('hiw_seeker_description', '') }}</p>
            </div>
            <div class="hiw-cms-grid">
                @foreach(($items['seeker_step'] ?? collect()) as $item)
                    <article class="hiw-cms-card step"><small>{{ $item->badge }}</small><i class="fas {{ $item->icon }}"></i><h3>{{ $item->title }}</h3><p>{{ $item->description }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="hiw-cms-section is-soft">
        <div class="hiw-cms-wrap hiw-cms-owner">
            <div class="hiw-cms-owner-copy">
                <span class="hiw-cms-kicker">{{ \App\Models\Setting::get('hiw_owner_eyebrow', 'For property owners') }}</span>
                <h2>{{ \App\Models\Setting::get('hiw_owner_title', 'List and manage your properties') }}</h2>
                <p>{{ \App\Models\Setting::get('hiw_owner_description', '') }}</p>
                <a href="{{ route('plans') }}">{{ \App\Models\Setting::get('hiw_owner_button_label', 'View listing plans') }} <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hiw-cms-grid">
                @foreach(($items['owner_step'] ?? collect()) as $item)
                    <article class="hiw-cms-card owner"><i class="fas {{ $item->icon }}"></i><h3>{{ $item->title }}</h3><p>{{ $item->description }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="hiw-cms-section">
        <div class="hiw-cms-wrap">
            <div class="hiw-cms-alert">
                <i class="fas fa-shield-halved"></i>
                <div>
                    <h2>{{ \App\Models\Setting::get('hiw_safety_title', 'Visit and verify before finalizing') }}</h2>
                    <p>{{ \App\Models\Setting::get('hiw_safety_description', '') }}</p>
                </div>
                <a href="{{ route('pages.safety-tips') }}">{{ \App\Models\Setting::get('hiw_safety_button_label', 'Safety Tips') }}</a>
            </div>
        </div>
    </section>
</main>
@endsection
