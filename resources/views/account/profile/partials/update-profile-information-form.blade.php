<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Avatar Upload -->
        <div class="flex items-center gap-5 p-4 bg-slate-50 rounded-2xl border border-slate-200/70">
            <div class="relative group shrink-0">
                <div class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-white shadow-md bg-white flex items-center justify-center">
                    @if($user->avatar)<img id="avatar_preview" src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }} profile" class="w-full h-full object-cover">@else<div id="avatar_preview" class="w-full h-full flex items-center justify-center bg-indigo-50 text-indigo-700"><i class="fas fa-user" aria-hidden="true"></i><span class="sr-only">{{ $user->name }} profile</span></div>@endif
                </div>
                <label for="avatar" class="absolute -bottom-1 -right-1 w-8 h-8 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center justify-center cursor-pointer shadow-md transition-all group-hover:scale-105 border-2 border-white">
                    <i class="fas fa-camera text-xs"></i>
                    <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this)">
                </label>
            </div>
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Profile Photo</h3>
                <p class="text-xs text-slate-500 mt-0.5 mb-2">Upload a high quality avatar image (JPG, PNG or WebP, max 2MB).</p>
                <x-input-error class="mt-1 text-xs font-semibold text-rose-500" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Full Name <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" placeholder="Enter your name" />
                <x-input-error class="mt-2 text-xs font-semibold text-rose-500" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Email Address <span class="text-rose-500">*</span></label>
                <input id="email" name="email" type="email" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                       value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="name@domain.com" />
                <x-input-error class="mt-2 text-xs font-semibold text-rose-500" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-xs text-amber-700 font-medium">
                            Your email address is unverified.
                            <button form="send-verification" class="underline font-bold text-amber-800 hover:text-amber-900 ml-1">
                                Re-send verification email
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-1 text-xs font-bold text-emerald-600">
                                A new verification link has been sent to your email.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Phone Number</label>
                <input id="phone" name="phone" type="tel" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                       value="{{ old('phone', $user->phone) }}" placeholder="+91 98765 43210" />
                <x-input-error class="mt-2 text-xs font-semibold text-rose-500" :messages="$errors->get('phone')" />
            </div>
        </div>

        <div class="pt-2 flex items-center gap-4">
            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-extrabold py-3.5 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all duration-200 transform active:scale-[0.99] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-bold text-emerald-600 flex items-center gap-1"
                >
                    <i class="fas fa-circle-check"></i>
                    <span>Saved successfully.</span>
                </p>
            @endif
        </div>
    </form>

<script>
function previewImage(input) {
    const preview = document.getElementById('avatar_preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.outerHTML = `<img id="avatar_preview" src="${e.target.result}" alt="Profile preview" class="w-full h-full object-cover">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</section>
