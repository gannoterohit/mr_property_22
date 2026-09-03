<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the separate admin/staff password login screen.
     */
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

        return view('auth.admin-login');
    }

    /**
     * Authenticate admin and staff accounts with email/password.
     */
    public function adminAuthenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || $user->role !== 'admin' || !Hash::check($credentials['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid admin credentials.',
            ]);
        }

        if (!$user->is_staff_active || $user->is_blocked) {
            throw ValidationException::withMessages([
                'email' => 'This admin account is inactive. Please contact the super admin.',
            ]);
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_admin_login_at' => now()])->save();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($request->boolean('admin_login')) {
            return redirect()->route('admin.login-access')->with('status', 'Admin account signed out.');
        }

        return redirect('/');
    }
}
