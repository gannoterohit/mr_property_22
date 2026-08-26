@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title', 'My Wallet | ' . \App\Models\Setting::get('website_name', 'RoomRental'))
@php $contentSection = Auth::user()->role === 'owner' ? 'owner-content' : (Auth::user()->role === 'broker' ? 'broker-content' : (Auth::user()->role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    @if(session('success'))
        <div class="account-flash success"><i class="fas fa-circle-check mr-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="account-flash error"><i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="account-flash error">{{ $errors->first() }}</div>
    @endif

    <section class="wallet-summary">
        <article class="wallet-balance primary">
            <div class="wallet-card-top">
                <span><i class="fas fa-wallet"></i></span>
                <small>Available balance</small>
            </div>
            <strong>&#8377;{{ number_format((float)($user->wallet_balance??0),2) }}</strong>
            <p>Use this balance for direct room bookings, listing fee payments, and unlocks.</p>
        </article>
        <article class="wallet-balance indigo-gradient">
            <div class="wallet-card-top">
                <span><i class="fas fa-key"></i></span>
                <small>Free Contact Unlocks</small>
            </div>
            <strong>{{ number_format((int)($user->free_unlocks??0)) }}</strong>
            <p>Referral credits. Use these to view owner phone numbers without paying.</p>
        </article>
    </section>
    
    <section class="wallet-layout">
        <article class="account-card wallet-history">
            <div class="account-card-head">
                <div>
                    <h2>Credit & Wallet Guide</h2>
                    <p>Information on using your cash balance and unlock credits.</p>
                </div>
            </div>
            <div class="p-6 space-y-4 text-sm text-slate-600 leading-relaxed">
                <p>
                    Your <strong>Available Balance</strong> can be used as direct currency on ApnaNest. You can top it up or earn promotional credits. It is used during checkout when selecting "Wallet Balance" payment option.
                </p>
                <p>
                    Your <strong>Free Contact Unlocks</strong> are automatically applied when you view owner details. Each unlock reduces your credit by 1. Once your free credits are used, you can pay using your wallet balance or online payment gateway.
                </p>
            </div>
        </article>
        
        <aside class="account-card wallet-guide">
            <div class="account-card-head">
                <div>
                    <h2>How referral credits work</h2>
                    <p>Three simple steps.</p>
                </div>
            </div>
            <ol>
                <li>
                    <b>1</b>
                    <span>
                        <strong>Share link</strong>
                        <small>Invite friends through your referral link.</small>
                    </span>
                </li>
                <li>
                    <b>2</b>
                    <span>
                        <strong>Friend joins</strong>
                        <small>Credit is awarded after they register.</small>
                    </span>
                </li>
                <li>
                    <b>3</b>
                    <span>
                        <strong>Get free unlock</strong>
                        <small>Use credit directly on any room page.</small>
                    </span>
                </li>
            </ol>
            <a href="{{ route('referral.index') }}" class="wallet-guide-link">Open Refer & Earn <i class="fas fa-arrow-right"></i></a>
        </aside>
    </section>
</div>
@include('account.partials.page-styles')
<link rel="stylesheet" href="{{ asset('css/account-wallet.css') }}">
@endsection
