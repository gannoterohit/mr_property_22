@extends('layouts.app')

@section('title', 'How It Works - ' . \App\Models\Setting::get('website_name', 'ApnaNest'))
@section('description', 'Learn how users find rooms and unlock owner contacts, and how property owners list rooms on ApnaNest.')

@push('styles')
<style>
    .hiw-hero { background:linear-gradient(135deg,#0f172a 0%,rgba(var(--primary-rgb),.9) 62%,rgba(var(--secondary-rgb),.82) 100%); }
    .hiw-step { transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease; }
    .hiw-step:hover { transform:translateY(-4px); border-color:rgba(var(--primary-rgb),.35); box-shadow:0 18px 35px -28px rgba(15,23,42,.6); }
    .hiw-primary-cta { background:var(--primary); color:#fff; }
    .hiw-secondary-cta { background:#fff; color:#0f172a; border:1px solid rgba(255,255,255,.8); }
    .hiw-seeker-step { border-top:3px solid var(--primary); }
    .hiw-owner-step { border-top:3px solid var(--secondary); transition:transform .22s ease,box-shadow .22s ease; }
    .hiw-owner-step:hover { transform:translateY(-4px); box-shadow:0 18px 35px -28px rgba(15,23,42,.6); }
    .hiw-owner-section article { border-top:3px solid var(--secondary); transition:transform .22s ease,box-shadow .22s ease; }
    .hiw-owner-section article:hover { transform:translateY(-4px); box-shadow:0 18px 35px -28px rgba(15,23,42,.6); }
    .hiw-feature-tile { border:1px solid rgba(255,255,255,.09); background:rgba(255,255,255,.08); }
    .hiw-primary-text { color: var(--primary); }
    .hiw-secondary-text { color: var(--secondary); }
    .hiw-primary-soft { background: rgba(var(--primary-rgb), .12); color: var(--primary); }
    .hiw-secondary-soft { background: rgba(var(--secondary-rgb), .12); color: var(--secondary); }
    .hiw-hero-soft-icon { background: rgba(var(--primary-rgb), .2); color: rgba(255,255,255,.88); }
</style>
@endpush

@section('content')
<section class="hiw-hero text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 lg:py-20 grid lg:grid-cols-2 gap-10 items-center">
        <div><span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white/80"><i class="fas fa-route hiw-secondary-text"></i>Simple and transparent process</span><h1 class="mt-5 text-4xl lg:text-5xl font-extrabold tracking-tight">Find the right room.<br><span class="hiw-secondary-text">Connect directly.</span></h1><p class="mt-4 max-w-xl text-sm sm:text-base leading-7 text-slate-300">ApnaNest helps users discover room, PG and rental listings and unlock verified owner contact details. Owners can publish and manage listings from one dashboard.</p><div class="mt-7 flex flex-wrap gap-3"><a href="{{ route('rooms.index') }}" class="hiw-primary-cta rounded-xl px-5 py-3 text-sm font-bold"><i class="fas fa-search mr-2"></i>Browse Rooms</a><a href="{{ route('register',['role'=>'owner']) }}" class="hiw-secondary-cta rounded-xl px-5 py-3 text-sm font-bold"><i class="fas fa-plus mr-2 hiw-primary-text"></i>List a Room</a></div></div>
        <div class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm"><div class="grid grid-cols-2 gap-3">@foreach([['fa-house','Room & PG listings'],['fa-circle-check','Admin reviewed'],['fa-address-card','Contact unlock'],['fa-comments','Direct conversation']] as $item)<div class="hiw-feature-tile rounded-xl p-4"><span class="hiw-hero-soft-icon flex h-9 w-9 items-center justify-center rounded-lg"><i class="fas {{ $item[0] }}"></i></span><p class="mt-3 text-sm font-bold text-white">{{ $item[1] }}</p></div>@endforeach</div></div>
    </div>
</section>

<section class="bg-white py-10 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="text-center max-w-2xl mx-auto"><span class="hiw-primary-text text-xs font-bold uppercase tracking-wider">For room seekers</span><h2 class="mt-2 text-3xl font-extrabold text-slate-950">From search to owner contact</h2><p class="mt-2 text-sm text-slate-500">No booking confusion. Choose a room, unlock the contact and speak directly with the owner.</p></div>
        <div class="mt-9 grid md:grid-cols-3 gap-5">
            @foreach([['01','fa-magnifying-glass','Search and compare','Filter rooms by city, rent, property type and tenant preference. Review photos, amenities and location.'],['02','fa-lock-open','Unlock owner contact','Use a contact-plan credit, wallet balance or single online payment to reveal the complete owner number.'],['03','fa-phone','Call, visit and finalize','Contact the owner directly, schedule a visit and independently confirm rent, deposit and rental terms.']] as $step)
                <article class="hiw-step hiw-seeker-step relative rounded-2xl border border-slate-200 bg-white p-5"><div class="flex items-center justify-between"><span class="hiw-primary-text text-xs font-extrabold">STEP {{ $step[0] }}</span><span class="hiw-primary-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas {{ $step[1] }}"></i></span></div><h3 class="mt-4 font-bold text-slate-950">{{ $step[2] }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">{{ $step[3] }}</p></article>
            @endforeach
        </div>
    </div>
</section>

<section class="hiw-owner-section bg-slate-50 border-y border-slate-200 py-10 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-[360px_1fr] gap-8 items-start"><div><span class="hiw-secondary-text text-xs font-bold uppercase tracking-wider">For property owners</span><h2 class="mt-2 text-3xl font-extrabold text-slate-950">List and manage your rooms</h2><p class="mt-3 text-sm leading-6 text-slate-500">Use a listing plan or single listing payment. Your room becomes public after entitlement validation and admin approval.</p><a href="{{ route('plans') }}" class="hiw-primary-text mt-5 inline-flex items-center text-sm font-bold">View listing plans <i class="fas fa-arrow-right ml-2"></i></a></div><div class="grid sm:grid-cols-3 gap-4">@foreach([['fa-file-pen','Create listing','Add pricing, room details, amenities, location, photos and preferred tenant.'],['fa-credit-card','Activate listing','Use an owner listing credit, wallet balance or online payment.'],['fa-chart-line','Manage availability','Mark a room rented to hide it, then reactivate it after current plan/payment validation.']] as $item)<article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><span class="hiw-secondary-soft flex h-10 w-10 items-center justify-center rounded-xl"><i class="fas {{ $item[0] }}"></i></span><h3 class="mt-4 font-bold text-slate-950">{{ $item[1] }}</h3><p class="mt-2 text-sm leading-6 text-slate-500">{{ $item[2] }}</p></article>@endforeach</div></div>
</section>

<section class="bg-white py-10">
    @php $safetyTipsLive = \App\Models\CmsPage::published()->where('slug', 'safety-tips')->exists(); @endphp
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"><div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 flex flex-col md:flex-row md:items-center gap-5"><span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"><i class="fas fa-shield-halved"></i></span><div class="flex-1"><h2 class="font-bold text-slate-950">Visit and verify before finalizing</h2><p class="mt-1 text-sm leading-6 text-slate-600">ApnaNest provides listing discovery and contact access. Always visit the property, verify owner identity/documents and agree on rent, deposit and terms before paying the owner.</p></div>@if($safetyTipsLive)<a href="{{ route('pages.safety-tips') }}" class="shrink-0 rounded-xl bg-amber-600 px-5 py-3 text-sm font-bold text-white hover:bg-amber-700">Safety Tips</a>@endif</div></div>
</section>

@endsection
