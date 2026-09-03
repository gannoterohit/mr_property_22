<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('status', 'Invalid social login provider.');
        }

        $enabledKey = $provider . '_login_enabled';
        if (!filter_var(\App\Models\Setting::get($enabledKey, '0'), FILTER_VALIDATE_BOOLEAN)) {
            return redirect()->route('login')->with('status', ucfirst($provider) . ' login is currently disabled.');
        }

        $clientId = \App\Models\Setting::get($provider . '_client_id');
        $clientSecret = \App\Models\Setting::get($provider . '_client_secret');
        $redirectUrl = \App\Models\Setting::get($provider . '_redirect_url');

        if (!$clientId || !$clientSecret || !$redirectUrl) {
            return redirect()->route('login')->with('status', ucfirst($provider) . ' login is not configured properly.');
        }

        config(['services.' . $provider => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => $redirectUrl,
        ]]);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('login')->with('status', 'Invalid social login provider.');
        }

        $clientId = \App\Models\Setting::get($provider . '_client_id');
        $clientSecret = \App\Models\Setting::get($provider . '_client_secret');
        $redirectUrl = \App\Models\Setting::get($provider . '_redirect_url');

        if (!$clientId || !$clientSecret || !$redirectUrl) {
            return redirect()->route('login')->with('status', ucfirst($provider) . ' login is not configured properly.');
        }

        config(['services.' . $provider => [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect' => $redirectUrl,
        ]]);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', 'Social login failed. Please try again.');
        }

        if (empty($socialUser->email)) {
            return redirect()->route('login')->with('status', 'Email is required for social login. Please enable email access from your account.');
        }

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'User',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(32)),
                    'role' => 'user',
                    'avatar' => $socialUser->getAvatar() ?: null,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            } else {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ]);
        }

        Auth::login($user, true);

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'owner') {
            return redirect()->route('owner.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
