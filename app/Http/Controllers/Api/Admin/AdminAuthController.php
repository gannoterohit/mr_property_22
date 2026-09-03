<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends BaseApiController
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $admin = User::with('adminRole')->where('email', $credentials['email'])->first();

        if (!$admin || $admin->role !== 'admin' || !Hash::check($credentials['password'], (string) $admin->password)) {
            return $this->sendError('Invalid admin credentials.', [], 401);
        }

        if ($admin->is_blocked || !$admin->is_staff_active) {
            return $this->sendError('This admin account is inactive.', [], 403);
        }

        $admin->forceFill(['last_admin_login_at' => now()])->save();
        $token = $admin->createToken($credentials['device_name'] ?? 'admin_app', ['admin'])->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'token_type' => 'Bearer',
            'admin' => new UserResource($admin),
            'permissions' => $admin->admin_role_id
                ? ($admin->adminRole?->permissions ?? [])
                : ['*'],
        ], 'Admin login successful');
    }

    public function me(Request $request)
    {
        $admin = $request->user()->loadMissing('adminRole');

        return $this->sendSuccess([
            'admin' => new UserResource($admin),
            'permissions' => $admin->admin_role_id
                ? ($admin->adminRole?->permissions ?? [])
                : ['*'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->sendSuccess([], 'Admin logged out successfully');
    }
}
