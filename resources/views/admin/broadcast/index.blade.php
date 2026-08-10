@extends('layouts.admin')

@section('title', 'Push Broadcast Announcement Center')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-shared.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin-broadcast-index.css') }}">
@endpush

@section('admin-content')
<div class="space-y-6 p-5 lg:p-6" x-data="{ activeTab: 'compose' }">
    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b pb-5">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text flex items-center gap-1.5">
                <i class="fas fa-bullhorn text-indigo-600"></i> Marketing & Engagement Center
            </p>
            <h1 class="mt-1 text-2xl font-black text-slate-900 tracking-tight">Push Broadcast Announcement</h1>
            <p class="text-xs font-semibold text-slate-500 mt-0.5">Send targeted push notifications, offer banners, and email blasts to renters & property owners.</p>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-2xl border bg-white px-3.5 py-2.5 shadow-sm text-center">
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Renters</span>
                <span class="text-base font-black text-slate-800">{{ number_format($totalUsers) }}</span>
            </div>
            <div class="rounded-2xl border bg-white px-3.5 py-2.5 shadow-sm text-center">
                <span class="block text-[9px] font-bold text-slate-400 uppercase">Owners</span>
                <span class="text-base font-black text-slate-800">{{ number_format($totalOwners) }}</span>
            </div>
            <div class="rounded-2xl border bg-indigo-900 px-3.5 py-2.5 text-center text-white shadow-md">
                <span class="block text-[9px] font-bold text-indigo-300 uppercase">Total Targetable</span>
                <span class="text-base font-black">{{ number_format($totalUsers + $totalOwners) }}</span>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 flex items-center gap-3 shadow-sm">
            <i class="fas fa-check-circle text-xl text-emerald-600"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 space-y-1 shadow-sm">
            @foreach($errors->all() as $error)
                <p><i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-6">
        <button @click="activeTab = 'compose'"
                :aria-selected="activeTab === 'compose'"
                class="tab-btn pb-3 text-xs font-bold text-slate-500 hover:text-slate-900 transition flex items-center gap-2">
            <i class="fas fa-paper-plane text-sm"></i> Send New Announcement
        </button>
        <button @click="activeTab = 'history'"
                :aria-selected="activeTab === 'history'"
                class="tab-btn pb-3 text-xs font-bold text-slate-500 hover:text-slate-900 transition flex items-center gap-2 relative">
            <i class="fas fa-history text-sm"></i> Broadcast Logs & History
            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-extrabold text-slate-600">{{ $pastBroadcasts->total() }}</span>
        </button>
    </div>

    <!-- TAB 1: COMPOSE ANNOUNCEMENT -->
    <div x-show="activeTab === 'compose'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Section (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('admin.broadcast.send') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border bg-white p-6 shadow-sm space-y-6">
                @csrf

                <!-- Target Audience -->
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">1. Select Target Audience & Location</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="audience-card active relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="all" checked class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-users-viewfinder"></i></span>
                            <span class="text-xs font-black text-slate-800">Everyone</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">All Renters & Owners</span>
                        </label>
                        <label class="audience-card relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="user" class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-user"></i></span>
                            <span class="text-xs font-black text-slate-800">Renters Only</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">Tenant accounts</span>
                        </label>
                        <label class="audience-card relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="owner" class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-user-tie"></i></span>
                            <span class="text-xs font-black text-slate-800">Owners Only</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">Property owners</span>
                        </label>
                    </div>

                    <!-- Target City Filter -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5"><i class="fas fa-location-dot text-red-500 mr-1"></i> Target Specific City (Optional)</label>
                        <select name="target_city" class="w-full h-11 rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Cities (Send Everywhere)</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" @selected(old('target_city') === $c)>📍 {{ $c }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400">Select a city to target only users & owners associated with that city.</span>
                    </div>
                </div>

                <!-- Channels -->
                <div class="space-y-3">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">2. Select Notification Channels</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-2xl border bg-slate-50 cursor-pointer hover:bg-white transition shadow-sm">
                            <input type="checkbox" name="channels[]" value="bell" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800"><i class="fas fa-bell text-indigo-500 mr-1"></i> In-App Bell</span>
                                <span class="text-[10px] text-slate-400">Header dropdown alert</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-2xl border bg-slate-50 cursor-pointer hover:bg-white transition shadow-sm">
                            <input type="checkbox" name="channels[]" value="firebase" checked class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800"><i class="fas fa-fire text-amber-500 mr-1"></i> Push Notification</span>
                                <span class="text-[10px] text-slate-400">Mobile + Web Push</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-2xl border bg-slate-50 cursor-pointer hover:bg-white transition shadow-sm">
                            <input type="checkbox" name="channels[]" value="email" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800"><i class="fas fa-envelope text-emerald-500 mr-1"></i> Email Blast</span>
                                <span class="text-[10px] text-slate-400">Branded email template</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Message Details -->
                <div class="space-y-4">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">3. Notification Details</label>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Announcement Title *</label>
                        <input type="text" id="input_title" name="title" required value="{{ old('title') }}" placeholder="e.g. 🎉 Diwali Special Offer: Get 50% Off on Premium Plans!" class="w-full h-11 rounded-xl border-slate-200 text-xs font-semibold px-4 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Announcement Message *</label>
                        <textarea id="input_message" name="message" rows="4" required placeholder="Write your offer details, coupon code, or announcement message here..." class="w-full rounded-xl border-slate-200 text-xs font-medium p-4 focus:ring-2 focus:ring-indigo-500">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Target Link / URL (Optional)</label>
                        <input type="url" name="link" value="{{ old('link') }}" placeholder="https://apnanest.com/plans or leave blank for home" class="w-full h-10 rounded-xl border-slate-200 text-xs px-4 focus:ring-2 focus:ring-indigo-500 font-mono">
                        <span class="text-[10px] text-slate-400">Users will be taken to this link when tapping/clicking the notification.</span>
                    </div>

                    <!-- Offer Banner Image Upload -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-2">Offer Banner Image 🖼️ (Optional)</label>
                        <div class="flex flex-col sm:flex-row items-start gap-4">
                            <div id="image-preview-wrapper" class="h-28 w-44 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                <span id="image-placeholder" class="text-center text-slate-400 p-2">
                                    <i class="fas fa-image text-2xl"></i>
                                    <span class="block text-[10px] mt-1 font-bold">No Image Selected</span>
                                </span>
                                <img id="image-preview" class="hidden h-full w-full object-cover">
                            </div>
                            <div class="flex-1 space-y-2">
                                <label class="cursor-pointer inline-flex items-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 px-4 py-2.5 text-xs font-bold text-slate-700 transition">
                                    <i class="fas fa-upload text-indigo-600"></i> Choose Offer Banner
                                    <input type="file" id="banner_image" name="banner_image" accept="image/jpeg,image/png,image/webp" class="hidden">
                                </label>
                                <p class="text-[11px] text-slate-500">Banner image will be shown on <strong>Mobile Push, Web Push, Bell Icon, and Email</strong>. Max size: 3MB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black px-6 py-3 text-xs shadow-lg transition">
                        <i class="fas fa-paper-plane"></i> Send Broadcast Now
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Phone Notification Preview (1 Col) -->
        <div class="space-y-4">
            <div class="sticky top-20 rounded-2xl border bg-slate-900 p-5 shadow-xl text-white space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                        <i class="fas fa-mobile-screen text-indigo-400"></i> Live User Preview
                    </span>
                    <span class="rounded-full bg-emerald-500/20 text-emerald-400 px-2 py-0.5 text-[9px] font-bold">Live Sync</span>
                </div>

                <!-- Simulated Push Notification Card -->
                <div class="rounded-2xl bg-slate-800 border border-slate-700/80 p-4 shadow-lg space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="h-6 w-6 rounded-lg bg-indigo-600 flex items-center justify-center text-white text-[10px] font-black">AN</div>
                            <span class="text-xs font-bold text-slate-200">ApnaNest</span>
                        </div>
                        <span class="text-[9px] text-slate-400">now</span>
                    </div>

                    <strong id="preview_title" class="block text-xs font-extrabold text-white leading-snug">🎉 Diwali Special Offer: Get 50% Off!</strong>
                    <p id="preview_message" class="text-[11px] text-slate-300 line-clamp-3 leading-relaxed">Write your offer details, coupon code, or announcement message here...</p>

                    <!-- Preview Offer Banner -->
                    <div id="preview_image_container" class="hidden rounded-xl overflow-hidden border border-slate-700 max-h-36">
                        <img id="preview_image" class="w-full h-full object-cover">
                    </div>
                </div>

                <p class="text-[10px] text-slate-400 text-center">This preview demonstrates how your notification will render on the user's smartphone and browser.</p>
            </div>
        </div>

    </div>

    <!-- TAB 2: BROADCAST LOGS & HISTORY TABLE -->
    <div x-show="activeTab === 'history'" class="space-y-4">
        <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b p-5 gap-3">
                <div>
                    <h2 class="text-base font-black text-slate-900">Sent Broadcast History</h2>
                    <p class="text-xs text-slate-500">Full audit log of all marketing announcements sent to users.</p>
                </div>
                <span class="text-xs font-bold text-slate-500">Showing {{ $pastBroadcasts->count() }} of {{ $pastBroadcasts->total() }} entries</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b bg-slate-50 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3.5">Announcement / Title</th>
                            <th class="px-5 py-3.5">Target Audience</th>
                            <th class="px-5 py-3.5">Channels</th>
                            <th class="px-5 py-3.5">Recipients</th>
                            <th class="px-5 py-3.5">Sent Date</th>
                            <th class="px-5 py-3.5 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-xs">
                        @forelse($pastBroadcasts as $b)
                            @php
                                $payload = json_decode($b->message, true);
                                $isJson  = is_array($payload);
                                $msgText = $isJson ? ($payload['message'] ?? '') : $b->message;
                                $audience= $isJson ? ($payload['audience'] ?? 'All Users') : 'All Users';
                                $count   = $isJson ? ($payload['sent_count'] ?? 0) : 0;
                                $channels= $isJson ? ($payload['channels'] ?? []) : ['bell', 'firebase'];
                                $imgUrl  = $isJson ? ($payload['image_url'] ?? null) : $b->link;
                                $linkUrl = $isJson ? ($payload['target_link'] ?? null) : null;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        @if($imgUrl)
                                            <img src="{{ $imgUrl }}" class="h-10 w-14 rounded-lg object-cover border shrink-0 shadow-sm">
                                        @else
                                            <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0 text-slate-500">
                                                <i class="fas fa-bullhorn text-sm"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <strong class="block text-slate-900 font-extrabold truncate max-w-md">{{ $b->title }}</strong>
                                            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $msgText }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold text-slate-700 whitespace-nowrap border border-slate-200">
                                        <i class="fas fa-user-group mr-1 text-slate-400"></i>{{ $audience }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5">
                                        @if(in_array('bell', $channels, true))
                                            <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700" title="In-App Bell"><i class="fas fa-bell"></i> Bell</span>
                                        @endif
                                        @if(in_array('firebase', $channels, true))
                                            <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700" title="Push Notification"><i class="fas fa-fire"></i> Push</span>
                                        @endif
                                        @if(in_array('email', $channels, true))
                                            <span class="rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700" title="Email Blast"><i class="fas fa-envelope"></i> Email</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-extrabold text-slate-800">{{ number_format($count) }}</span> <span class="text-[10px] text-slate-400">users</span>
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="font-bold text-slate-700">{{ $b->created_at->format('d M Y, h:i A') }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $b->created_at->diffForHumans() }}</p>
                                </td>

                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <button @click="$dispatch('open-broadcast-modal', { title: '{{ e($b->title) }}', message: '{{ e($msgText) }}', image: '{{ $imgUrl }}', link: '{{ $linkUrl }}', audience: '{{ $audience }}', date: '{{ $b->created_at->format('d M Y, h:i A') }}' })"
                                            class="rounded-xl border px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition">
                                        <i class="fas fa-eye mr-1 text-slate-400"></i> Inspect
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-12 text-center text-slate-400">
                                    <i class="fas fa-bullhorn text-4xl text-slate-200 mb-2"></i>
                                    <p class="text-xs font-bold">No broadcast notifications sent yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pastBroadcasts->hasPages())
                <div class="p-4 border-t">
                    {{ $pastBroadcasts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal for Inspecting Sent Broadcast -->
    <div x-data="{ open: false, data: {} }"
         @open-broadcast-modal.window="open = true; data = $event.detail"
         x-show="open"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
         style="display: none;">
        <div @click.outside="open = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-sm font-black text-slate-900" x-text="data.title"></h3>
                <button @click="open = false" class="text-slate-400 hover:text-slate-700"><i class="fas fa-times"></i></button>
            </div>

            <template x-if="data.image">
                <img :src="data.image" class="w-full rounded-xl max-h-48 object-cover border shadow-sm">
            </template>

            <div class="space-y-2">
                <p class="text-xs font-semibold text-slate-700 leading-relaxed" x-text="data.message"></p>
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t">
                    <span x-text="'Target: ' + data.audience"></span>
                    <span x-text="data.date"></span>
                </div>
            </div>

            <div class="pt-2 text-right">
                <button @click="open = false" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Radio Card Highlight Toggle
document.querySelectorAll('.audience-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.audience-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});

// Live Preview Syncing
const inputTitle   = document.getElementById('input_title');
const inputMessage = document.getElementById('input_message');
const previewTitle = document.getElementById('preview_title');
const previewMsg   = document.getElementById('preview_message');
const bannerInput  = document.getElementById('banner_image');

if (inputTitle && previewTitle) {
    inputTitle.addEventListener('input', e => {
        previewTitle.textContent = e.target.value.trim() || '🎉 Diwali Special Offer: Get 50% Off!';
    });
}

if (inputMessage && previewMsg) {
    inputMessage.addEventListener('input', e => {
        previewMsg.textContent = e.target.value.trim() || 'Write your offer details, coupon code, or announcement message here...';
    });
}

if (bannerInput) {
    bannerInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('image-placeholder');
        const liveContainer = document.getElementById('preview_image_container');
        const liveImg       = document.getElementById('preview_image');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                if (preview) { preview.src = evt.target.result; preview.classList.remove('hidden'); }
                if (placeholder) placeholder.classList.add('hidden');
                if (liveImg && liveContainer) {
                    liveImg.src = evt.target.result;
                    liveContainer.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        } else {
            if (preview) { preview.src = ''; preview.classList.add('hidden'); }
            if (placeholder) placeholder.classList.remove('hidden');
            if (liveContainer) liveContainer.classList.add('hidden');
        }
    });
}
</script>
@endpush
