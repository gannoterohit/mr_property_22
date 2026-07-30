@extends('layouts.app')
@section('title', 'My Dashboard | ApnaNest')

@section('content')
@php
    $user = Auth::user();
    $unlockedCount = \App\Models\Enquiry::where('user_id', $user->id)->where('unlocked', true)->count();
    $wishlistCount = \App\Models\Wishlist::where('user_id', $user->id)->count();
    $firstName = explode(' ', trim($user->name))[0] ?: 'there';
@endphp
<div class="min-h-screen bg-slate-50">
    <main class="user-dashboard-main pb-20 lg:pb-12">
        <header class="user-dashboard-header">
            <div class="dashboard-container">
                <div><span class="dashboard-eyebrow">My workspace</span><h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $firstName }}</h1><p>Everything you need to find and manage your next room.</p></div>
                <a href="{{ route('rooms.index') }}" class="dashboard-primary"><i class="fas fa-magnifying-glass"></i> Explore rooms</a>
            </div>
        </header>

        <div class="dashboard-container dashboard-body">
            @include('partials.offer-banner', ['placement' => 'dashboard'])

            <section class="dashboard-stats" aria-label="Account overview">
                @foreach([
                    ['Wallet balance', '&#8377;'.number_format((float)($user->wallet_balance ?? 0), 2), 'fa-wallet', 'blue', route('wallet')],
                    ['Reward points', number_format((int)($user->wallet ?? 0)), 'fa-coins', 'amber', route('referral.index')],
                    ['Unlocked contacts', $unlockedCount, 'fa-lock-open', 'green', '#unlocked'],
                    ['Saved rooms', $wishlistCount, 'fa-heart', 'rose', route('wishlist.index')],
                ] as $stat)
                <a href="{{ $stat[4] }}" class="dashboard-stat"><span class="stat-icon {{ $stat[3] }}"><i class="fas {{ $stat[2] }}"></i></span><span><small>{{ $stat[0] }}</small><strong>{!! $stat[1] !!}</strong></span><i class="fas fa-arrow-right stat-arrow"></i></a>
                @endforeach
            </section>

            <section class="dashboard-grid">
                <div class="dashboard-main-column">
                    <article id="unlocked" class="dashboard-card">
                        <div class="dashboard-card-head"><div><h2>Recently unlocked contacts</h2><p>Properties whose owner details are available to you.</p></div><a href="{{ route('rooms.index') }}">Find more</a></div>
                        <div class="unlocked-list">
                        @forelse($recentUnlocks as $unlock)
                            @if($unlock->room)
                            <div class="unlocked-row"><img src="{{ $unlock->room->photo_url }}" alt="{{ $unlock->room->title }}" onerror="this.src='{{ asset('storage/default-room.jpg') }}'"><div class="unlocked-copy"><h3>{{ $unlock->room->title }}</h3><p><i class="fas fa-location-dot"></i>{{ $unlock->room->city ?: $unlock->room->address }}</p><strong>&#8377;{{ number_format((float)$unlock->room->rent) }} <small>/ month</small></strong></div><a href="{{ route('rooms.show', $unlock->room) }}">View details <i class="fas fa-arrow-right"></i></a></div>
                            @endif
                        @empty
                            <div class="dashboard-empty"><span><i class="fas fa-lock-open"></i></span><h3>No unlocked contacts yet</h3><p>Explore verified rooms and unlock owner contact details when you find a suitable property.</p><a href="{{ route('rooms.index') }}" class="dashboard-primary">Browse rooms</a></div>
                        @endforelse
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <div class="dashboard-card-head"><div><h2>Recommended for you</h2><p>{{ $cityAlerts->isNotEmpty() ? 'Fresh listings from your selected cities.' : 'Recently approved listings on ApnaNest.' }}</p></div><a href="{{ route('rooms.index') }}">View all</a></div>
                        <div class="recommendation-grid">
                        @forelse($recommendedRooms as $room)
                            <a href="{{ route('rooms.show', $room) }}" class="room-mini-card"><div class="room-mini-image"><img src="{{ $room->photo_url }}" alt="{{ $room->title }}" onerror="this.src='{{ asset('storage/default-room.jpg') }}'">@if($room->is_featured)<span>Featured</span>@endif</div><div class="room-mini-copy"><h3>{{ $room->title }}</h3><p><i class="fas fa-location-dot"></i>{{ $room->city }}</p><strong>&#8377;{{ number_format((float)$room->rent) }} <small>/mo</small></strong></div></a>
                        @empty
                            <div class="dashboard-inline-empty"><i class="fas fa-house-circle-check"></i><span><strong>No recommendations right now</strong><small>New approved rooms will appear here.</small></span></div>
                        @endforelse
                        </div>
                    </article>
                </div>

                <aside class="dashboard-side-column">
                    <article class="dashboard-card quick-card"><div class="dashboard-card-head"><div><h2>Quick actions</h2><p>Common account tasks.</p></div></div><nav>
                        <a href="{{ route('wishlist.index') }}"><span class="quick-icon rose"><i class="fas fa-heart"></i></span><span><strong>Saved rooms</strong><small>Review your shortlist</small></span><i class="fas fa-chevron-right"></i></a>
                        <a href="{{ route('plans') }}"><span class="quick-icon blue"><i class="fas fa-tags"></i></span><span><strong>Unlock plans</strong><small>Compare contact options</small></span><i class="fas fa-chevron-right"></i></a>
                        <a href="{{ route('referral.index') }}"><span class="quick-icon amber"><i class="fas fa-gift"></i></span><span><strong>Refer and earn</strong><small>Invite friends for rewards</small></span><i class="fas fa-chevron-right"></i></a>
                        <a href="{{ route('complaints.index') }}"><span class="quick-icon green"><i class="fas fa-shield-halved"></i></span><span><strong>My complaints</strong><small>{{ $openComplaints }} open {{ Str::plural('ticket', $openComplaints) }}</small></span><i class="fas fa-chevron-right"></i></a>
                    </nav></article>

                    <article class="dashboard-card alert-card"><div class="dashboard-card-head"><div><h2>City alerts</h2><p>Get notified when matching rooms go live.</p></div></div>
                        @if($cityAlerts->isNotEmpty())<div class="city-chips">@foreach($cityAlerts as $alert)<span><i class="fas fa-location-dot"></i>{{ $alert->city }}</span>@endforeach</div>@else<div class="small-empty"><i class="far fa-bell"></i><p>No city alerts added yet.</p></div>@endif
                        <a href="{{ route('rooms.index') }}" class="outline-action">Manage through room search <i class="fas fa-arrow-right"></i></a>
                    </article>

                    <article class="profile-summary"><img src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('assets/images/default-avatar.svg') }}" alt="{{ $user->name }}"><div><small>Signed in as</small><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></div><a href="{{ route('profile.edit') }}" aria-label="Edit profile"><i class="fas fa-pen"></i></a></article>
                </aside>
            </section>
        </div>
    </main>
</div>

<style>
.user-dashboard-main{background:#f5f7fb}.dashboard-container{width:min(1220px,calc(100% - 40px));margin:auto}.user-dashboard-header{background:#fff;border-bottom:1px solid #e2e8f0}.user-dashboard-header .dashboard-container{min-height:142px;display:flex;align-items:center;justify-content:space-between;gap:24px}.dashboard-eyebrow{color:#2563eb;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.12em}.user-dashboard-header h1{margin:7px 0 5px;color:#0f172a;font-size:29px;line-height:1.2;font-weight:850;letter-spacing:-.7px}.user-dashboard-header p{margin:0;color:#64748b;font-size:13px}.dashboard-primary{display:inline-flex;align-items:center;justify-content:center;gap:9px;background:#2563eb;color:#fff!important;text-decoration:none;border-radius:11px;padding:13px 18px;font-size:13px;font-weight:800;box-shadow:0 9px 20px rgba(37,99,235,.18)}.dashboard-body{padding-top:28px}.dashboard-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:18px}.dashboard-stat{position:relative;display:flex;align-items:center;gap:13px;padding:18px;background:#fff;border:1px solid #e2e8f0;border-radius:15px;text-decoration:none;box-shadow:0 3px 12px rgba(15,23,42,.035);transition:.2s}.dashboard-stat:hover{border-color:#bfdbfe;transform:translateY(-2px);box-shadow:0 9px 24px rgba(15,23,42,.07)}.stat-icon,.quick-icon{display:grid;place-items:center;flex:none;border-radius:11px}.stat-icon{width:43px;height:43px}.blue{background:#eff6ff;color:#2563eb}.amber{background:#fffbeb;color:#d97706}.green{background:#ecfdf5;color:#059669}.rose{background:#fff1f2;color:#e11d48}.dashboard-stat>span:nth-child(2){display:grid;gap:4px}.dashboard-stat small{font-size:10px;color:#64748b;font-weight:700}.dashboard-stat strong{font-size:20px;color:#0f172a;line-height:1}.stat-arrow{margin-left:auto;color:#cbd5e1;font-size:10px}.dashboard-grid{display:grid;grid-template-columns:minmax(0,1.75fr) minmax(280px,.75fr);gap:20px;margin-top:20px;align-items:start}.dashboard-main-column,.dashboard-side-column{display:grid;gap:20px}.dashboard-card{background:#fff;border:1px solid #e2e8f0;border-radius:17px;overflow:hidden;box-shadow:0 3px 14px rgba(15,23,42,.035)}.dashboard-card-head{min-height:74px;padding:17px 20px;border-bottom:1px solid #edf2f7;display:flex;align-items:center;justify-content:space-between;gap:15px}.dashboard-card-head h2{margin:0;color:#0f172a;font-size:15px;font-weight:850}.dashboard-card-head p{margin:4px 0 0;color:#64748b;font-size:10px}.dashboard-card-head>a{color:#2563eb;text-decoration:none;font-size:11px;font-weight:800}.unlocked-row{display:grid;grid-template-columns:76px minmax(0,1fr) auto;align-items:center;gap:15px;padding:15px 20px;border-bottom:1px solid #f1f5f9}.unlocked-row:last-child{border:0}.unlocked-row>img{width:76px;height:62px;border-radius:10px;object-fit:cover;background:#f1f5f9}.unlocked-copy{min-width:0}.unlocked-copy h3{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0;color:#0f172a;font-size:13px;font-weight:800}.unlocked-copy p{margin:5px 0;color:#64748b;font-size:10px}.unlocked-copy p i{color:#f43f5e;margin-right:5px}.unlocked-copy>strong{color:#0f172a;font-size:13px}.unlocked-copy small,.room-mini-copy small{color:#94a3b8;font-weight:500}.unlocked-row>a{padding:9px 12px;border-radius:9px;background:#eff6ff;color:#1d4ed8;text-decoration:none;font-size:10px;font-weight:800}.unlocked-row>a i{margin-left:5px}.dashboard-empty{text-align:center;padding:42px 20px}.dashboard-empty>span{display:grid;place-items:center;width:48px;height:48px;margin:auto;border-radius:14px;background:#eff6ff;color:#2563eb}.dashboard-empty h3{margin:12px 0 4px;color:#0f172a;font-size:14px}.dashboard-empty p{max-width:420px;margin:0 auto 16px;color:#64748b;font-size:11px;line-height:1.6}.recommendation-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;padding:16px}.room-mini-card{display:grid;grid-template-columns:90px minmax(0,1fr);gap:12px;padding:9px;border:1px solid #e2e8f0;border-radius:13px;text-decoration:none;transition:.2s}.room-mini-card:hover{border-color:#bfdbfe;background:#f8fbff}.room-mini-image{position:relative}.room-mini-image img{width:90px;height:80px;border-radius:9px;object-fit:cover}.room-mini-image span{position:absolute;left:5px;top:5px;padding:3px 5px;border-radius:5px;background:#2563eb;color:#fff;font-size:7px;font-weight:800}.room-mini-copy{min-width:0;align-self:center}.room-mini-copy h3{margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#0f172a;font-size:11px;font-weight:800}.room-mini-copy p{margin:6px 0;color:#64748b;font-size:9px}.room-mini-copy p i{color:#f43f5e;margin-right:4px}.room-mini-copy strong{color:#0f172a;font-size:12px}.quick-card nav{padding:8px}.quick-card nav>a{display:grid;grid-template-columns:38px 1fr auto;align-items:center;gap:10px;padding:10px;border-radius:11px;text-decoration:none}.quick-card nav>a:hover{background:#f8fafc}.quick-icon{width:36px;height:36px;font-size:12px}.quick-card nav span:nth-child(2){display:grid;gap:2px}.quick-card nav strong{color:#334155;font-size:11px}.quick-card nav small{color:#94a3b8;font-size:9px}.quick-card nav>a>i{color:#cbd5e1;font-size:9px}.city-chips{display:flex;flex-wrap:wrap;gap:7px;padding:16px}.city-chips span{padding:7px 9px;border-radius:8px;background:#f1f5f9;color:#475569;font-size:10px;font-weight:700}.city-chips i{color:#2563eb;margin-right:5px}.outline-action{margin:0 16px 16px;display:flex;align-items:center;justify-content:space-between;border:1px solid #dbeafe;border-radius:9px;padding:10px 11px;color:#2563eb;text-decoration:none;font-size:10px;font-weight:800}.small-empty{text-align:center;padding:18px;color:#94a3b8}.small-empty i{font-size:20px}.small-empty p{font-size:10px}.profile-summary{display:grid;grid-template-columns:44px 1fr 34px;align-items:center;gap:11px;padding:15px;background:#0f172a;border-radius:16px;color:#fff}.profile-summary img{width:44px;height:44px;border-radius:12px;object-fit:cover;background:#fff}.profile-summary div{display:grid;min-width:0}.profile-summary small{color:#94a3b8;font-size:8px}.profile-summary strong,.profile-summary span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.profile-summary strong{font-size:11px}.profile-summary span{color:#94a3b8;font-size:9px}.profile-summary>a{width:32px;height:32px;display:grid;place-items:center;border-radius:9px;background:#1e293b;color:#cbd5e1}.dashboard-inline-empty{grid-column:1/-1;display:flex;align-items:center;justify-content:center;gap:12px;padding:28px;color:#94a3b8}.dashboard-inline-empty>i{font-size:24px}.dashboard-inline-empty span{display:grid}.dashboard-inline-empty strong{color:#475569;font-size:11px}.dashboard-inline-empty small{font-size:9px}
@media(max-width:1100px){.dashboard-stats{grid-template-columns:repeat(2,1fr)}.dashboard-grid{grid-template-columns:1fr}.dashboard-side-column{grid-template-columns:repeat(2,1fr)}.profile-summary{grid-column:1/-1}}
@media(max-width:700px){.dashboard-container{width:min(100% - 24px,1220px)}.user-dashboard-header .dashboard-container{min-height:150px;align-items:flex-start;justify-content:center;flex-direction:column}.dashboard-primary{width:100%}.dashboard-stats{gap:10px}.dashboard-stat{padding:14px 11px}.stat-icon{width:36px;height:36px}.dashboard-stat strong{font-size:16px}.dashboard-grid{gap:14px}.dashboard-main-column,.dashboard-side-column{gap:14px}.dashboard-side-column{grid-template-columns:1fr}.profile-summary{grid-column:auto}.unlocked-row{grid-template-columns:62px minmax(0,1fr);padding:13px}.unlocked-row>img{width:62px;height:56px}.unlocked-row>a{grid-column:2;width:max-content}.recommendation-grid{grid-template-columns:1fr}.user-dashboard-header h1{font-size:24px}}
</style>
@endsection
