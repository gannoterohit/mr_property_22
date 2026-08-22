<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'success' => false,
                    'message' => 'Please login to continue.',
                    'data' => null,
                    'errors' => (object) [],
                ], 401);
            }
            return redirect('/login');
        }

        $allowedRoles = array_map('trim', explode(',', $role));
        $hasRole = in_array(Auth::user()->role, $allowedRoles, true);

        if (!$hasRole) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'success' => false,
                    'message' => 'You do not have '.$role.' access.',
                    'data' => null,
                    'errors' => (object) [],
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
