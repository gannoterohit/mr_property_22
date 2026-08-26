@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'My Wallet | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@php
    $role = Auth::user()->role;
    $contentSection = $role === 'owner' ? 'owner-content' : ($role === 'broker' ? 'broker-content' : ($role === 'user' ? 'customer-content' : 'content'));
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('css/owner-dashboard.css') }}">
<style>
.wallet-page-wrap { padding: 2rem 0 3.5rem; }
.wallet-balance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
.wallet-card {
    position: relative; overflow: hidden;
    border-radius: 1.25rem; padding: 1.5rem;
    color: #fff; min-height: 170px;
    display: flex; flex-direction: column; justify-content: space-between;
}
.wallet-card::before {
    content: ''; position: absolute;
    width: 180px; height: 180px;
    border: 48px solid rgba(255,255,255,.08);
    border-radius: 50%; right: -80px; top: -80px;
}
.wallet-card::after {
    content: ''; position: absolute;
    width: 110px; height: 110px;
    border: 30px solid rgba(255,255,255,.06);
    border-radius: 50%; left: -40px; bottom: -40px;
}
.wallet-card.primary { background: linear-gradient(135deg, var(--owner-primary, #4f46e5) 0%, rgba(var(--owner-primary-rgb,79,70,229),.72) 100%); box-shadow: 0 8px 28px rgba(var(--owner-primary-rgb,79,70,229),.28); }
.wallet-card.secondary { background: linear-gradient(135deg, #10b981 0%, #0d9488 100%); box-shadow: 0 8px 28px rgba(16,185,129,.28); }
.wallet-card-icon {
    width: 42px; height: 42px; border-radius: .875rem;
    background: rgba(255,255,255,.18); display: flex;
    align-items: center; justify-content: center; font-size: 1.1rem;
    position: relative; z-index: 1;
}
.wallet-card-label { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.72); position: relative; z-index: 1; margin-top: .4rem; }
.wallet-card-amount { font-size: 2.2rem; font-weight: 900; color: #fff; line-height: 1; position: relative; z-index: 1; }
.wallet-card-note { font-size: .72rem; color: rgba(255,255,255,.68); position: relative; z-index: 1; margin-top: .3rem; max-width: 260px; }

.wallet-body-grid { display: grid; grid-template-columns: minmax(0,1.65fr) minmax(0,.75fr); gap: 1.25rem; align-items: start; }
.wallet-guide-steps { list-style: none; padding: 1.25rem; margin: 0; display: grid; gap: 1rem; }
.wallet-guide-steps li { display: flex; gap: .875rem; align-items: flex-start; }
.wallet-guide-step-num {
    width: 30px; height: 30px; flex-shrink: 0; border-radius: .625rem;
    background: rgba(var(--owner-primary-rgb,79,70,229),.09); color: var(--owner-primary, #4f46e5);
    display: flex; align-items: center; justify-content: center;
    font-size: .7rem; font-weight: 800;
}
.wallet-guide-link {
    display: flex; align-items: center; justify-content: space-between;
    margin: 0 1.25rem 1.25rem; padding: .75rem 1rem; border-radius: .75rem;
    background: #f1f5f9; color: #334155; text-decoration: none;
    font-size: .75rem; font-weight: 700;
    transition: background .15s ease;
}
.wallet-guide-link:hover { background: #e2e8f0; }
@media (max-width: 900px) { .wallet-body-grid { grid-template-columns: 1fr; } }
@media (max-width: 640px) {
    .wallet-balance-grid { grid-template-columns: 1fr; }
    .wallet-card { min-height: 150px; }
    .wallet-card-amount { font-size: 1.8rem; }
}
</style>
@endpush

@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="owner-dashboard-content max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 wallet-page-wrap">

    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 shadow-sm">
            <i class="fas fa-circle-check text-emerald-600 flex-shrink-0"></i>
            <span class="text-sm font-bold text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 shadow-sm">
            <i class="fas fa-circle-exclamation text-red-600 flex-shrink-0"></i>
            <span class="text-sm font-bold text-red-700">{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm font-bold text-red-700 shadow-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Balance Cards --}}
    <div class="wallet-balance-grid">
        <div class="wallet-card primary">
            <div>
                <div class="wallet-card-icon"><i class="fas fa-wallet"></i></div>
                <div class="wallet-card-label">Available Balance</div>
            </div>
            <div>
                <div class="wallet-card-amount">&#8377;{{ number_format((float)($user->wallet_balance ?? 0), 2) }}</div>
                <div class="wallet-card-note">Use for bookings, listing fees & contact unlocks.</div>
            </div>
        </div>
        <div class="wallet-card secondary">
            <div>
                <div class="wallet-card-icon"><i class="fas fa-key"></i></div>
                <div class="wallet-card-label">Free Contact Unlocks</div>
            </div>
            <div>
                <div class="wallet-card-amount">{{ number_format((int)($user->free_unlocks ?? 0)) }}</div>
                <div class="wallet-card-note">Referral credits to view owner numbers for free.</div>
            </div>
        </div>
    </div>

    {{-- Body --}}
    <div class="wallet-body-grid">
        {{-- Guide info --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">Credit & Wallet Guide</h2>
                    <p class="mt-0.5 text-xs text-slate-500">How to use your cash balance and unlock credits.</p>
                </div>
            </div>
            <div class="p-6 space-y-4 text-sm text-slate-600 leading-relaxed">
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"><i class="fas fa-wallet text-xs"></i></span>
                    <div>
                        <p class="font-semibold text-slate-800">Available Balance</p>
                        <p class="text-xs text-slate-500 mt-0.5">Can be used as direct currency. Top it up or earn promotional credits. Selected during checkout as "Wallet Balance" payment option.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fas fa-key text-xs"></i></span>
                    <div>
                        <p class="font-semibold text-slate-800">Free Contact Unlocks</p>
                        <p class="text-xs text-slate-500 mt-0.5">Automatically applied when you view owner details. Each unlock uses 1 credit. Once exhausted, pay via wallet or payment gateway.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Referral steps --}}
        <div class="owner-dashboard-panel rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="font-bold text-slate-950 text-sm">How referral credits work</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Three simple steps.</p>
                </div>
            </div>
            <ul class="wallet-guide-steps">
                @foreach([
                    ['Share link','Invite friends through your referral link.','fa-share-nodes','indigo'],
                    ['Friend joins','Credit awarded after they register.','fa-user-plus','emerald'],
                    ['Get free unlock','Use credit directly on any room page.','fa-unlock','violet'],
                ] as $i => [$title,$desc,$icon,$color])
                <li>
                    <div class="wallet-guide-step-num">{{ $i+1 }}</div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800">{{ $title }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $desc }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
            <a href="{{ route('referral.index') }}" class="wallet-guide-link">
                <span>Open Refer &amp; Earn</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</div>
@endsection
