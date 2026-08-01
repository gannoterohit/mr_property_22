<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformAvailability
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->alwaysAllowed($request)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user?->role === 'admin') {
            return $next($request);
        }

        if ($this->enabled('maintenance_mode')) {
            return $this->unavailable($request, 'maintenance');
        }

        if (!$this->enabled('registration_enabled', true) && ($request->routeIs('register', 'verify.registration.otp')
            || $request->is('api/v1/auth/register'))) {
            return $this->unavailable($request, 'registration');
        }

        if (!$this->enabled('new_listings_enabled', true) && ($request->routeIs('rooms.create', 'rooms.store')
            || ($request->is('api/v1/owner/rooms') && $request->isMethod('post')))) {
            return $this->unavailable($request, 'listings');
        }

        if (!$this->enabled('payments_enabled', true) && ($request->routeIs('unlock.contact', 'razorpay.createOrder', 'subscription.purchase', 'subscribe')
            || $request->is('api/v1/unlock/*', 'api/v1/payments/*', 'api/v1/subscriptions/purchase'))) {
            return $this->unavailable($request, 'payments');
        }

        if (!$this->enabled('wallet_enabled', true) && ($request->routeIs('wallet', 'wallet.*')
            || $request->is('api/v1/wallet', 'api/v1/wallet/*')
            || $request->input('payment_method') === 'wallet')) {
            return $this->unavailable($request, 'wallet');
        }

        if (!$this->enabled('referral_enabled', true) && ($request->routeIs('referral.*')
            || $request->is('api/v1/referral-stats', 'api/v1/referral/*'))) {
            return $this->unavailable($request, 'referral');
        }

        if ($user?->role === 'owner' && !$this->enabled('owner_panel_enabled', true)
            && $this->isOwnerWorkspace($request)) {
            return $this->unavailable($request, 'owner_panel');
        }

        if ($user?->role === 'user' && !$this->enabled('user_panel_enabled', true)
            && $this->isUserWorkspace($request)) {
            return $this->unavailable($request, 'user_panel');
        }

        return $next($request);
    }

    private function alwaysAllowed(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'admin-login', 'login', 'logout', 'up', 'storage/*', 'build/*')
            || $request->routeIs('login', 'logout', 'razorpay.verify', 'razorpay.webhook')
            || $request->is(
                'webhook/razorpay',
                'api/v1/webhook/razorpay',
                'api/v1/settings',
                'api/v1/auth/send-otp',
                'api/v1/auth/login',
                'api/v1/admin/auth/login'
            );
    }

    private function enabled(string $key, bool $default = false): bool
    {
        return filter_var(Setting::get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    }

    private function isOwnerWorkspace(Request $request): bool
    {
        return $request->routeIs(
            'owner.*', 'dashboard', 'rooms.create', 'rooms.store', 'rooms.edit', 'rooms.update',
            'rooms.destroy', 'rooms.featured', 'rooms.markBooked', 'rooms.markAvailable',
            'profile.*', 'plans', 'subscription.purchase', 'subscribe', 'wallet', 'wallet.*',
            'referral.*', 'complaints.*'
        ) || $request->is('api/v1/owner', 'api/v1/owner/*', 'api/v1/profile*', 'api/v1/wallet*',
            'api/v1/payments*', 'api/v1/subscriptions*', 'api/v1/complaints*', 'api/v1/complaint-options');
    }

    private function isUserWorkspace(Request $request): bool
    {
        return $request->routeIs(
            'dashboard', 'profile.*', 'plans', 'subscription.purchase', 'subscribe',
            'wallet', 'wallet.*', 'referral.*', 'wishlist.*', 'complaints.*',
            'city-alerts.*', 'unlock.contact'
        ) || $request->is('api/v1/dashboard', 'api/v1/profile*', 'api/v1/wallet*', 'api/v1/wishlist*',
            'api/v1/city-alerts*', 'api/v1/unlock*', 'api/v1/payments*', 'api/v1/subscriptions*',
            'api/v1/referral-stats', 'api/v1/complaints*', 'api/v1/complaint-options');
    }

    private function unavailable(Request $request, string $reason): Response
    {
        $messages = [
            'registration' => ['Registration is temporarily unavailable', 'New registrations are paused for a short time. Existing members can still log in.'],
            'listings' => ['New listings are temporarily paused', 'Owners can manage existing properties, but new property submission is currently unavailable.'],
            'payments' => ['Payments are temporarily unavailable', 'Purchases and contact unlocks are paused. No amount has been charged for this attempt.'],
            'wallet' => ['Wallet is temporarily unavailable', 'Wallet balance and wallet payments are currently disabled by the administrator.'],
            'referral' => ['Referral system is temporarily unavailable', 'Referral rewards and referral statistics are currently disabled by the administrator.'],
            'owner_panel' => ['Owner panel is temporarily unavailable', 'Owner workspace access is paused while we complete essential maintenance.'],
            'user_panel' => ['User panel is temporarily unavailable', 'User workspace access is paused while we complete essential maintenance.'],
        ];

        $title = $reason === 'maintenance'
            ? Setting::get('maintenance_title', 'Website is currently under maintenance')
            : $messages[$reason][0];
        $message = $reason === 'maintenance'
            ? Setting::get('maintenance_message', 'We are improving your experience and will be back soon.')
            : $messages[$reason][1];

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'unavailable',
                'success' => false,
                'message' => $message,
                'reason' => $reason,
                'data' => null,
                'errors' => (object) [],
            ], 503);
        }

        return response()->view('maintenance', [
            'title' => $title,
            'message' => $message,
            'reopeningAt' => $reason === 'maintenance' ? Setting::get('maintenance_reopening_at') : null,
            'reason' => $reason,
        ], 503);
    }
}
