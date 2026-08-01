@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : 'layouts.public')
@section('title','My Wallet | ApnaNest')
@section('content')
<div class="{{ $user->role==='owner'?'owner-workspace flex':'' }} min-h-screen">
    @if($user->role==='owner') @include('owner.partials.sidebar',['active'=>'wallet']) @endif
    <main class="account-main">
        <header class="account-header">
            <div class="account-container">
                <div>
                    <span class="account-eyebrow">Payments and credits</span>
                    <h1>My Wallet</h1>
                    <p>Track cash balance and referral unlock credits.</p>
                </div>
                <a href="{{ route('referral.index') }}" class="account-action">
                    <i class="fas fa-gift"></i> Earn Free Unlocks
                </a>
            </div>
        </header>
        
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
    </main>
</div>
@include('account.partials.page-styles')
<style>
.wallet-summary{display:grid;grid-template-columns:1fr 1fr;gap:16px}.wallet-balance{position:relative;overflow:hidden;border-radius:18px;padding:23px;color:#fff;min-height:190px}.wallet-balance:after{content:"";position:absolute;width:170px;height:170px;border:45px solid rgba(255,255,255,.08);border-radius:50%;right:-100px;top:-100px}.wallet-balance.primary{background:linear-gradient(135deg,var(--primary),var(--primary-dark))}.wallet-balance.indigo-gradient{background:linear-gradient(135deg,var(--primary),var(--secondary))}.wallet-card-top{display:flex;align-items:center;justify-content:space-between}.wallet-card-top>span{width:40px;height:40px;display:grid;place-items:center;border-radius:11px;background:rgba(255,255,255,.14)}.wallet-card-top small{text-transform:uppercase;letter-spacing:.08em;font-size:9px;font-weight:800;color:rgba(255,255,255,.72)}.wallet-balance>strong{display:block;margin-top:24px;font-size:34px;line-height:1}.wallet-balance>p{margin:11px 0 0;max-width:400px;color:rgba(255,255,255,.72);font-size:10px}.wallet-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(280px,.75fr);gap:18px;margin-top:18px;align-items:start}.wallet-guide ol{display:grid;gap:17px;padding:20px;margin:0}.wallet-guide li{display:flex;gap:11px}.wallet-guide li>b{width:28px;height:28px;display:grid;place-items:center;flex:none;border-radius:9px;background:rgba(var(--primary-rgb),.08);color:var(--primary);font-size:10px}.wallet-guide li>span{display:grid;gap:3px}.wallet-guide li strong{color:#334155;font-size:11px}.wallet-guide li small{color:#94a3b8;font-size:9px}.wallet-guide-link{display:flex;justify-content:space-between;margin:0 20px 20px;padding:11px;border-radius:9px;background:#f1f5f9;color:#334155;text-decoration:none;font-size:10px;font-weight:800}@media(max-width:900px){.wallet-layout{grid-template-columns:1fr}}@media(max-width:620px){.wallet-summary{grid-template-columns:1fr}.wallet-balance{min-height:165px}}
</style>
@endsection
