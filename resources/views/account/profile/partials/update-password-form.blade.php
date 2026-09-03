<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" 
                   class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                   autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-xs font-semibold text-rose-500" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="update_password_password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">New Password</label>
                <input id="update_password_password" name="password" type="password" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                       autocomplete="new-password" placeholder="Minimum 8 characters" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-xs font-semibold text-rose-500" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Confirm Password</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all font-semibold text-slate-800 text-sm" 
                       autocomplete="new-password" placeholder="Re-enter new password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-xs font-semibold text-rose-500" />
            </div>
        </div>

        <div class="pt-2 flex items-center gap-4">
            <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-indigo-600 to-indigo-800 hover:from-indigo-700 hover:to-indigo-900 text-white font-extrabold py-3.5 px-8 rounded-2xl shadow-lg shadow-indigo-100 transition-all duration-200 transform active:scale-[0.99] text-sm flex items-center justify-center gap-2">
                <i class="fas fa-key text-xs"></i> Update Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-bold text-emerald-600 flex items-center gap-1"
                >
                    <i class="fas fa-circle-check"></i>
                    <span>Password updated successfully.</span>
                </p>
            @endif
        </div>
    </form>
</section>
