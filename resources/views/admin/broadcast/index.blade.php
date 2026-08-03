@extends('layouts.admin')

@section('title', 'Push Broadcast Announcement')

@push('styles')
<style>
.audience-card.active {
    border-color: #4f46e5 !important;
    background-color: rgba(238, 242, 255, 0.75) !important;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15) !important;
}
.audience-card.active .check-icon {
    opacity: 1 !important;
}
.audience-card.active .badge-icon {
    background-color: #4f46e5 !important;
    color: #ffffff !important;
}
</style>
@endpush

@section('admin-content')
<div class="space-y-6 p-5 lg:p-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Marketing & Announcements</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Broadcast Push Notification</h1>
            <p class="text-sm text-slate-500">Send instant announcements, offers, and notifications with banners to your users and owners.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <div class="rounded-xl border bg-white px-4 py-2 text-center shadow-sm">
                <span class="block text-[10px] font-bold text-slate-400 uppercase">Renters</span>
                <span class="text-sm font-extrabold text-slate-800">{{ number_format($totalUsers) }}</span>
            </div>
            <div class="rounded-xl border bg-white px-4 py-2 text-center shadow-sm">
                <span class="block text-[10px] font-bold text-slate-400 uppercase">Owners</span>
                <span class="text-sm font-extrabold text-slate-800">{{ number_format($totalOwners) }}</span>
            </div>
            <div class="rounded-xl border bg-slate-900 px-4 py-2 text-center text-white shadow-sm">
                <span class="block text-[10px] font-bold text-slate-300 uppercase">Total Targetable</span>
                <span class="text-sm font-extrabold">{{ number_format($totalUsers + $totalOwners) }}</span>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 flex items-center gap-3">
            <i class="fas fa-check-circle text-lg text-emerald-600"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 space-y-1">
            @foreach($errors->all() as $error)
                <p><i class="fas fa-exclamation-circle mr-1"></i> {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Card (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('admin.broadcast.send') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border bg-white p-6 shadow-sm space-y-6">
                @csrf

                <!-- Target Audience -->
                <div class="space-y-4">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700">1. Select Target Audience & Location</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="audience-card active relative flex flex-col items-center justify-center p-4.5 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="all" checked class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-users-viewfinder"></i></span>
                            <span class="text-xs font-black text-slate-800">Everyone</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">All Renters & Owners</span>
                        </label>
                        <label class="audience-card relative flex flex-col items-center justify-center p-4.5 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="user" class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-user"></i></span>
                            <span class="text-xs font-black text-slate-800">Renters Only</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">Tenant accounts</span>
                        </label>
                        <label class="audience-card relative flex flex-col items-center justify-center p-4.5 rounded-2xl border-2 cursor-pointer transition-all border-slate-200 bg-white hover:border-slate-300 group">
                            <input type="radio" name="target_audience" value="owner" class="peer sr-only">
                            <span class="check-icon absolute top-2.5 right-2.5 text-indigo-600 opacity-0 transition-opacity"><i class="fas fa-check-circle text-sm"></i></span>
                            <span class="badge-icon h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base mb-2 group-hover:scale-110 transition shadow-sm"><i class="fas fa-user-tie"></i></span>
                            <span class="text-xs font-black text-slate-800">Owners Only</span>
                            <span class="text-[10px] font-semibold text-slate-400 mt-0.5">Property owners</span>
                        </label>
                    </div>

                    <!-- Target City Filter -->
                    <div class="pt-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5"><i class="fas fa-location-dot text-red-500 mr-1"></i> Target Specific City (Optional)</label>
                        <select name="target_city" class="w-full h-11 rounded-xl border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Cities (Send Everywhere)</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" @selected(old('target_city') === $c)>📍 {{ $c }}</option>
                            @endforeach
                        </select>
                        <span class="text-[10px] text-slate-400">If a city is selected (e.g. Indore), notification will only be sent to users & owners associated with that city.</span>
                    </div>
                </div>

                <!-- Channels -->
                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-3">2. Select Notification Channels</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border bg-slate-50 cursor-pointer hover:bg-white transition">
                            <input type="checkbox" name="channels[]" value="bell" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800"><i class="fas fa-bell text-indigo-500 mr-1"></i> In-App Bell</span>
                                <span class="text-[10px] text-slate-400">Navbar dropdown alert</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border bg-slate-50 cursor-pointer hover:bg-white transition">
                            <input type="checkbox" name="channels[]" value="firebase" checked class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800"><i class="fas fa-fire text-amber-500 mr-1"></i> Push Notification</span>
                                <span class="text-[10px] text-slate-400">Mobile + Web Push</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3.5 rounded-xl border bg-slate-50 cursor-pointer hover:bg-white transition">
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
                        <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. 🎉 Diwali Special Offer: Get 50% Off on Premium Plans!" class="w-full h-11 rounded-xl border-slate-200 text-xs font-semibold px-4 focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Announcement Message *</label>
                        <textarea name="message" rows="4" required placeholder="Write your offer details, coupon code, or announcement message here..." class="w-full rounded-xl border-slate-200 text-xs font-medium p-4 focus:ring-2 focus:ring-indigo-500">{{ old('message') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1">Target Link / URL (Optional)</label>
                        <input type="url" name="link" value="{{ old('link') }}" placeholder="https://apnanest.com/plans or leave blank for home" class="w-full h-10 rounded-xl border-slate-200 text-xs px-4 focus:ring-2 focus:ring-indigo-500 font-mono">
                        <span class="text-[10px] text-slate-400">Users will be taken to this link when clicking the notification.</span>
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
                                <p class="text-[11px] text-slate-500">Banner image will be shown on <strong>Mobile Push, Web Push, Bell Icon, and Email</strong>. Recommended size: 600x300px or 16:9 ratio. Max size: 3MB.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-6 py-3 text-xs shadow-lg transition">
                        <i class="fas fa-paper-plane"></i> Send Broadcast Now
                    </button>
                </div>
            </form>
        </div>

        <!-- Broadcast History Card (1 Column) -->
        <div class="space-y-4">
            <div class="rounded-2xl border bg-white p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-700"><i class="fas fa-clock-rotate-left mr-1.5 text-indigo-500"></i> Broadcast History</h2>
                    <span class="text-[10px] font-bold text-slate-400">{{ $pastBroadcasts->total() }} sent</span>
                </div>

                <div class="space-y-3 divide-y max-h-[600px] overflow-y-auto">
                    @forelse($pastBroadcasts as $b)
                        <div class="pt-3 first:pt-0 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <strong class="text-xs font-bold text-slate-900 block leading-tight">{{ $b->title }}</strong>
                                <span class="text-[9px] font-extrabold text-slate-400 whitespace-nowrap">{{ $b->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-[11px] text-slate-500 line-clamp-2">{{ $b->message }}</p>
                            @if($b->link)
                                <a href="{{ $b->link }}" target="_blank" class="text-[10px] font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                    <i class="fas fa-external-link-alt text-[8px]"></i> Banner Link
                                </a>
                            @endif
                            <div class="flex items-center justify-between pt-1 text-[10px] text-slate-400 border-t border-slate-100">
                                <span><i class="fas fa-calendar-alt mr-1"></i>{{ $b->created_at->format('d M Y, h:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400">
                            <i class="fas fa-bullhorn text-3xl text-slate-200 mb-2"></i>
                            <p class="text-xs font-bold">No broadcast announcements sent yet.</p>
                        </div>
                    @endforelse
                </div>

                @if($pastBroadcasts->hasPages())
                    <div class="pt-3 border-t">
                        {{ $pastBroadcasts->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.audience-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.audience-card').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});

document.getElementById('banner_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    const placeholder = document.getElementById('image-placeholder');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(evt) {
            preview.src = evt.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
});
</script>
@endpush
