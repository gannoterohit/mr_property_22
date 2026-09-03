<div id="direct-msg-card" class="rounded-2xl border bg-white p-5 shadow-sm space-y-4">
    <div class="flex items-center gap-3 border-b pb-4">
        <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <i class="fas fa-paper-plane text-lg"></i>
        </div>
        <div>
            <h2 class="text-sm font-extrabold text-slate-800">Send Direct Notification</h2>
            <p class="text-[10px] text-slate-500">Send individual SMS, Email, Push or Bell alert to {{ $targetUser->name }}</p>
        </div>
    </div>

    <!-- Contact Info Badges -->
    <div class="flex flex-wrap gap-1.5 text-[10px] font-bold">
        <span class="rounded-md px-2 py-1 {{ $targetUser->email ? 'bg-slate-100 text-slate-700' : 'bg-red-50 text-red-600' }}">
            <i class="fas fa-envelope mr-1"></i>{{ $targetUser->email ?: 'No email' }}
        </span>
        <span class="rounded-md px-2 py-1 {{ $targetUser->phone ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-600' }}">
            <i class="fas fa-phone mr-1"></i>{{ $targetUser->phone ?: 'No phone' }}
        </span>
        <span class="rounded-md px-2 py-1 {{ $targetUser->fcm_token ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-400' }}">
            <i class="fas fa-mobile-screen-button mr-1"></i>{{ $targetUser->fcm_token ? 'App Active' : 'No FCM Token' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.members.sendDirectMessage', $targetUser) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Channels Selection -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">Notification Channels</label>
            <div class="grid grid-cols-2 gap-2">
                <!-- Bell Icon -->
                <label class="flex items-center gap-2 rounded-xl border p-2.5 cursor-pointer transition hover:bg-slate-50">
                    <input type="checkbox" name="channels[]" value="bell" checked class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                    <span class="text-xs font-semibold text-slate-700"><i class="fas fa-bell text-amber-500 mr-1"></i>Bell Icon</span>
                </label>

                <!-- Push Notification -->
                <label class="flex items-center gap-2 rounded-xl border p-2.5 cursor-pointer transition hover:bg-slate-50">
                    <input type="checkbox" name="channels[]" value="firebase" {{ $targetUser->fcm_token ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                    <span class="text-xs font-semibold text-slate-700"><i class="fas fa-fire text-amber-600 mr-1"></i>Push</span>
                </label>

                <!-- Email -->
                <label class="flex items-center gap-2 rounded-xl border p-2.5 cursor-pointer transition hover:bg-slate-50">
                    <input type="checkbox" name="channels[]" value="email" {{ $targetUser->email ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                    <span class="text-xs font-semibold text-slate-700"><i class="fas fa-envelope text-blue-500 mr-1"></i>Email</span>
                </label>

                <!-- SMS -->
                <label class="flex items-center gap-2 rounded-xl border p-2.5 cursor-pointer transition hover:bg-slate-50">
                    <input type="checkbox" name="channels[]" value="sms" {{ $targetUser->phone ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                    <span class="text-xs font-semibold text-slate-700"><i class="fas fa-comment-sms text-indigo-500 mr-1"></i>SMS</span>
                </label>
            </div>
        </div>

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Message Title</label>
            <input type="text" name="title" required maxlength="255" placeholder="e.g. Account Update / Special Offer" class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:ring-0">
        </div>

        <!-- Message Body -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Message Content</label>
            <textarea name="message" rows="4" required maxlength="2000" placeholder="Type your message here..." class="w-full rounded-xl border-slate-200 text-xs py-2.5 px-3 focus:ring-0"></textarea>
        </div>

        <!-- Action Link (Optional) -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Target Link <span class="font-normal text-slate-400">(Optional)</span></label>
            <input type="url" name="link" placeholder="https://..." class="w-full rounded-xl border-slate-200 text-xs py-2 px-3 focus:ring-0">
        </div>

        <!-- Image Banner (Optional) -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Banner Image <span class="font-normal text-slate-400">(Optional)</span></label>
            <input type="file" name="banner_image" accept="image/*" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>

        <button type="submit" class="w-full rounded-xl bg-indigo-600 hover:bg-indigo-700 py-3 text-xs font-bold text-white shadow transition flex items-center justify-center gap-2">
            <i class="fas fa-paper-plane"></i>
            <span>Send Direct Message</span>
        </button>
    </form>
</div>
