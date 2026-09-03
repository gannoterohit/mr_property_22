@extends(Auth::user()->role === 'owner' ? 'layouts.owner' : (Auth::user()->role === 'broker' ? 'layouts.agent' : (Auth::user()->role === 'user' ? 'layouts.customer' : 'layouts.public')))
@section('title','Refer & Earn | ' . \App\Models\Setting::get('website_name', 'ApnaNest'))
@if(Auth::user()->role === 'owner')
    @push('styles')
    <link rel="stylesheet" href="{{ asset('css/owner-theme.css') }}">
    @endpush
@endif
@php $contentSection = Auth::user()->role === 'owner' ? 'owner-content' : (Auth::user()->role === 'broker' ? 'broker-content' : (Auth::user()->role === 'user' ? 'customer-content' : 'content')); @endphp
@section($contentSection)
@php $user = Auth::user(); @endphp
<div class="account-container account-body">
    <section class="referral-hero">
        <div class="referral-copy">
            <span><i class="fas fa-bolt"></i> 1 Referral = 1 Free Unlock</span>
            <h2>Share {{ \App\Models\Setting::get('website_name', 'ApnaNest') }}.<br>Unlock Contacts Free.</h2>
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
            <a href="https://wa.me/?text={{ urlencode('Join ' . \App\Models\Setting::get('website_name', 'ApnaNest') . ' using my referral link and get 1 Free Contact Unlock to connect with home owners: ' . $referralLink) }}" target="_blank" rel="noopener">
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
            <article>
                <span class="{{ $stat[3] }} bg-{{ $stat[3] }}-50 text-{{ $stat[3] }}-600">
                    <i class="fas {{ $stat[2] }}"></i>
                </span>
                <div>
                    <small>{{ $stat[0] }}</small>
                    <strong>{{ $stat[1] }}</strong>
                </div>
            </article>
        @endforeach
    </section>
    
    <section class="referral-layout">
        <article class="account-card referral-history">
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
@include('account.partials.page-styles')
<link rel="stylesheet" href="{{ asset('css/account-referral.css') }}">
<script>async function copyLink(){const input=document.getElementById('referralLink'),status=document.getElementById('copy-status');try{await navigator.clipboard.writeText(input.value)}catch(e){input.select();document.execCommand('copy')}status.textContent='Referral link copied to clipboard.';const button=document.getElementById('copy-referral');button.querySelector('span').textContent='Copied';setTimeout(()=>{status.textContent='';button.querySelector('span').textContent='Copy'},2200)}</script>
@endsection
