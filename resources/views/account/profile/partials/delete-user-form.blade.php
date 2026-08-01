<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="w-full lg:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 px-8 rounded-2xl shadow-lg shadow-red-100 transition-all active:scale-95 text-center"
    >{{ __('Delete Account') }}</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div x-data="{ otpSent: false, loading: false, otp: '' }" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. To proceed, please verify your identity via email OTP.') }}
            </p>

            <!-- Send OTP Button -->
            <div x-show="!otpSent" class="mt-6">
                <p class="text-sm text-gray-600 mb-4">{{ __('Click below to send a verification code to your registered email.') }}</p>
                
                <button 
                    @click="
                        loading = true;
                        fetch('{{ route('profile.send-delete-otp') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => {
                            if(response.ok) {
                                otpSent = true;
                                loading = false;
                            } else {
                                alert('Failed to send OTP. Please try again.');
                                loading = false;
                            }
                        })
                        .catch(error => {
                            alert('Something went wrong.');
                            loading = false;
                        });
                    "
                    type="button" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition disabled:opacity-50"
                    :disabled="loading"
                >
                    <span x-show="!loading">{{ __('Send Verification OTP') }}</span>
                    <span x-show="loading">{{ __('Sending...') }}</span>
                </button>
            </div>

            <!-- Verify OTP Form -->
            <form x-show="otpSent" method="post" action="{{ route('profile.destroy') }}" class="mt-6">
                @csrf
                @method('delete')

                <div class="mt-6">
                    <x-input-label for="otp" value="{{ __('Enter OTP Code') }}" class="sr-only" />

                    <x-text-input
                        id="otp"
                        name="otp"
                        type="number"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('Enter 6-digit OTP') }}"
                        required
                    />

                    <x-input-error :messages="$errors->userDeletion->get('otp')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        {{ __('Cancel') }}
                    </x-secondary-button>

                    <x-danger-button class="ms-3">
                        {{ __('Delete Account') }}
                    </x-danger-button>
                </div>
            </form>
            
            <div x-show="!otpSent" class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>
            </div>
        </div>
    </x-modal>
</section>
