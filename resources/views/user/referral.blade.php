@extends('layouts.app')
@section('title','Refer & Earn | ApnaNest')
@section('content')
<div class="{{ $user->role==='owner'?'owner-workspace flex':'' }} min-h-screen">
    @if($user->role==='owner') @include('owner.partials.sidebar',['active'=>'referral']) @endif
    <main class="account-main">
        <header class="account-header">
            <div class="account-container">
                <div>
                    <span class="account-eyebrow">Rewards program</span>
                    <h1>Refer & Earn</h1>
                    <p>Invite friends to ApnaNest and get free contact unlocks when they join.</p>
                </div>
            </div>
        </header>
        <div class="account-container account-body">
            <section class="referral-hero">
                <div class="referral-copy">
                    <span>1 Referral = 1 Free Unlock</span>
                    <h2>Share ApnaNest.<br>Unlock Contacts Free.</h2>
                    <p>Your friend receives 1 Free Contact Unlock joining bonus, and you receive 1 Free Contact Unlock after they sign up.</p>
                    <div class="reward-pair">
                        <div>
                            <small>Your reward</small>
                            <strong>+1 Free Unlock</strong>
                        </div>
                        <i class="fas fa-arrow-right"></i>
                        <div>
                            <small>Friend receives</small>
                            <strong>+1 Free Unlock</strong>
                        </div>
                    </div>
                </div>
                <div class="share-box">
                    <label>Your personal referral link</label>
                    <div class="share-input">
                        <input id="referralLink" readonly value="{{ $referralLink }}">
                        <button type="button" id="copy-referral" onclick="copyLink()">
                            <i class="far fa-copy"></i><span>Copy</span>
                        </button>
                    </div>
                    <p id="copy-status" aria-live="polite"></p>
                    <a href="https://wa.me/?text={{ urlencode('Join ApnaNest using my referral link and get 1 Free Contact Unlock to connect with home owners: '.$referralLink) }}" target="_blank" rel="noopener">
                        <i class="fa-brands fa-whatsapp"></i> Share on WhatsApp
                    </a>
                </div>
            </section>
            
            <section class="referral-stats">
                @foreach([
                    ['Available Free Unlocks', number_format((int)($user->free_unlocks??0)), 'fa-key', 'indigo'],
                    ['Friends joined', $referrals->count(), 'fa-user-group', 'green'],
                    ['Total Earned Unlocks', $referrals->count(), 'fa-gift', 'blue']
                ] as $stat)
                    <article class="account-card">
                        <span class="{{ $stat[3] }} bg-{{ $stat[3] }}-50 text-{{ $stat[3] }}-600">
                            <i class="fas {{ $stat[2] }}"></i>
                        </span>
                        <div>
                            <small class="text-slate-500 text-xs">{{ $stat[0] }}</small>
                            <strong class="text-slate-900 font-extrabold text-xl">{{ $stat[1] }}</strong>
                        </div>
                    </article>
                @endforeach
            </section>
            
            <section class="referral-layout">
                <article class="account-card">
                    <div class="account-card-head">
                        <div>
                            <h2>Referral history</h2>
                            <p>Friends registered using your personal link.</p>
                        </div>
                        <b class="history-count">{{ $referrals->count() }} total</b>
                    </div>
                    <div class="history-list">
                        @forelse($referrals as $referral)
                            <div class="history-row">
                                <span class="history-avatar">{{ strtoupper(substr($referral->name,0,1)) }}</span>
                                <div>
                                    <strong>{{ $referral->name }}</strong>
                                    <small>Joined {{ $referral->created_at->format('d M Y') }}</small>
                                </div>
                                <b>+1 Free Unlock</b>
                            </div>
                        @empty
                            <div class="account-empty">
                                <span><i class="fas fa-user-group"></i></span>
                                <h2>No referrals yet</h2>
                                <p>Copy your personal link or share it on WhatsApp to invite your first friend.</p>
                            </div>
                        @endforelse
                    </div>
                </article>
                
                <aside class="account-card referral-guide">
                    <div class="account-card-head">
                        <div>
                            <h2>How it works</h2>
                            <p>Referral rewards in three steps.</p>
                        </div>
                    </div>
                    <ol>
                        <li>
                            <b>1</b>
                            <span>
                                <strong>Share your link</strong>
                                <small>Send it only to friends looking for rooms or apartments.</small>
                            </span>
                        </li>
                        <li>
                            <b>2</b>
                            <span>
                                <strong>Your friend joins</strong>
                                <small>They must register and verify via OTP using your link.</small>
                            </span>
                        </li>
                        <li>
                            <b>3</b>
                            <span>
                                <strong>Get rewarded</strong>
                                <small>You both get 1 Free Contact Unlock immediately.</small>
                            </span>
                        </li>
                    </ol>
                    <div class="referral-note">
                        <i class="fas fa-shield-halved"></i>
                        <span>
                            <strong>Fair-use protection</strong>
                            <small>Duplicate or fake accounts will be blocked.</small>
                        </span>
                    </div>
                </aside>
            </section>
        </div>
    </main>
</div>
@include('user.partials.page-styles')
<style>
.referral-hero{position:relative;overflow:hidden;display:grid;grid-template-columns:1.15fr .85fr;gap:35px;padding:32px;border-radius:20px;background:linear-gradient(135deg,#0f172a,var(--primary));color:#fff}.referral-hero:after{content:"";position:absolute;width:280px;height:280px;border:60px solid rgba(var(--primary-rgb),.09);border-radius:50%;right:-150px;top:-150px}.referral-copy,.share-box{position:relative;z-index:1}.referral-copy>span{display:inline-block;padding:6px 9px;border-radius:999px;background:rgba(var(--primary-rgb),.18);color:rgba(255,255,255,.78);font-size:8px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}.referral-copy h2{margin:14px 0 9px;font-size:27px;line-height:1.18;letter-spacing:-.8px}.referral-copy>p{max-width:520px;margin:0;color:#cbd5e1;font-size:10px;line-height:1.65}.reward-pair{display:flex;align-items:center;gap:10px;margin-top:23px}.reward-pair>div{display:grid;padding:9px 12px;border-radius:10px;background:rgba(255,255,255,.08)}.reward-pair small{color:#94a3b8;font-size:8px}.reward-pair strong{font-size:14px}.reward-pair>i{color:rgba(255,255,255,.72)}.share-box{align-self:center;padding:20px;border:1px solid rgba(255,255,255,.13);border-radius:15px;background:rgba(255,255,255,.07)}.share-box>label{display:block;color:#cbd5e1;font-size:9px;font-weight:800;margin-bottom:8px}.share-input{display:flex;padding:5px;border-radius:10px;background:#fff}.share-input input{min-width:0;flex:1;border:0;outline:none;padding:0 9px;color:#475569;font-size:9px}.share-input button{border:0;border-radius:8px;padding:10px 12px;background:var(--primary);color:#fff;font-size:9px;font-weight:800;cursor:pointer}.share-input button i{margin-right:5px}.share-box>p{height:13px;margin:6px 0 2px;color:#86efac;font-size:8px}.share-box>a{display:flex;align-items:center;justify-content:center;gap:7px;padding:11px;border-radius:9px;background:#16a34a;color:#fff;text-decoration:none;font-size:10px;font-weight:800}.referral-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:17px}.referral-stats article{display:flex;align-items:center;gap:12px;padding:16px}.referral-stats article>span{width:39px;height:39px;display:grid;place-items:center;border-radius:11px}.referral-stats article>span.indigo { background: rgba(var(--primary-rgb),.1); color: var(--primary); }
.referral-stats article>span.green { background: #dcfce7; color: #16a34a; }
.referral-stats article>span.blue { background: rgba(var(--primary-rgb),.1); color: var(--primary); }
.referral-stats article>div{display:grid;gap:3px}.referral-stats small{color:#64748b;font-size:9px}.referral-stats strong{color:#0f172a;font-size:18px}.referral-layout{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(280px,.7fr);gap:17px;margin-top:17px;align-items:start}.history-count{padding:5px 8px;border-radius:7px;background:#f1f5f9;color:#64748b;font-size:8px}.history-row{display:grid;grid-template-columns:38px 1fr auto;align-items:center;gap:11px;padding:13px 19px;border-bottom:1px solid #f1f5f9}.history-avatar{width:36px;height:36px;display:grid;place-items:center;border-radius:10px;background:rgba(var(--primary-rgb),.08);color:var(--primary);font-size:11px;font-weight:900}.history-row>div{display:grid;gap:2px}.history-row>div strong{color:#334155;font-size:10px}.history-row>div small{color:#94a3b8;font-size:8px}.history-row>b{padding:5px 7px;border-radius:7px;background:#ecfdf5;color:#047857;font-size:8px}.referral-guide ol{display:grid;gap:16px;padding:19px;margin:0}.referral-guide li{display:flex;gap:10px}.referral-guide li>b{width:27px;height:27px;display:grid;place-items:center;flex:none;border-radius:9px;background:rgba(var(--primary-rgb),.08);color:var(--primary);font-size:9px}.referral-guide li>span{display:grid;gap:3px}.referral-guide li strong{color:#334155;font-size:10px}.referral-guide li small{color:#94a3b8;font-size:8px;line-height:1.45}.referral-note{display:flex;gap:9px;margin:0 19px 19px;padding:11px;border-radius:10px;background:#fffbeb;color:#b45309}.referral-note>span{display:grid}.referral-note strong{font-size:9px}.referral-note small{font-size:8px}@media(max-width:900px){.referral-hero,.referral-layout{grid-template-columns:1fr}}@media(max-width:620px){.referral-hero{padding:22px}.referral-stats{grid-template-columns:1fr}.history-row{grid-template-columns:36px 1fr}.history-row>b{grid-column:2;width:max-content}.referral-copy h2{font-size:23px}}</style>
<script>async function copyLink(){const input=document.getElementById('referralLink'),status=document.getElementById('copy-status');try{await navigator.clipboard.writeText(input.value)}catch(e){input.select();document.execCommand('copy')}status.textContent='Referral link copied to clipboard.';const button=document.getElementById('copy-referral');button.querySelector('span').textContent='Copied';setTimeout(()=>{status.textContent='';button.querySelector('span').textContent='Copy'},2200)}</script>
@endsection
