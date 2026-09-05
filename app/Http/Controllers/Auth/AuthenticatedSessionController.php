<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private const SESSION_PASSKEY = 'admin_passkey_verified';

    public function adminAccess(Request $request): RedirectResponse|View
    {
        if (Auth::guard('web')->check() && Auth::user()?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $passed = $request->session()->get(self::SESSION_PASSKEY, false);

        return view('auth.admin-login', ['passkeyValidated' => (bool) $passed]);
    }

    public function adminAuthenticate(Request $request): RedirectResponse|View
    {
        $request->validate([
            'access_passkey' => 'nullable|string',
            'email' => 'nullable|email',
            'password' => 'nullable|string',
            'remember' => 'nullable|bool',
        ]);

        if ($request->filled('access_passkey')) {
            $stored = \App\Models\Setting::get('admin_access_key');

            if (!$stored || !Hash::check($request->input('access_passkey'), (string) $stored)) {
                throw ValidationException::withMessages([
                    'access_passkey' => 'Invalid access key.',
                ]);
            }

            $request->session()->put(self::SESSION_PASSKEY, true);

            return back()->withInput($request->only('email'))
                ->with('status', 'Access key verified. Enter your credentials.');
        }

        if (!$request->session()->get(self::SESSION_PASSKEY, false)) {
            return back()->withInput()->withErrors([
                'access_passkey' => 'Please verify your access key first.',
            ]);
        }

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== 'admin' || !Hash::check($credentials['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        if (!$user->is_staff_active || $user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Contact the super admin.',
            ]);
        }

        $request->session()->forget(self::SESSION_PASSKEY);

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_admin_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('home', ['auth' => 'login']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget(self::SESSION_PASSKEY);

        return redirect('/');
    }
}
