<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin' || !$user->is_staff_active) abort(403, 'This admin account is inactive.');
        if ($user->currentAccessToken() && !$user->tokenCan('admin')) {
            abort(403, 'This token is not authorized for the admin API.');
        }

        $permission = $this->permissionFor($request);
        if ($permission && !$user->hasAdminPermission($permission)) abort(403, 'You do not have permission to access this admin module.');

        return $next($request);
    }

    private function permissionFor(Request $request): ?string
    {
        $route = (string) $request->route()?->getName();
        $write = !in_array($request->method(), ['GET', 'HEAD'], true);
        if ($request->is('api/v1/admin/*')) {
            $path = ltrim((string) $request->path(), '/');
            if (str_contains($path, '/staff') || str_contains($path, '/roles') || str_contains($path, '/permission-catalog')) return 'staff.manage';
            if (str_contains($path, '/activity-logs')) return 'activity.view';
            if (str_contains($path, '/rooms') || str_contains($path, '/room-options') || str_contains($path, '/rejection-reasons')) return $write ? 'listings.manage' : 'listings.view';
            if (str_contains($path, '/users') || str_contains($path, '/owners') || str_contains($path, '/members')) return $write ? 'people.manage' : 'people.view';
            if (str_contains($path, '/complaints') || str_contains($path, '/complaint-options') || str_contains($path, '/contact-messages') || str_contains($path, '/city-alerts') || str_contains($path, '/subscribers')) return $write ? 'support.manage' : 'support.view';
            if (str_contains($path, '/payments') || str_contains($path, '/payouts') || str_contains($path, '/plans')) return $write ? 'finance.manage' : 'finance.view';
            if (str_contains($path, '/blogs') || str_contains($path, '/offers') || str_contains($path, '/pages') || str_contains($path, '/home-page')) return $write ? 'content.manage' : 'content.view';
            if (str_contains($path, '/reports') || str_contains($path, '/analytics') || str_contains($path, '/search-analytics') || str_contains($path, '/search-logs')) return $write ? 'reports.manage' : 'reports.view';
            if (str_contains($path, '/settings') || str_contains($path, '/maintenance') || str_contains($path, '/cities')) return 'settings.manage';
            if (str_ends_with($path, '/dashboard')) return 'dashboard.view';
        }
        if ($route === 'admin.dashboard') return 'dashboard.view';
        if (str_starts_with($route, 'admin.staff') || str_starts_with($route, 'admin.roles')) return 'staff.manage';
        if (str_starts_with($route, 'admin.activity')) return 'activity.view';
        if (str_starts_with($route, 'admin.rooms') || $route === 'admin.all-rooms' || str_starts_with($route, 'admin.room-options') || str_starts_with($route, 'admin.rejection-reasons')) return $write ? 'listings.manage' : 'listings.view';
        if (str_starts_with($route, 'admin.users') || str_starts_with($route, 'admin.owners') || str_starts_with($route, 'admin.members')) return $write ? 'people.manage' : 'people.view';
        if (str_starts_with($route, 'admin.complaints') || str_starts_with($route, 'admin.contact-messages') || str_starts_with($route, 'admin.city-alerts') || str_starts_with($route, 'admin.subscribers')) return $write ? 'support.manage' : 'support.view';
        if (str_starts_with($route, 'admin.payments') || str_starts_with($route, 'admin.payouts') || str_starts_with($route, 'admin.plans')) return $write ? 'finance.manage' : 'finance.view';
        if (str_starts_with($route, 'admin.blogs') || str_starts_with($route, 'admin.offers') || str_starts_with($route, 'admin.pages') || str_starts_with($route, 'admin.cms-pages') || str_starts_with($route, 'admin.home-page')) return $write ? 'content.manage' : 'content.view';
        if ($route === 'admin.reports' || str_starts_with($route, 'admin.analytics')) return $write ? 'reports.manage' : 'reports.view';
        if (str_starts_with($route, 'admin.settings') || str_starts_with($route, 'admin.maintenance') || str_starts_with($route, 'admin.data-maintenance') || str_starts_with($route, 'admin.cities')) return 'settings.manage';
        return null;
    }
}
