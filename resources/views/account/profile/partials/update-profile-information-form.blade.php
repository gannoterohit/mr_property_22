<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Profile Avatar -->
        <div class="flex items-center gap-6 mb-6">
            <div class="relative group">
                <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-dashed border-gray-300 group-hover:border-indigo-500 transition-colors bg-gray-50 flex items-center justify-center">
                    <img id="avatar_preview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('assets/images/default-avatar.svg') }}" onerror="this.onerror=null;this.src='{{ asset('assets/images/default-avatar.svg') }}'" alt="{{ $user->name }} profile" class="w-full h-full object-cover">
                </div>
                <label for="avatar" class="absolute -bottom-2 -right-2 w-8 h-8 bg-indigo-600 text-white rounded-lg flex items-center justify-center cursor-pointer hover:bg-indigo-700 shadow-md transition-all group-hover:scale-110">
                    <i class="fas fa-camera text-xs"></i>
                    <input type="file" id="avatar" name="avatar" class="hidden" accept="image/*" onchange="previewImage(this)">
                </label>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Profile Photo</h3>
                <p class="text-xs text-gray-500 mb-2">JPG, PNG or WebP. Max 2MB.</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-3" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 py-3" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="w-full lg:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-95">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 font-medium flex items-center"
                >
                    <i class="fas fa-check-circle mr-1"></i>
                    {{ __('Saved successfully.') }}
                </p>
            @endif
        </div>
    </form>

<script>
function previewImage(input) {
    const preview = document.getElementById('avatar_preview');
    const placeholder = document.getElementById('avatar_placeholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</section>
