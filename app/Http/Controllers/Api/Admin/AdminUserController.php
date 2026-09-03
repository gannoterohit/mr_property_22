<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Models\Room;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends BaseApiController
{
    public function createUser(Request $request)
    {
        return $this->createMember($request, 'user');
    }

    /**
     * List all regular users
     */
    public function users(Request $request)
    {
        $query = User::withTrashed()->where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_blocked', false)->whereNull('deleted_at');
        } elseif ($request->status === 'blocked') {
            $query->where('is_blocked', true)->whereNull('deleted_at');
        } elseif ($request->status === 'deleted') {
            $query->onlyTrashed();
        }

        $users = $query->latest()->paginate($request->get('limit', 15));
        return $this->sendSuccess($users);
    }

    /**
     * Get user detail
     */
    public function userDetail($id)
    {
        $user = User::withTrashed()->with(['payments', 'subscriptions.plan', 'complaints', 'enquiries.room'])->where('role', 'user')->find($id);
        if (!$user) return $this->sendError('User not found', [], 404);
        return $this->sendSuccess($user);
    }

    /**
     * Toggle block user
     */
    public function toggleBlockUser($id)
    {
        $user = User::where('role', 'user')->find($id);
        if (!$user) return $this->sendError('User not found', [], 404);
        $blocking = ! $user->is_blocked;
        $user->update([
            'is_blocked' => $blocking,
            'block_reason' => $blocking ? 'Blocked by administrator' : null,
        ]);
        return $this->sendSuccess(['is_blocked' => $user->is_blocked], $user->is_blocked ? 'User blocked' : 'User unblocked');
    }

    public function updateUser(Request $request, $id)
    {
        return $this->updateMember($request, $id, 'user');
    }

    public function deleteUser($id)
    {
        return $this->deleteMember($id, 'user');
    }

    public function updateMemberNotes(Request $request, $id)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:5000']]);
        $member = User::withTrashed()->find($id);
        if (!$member || $member->role === 'admin') return $this->sendError('Member not found', [], 404);
        $member->update($data);
        return $this->sendSuccess($member, 'Member notes updated');
    }

    public function restoreMember($id)
    {
        $member = User::onlyTrashed()->find($id);
        if (!$member || $member->role === 'admin') return $this->sendError('Deleted member not found', [], 404);
        $member->restore();
        return $this->sendSuccess($member->fresh(), 'Member restored');
    }

    /**
     * List all owners
     */
    public function owners(Request $request)
    {
        $query = User::withTrashed()->where('role', 'owner')->withCount('rooms');
        if($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->status === 'active') {
            $query->where('is_blocked', false)->whereNull('deleted_at');
        } elseif ($request->status === 'blocked') {
            $query->where('is_blocked', true)->whereNull('deleted_at');
        } elseif ($request->status === 'deleted') {
            $query->onlyTrashed();
        }

        $owners = $query->latest()->paginate($request->get('limit', 15));
        return $this->sendSuccess($owners);
    }

    /**
     * Get owner detail
     */
    public function ownerDetail($id)
    {
        $owner = User::withTrashed()->where('role', 'owner')->find($id);
        if (!$owner) return $this->sendError('Owner not found', [], 404);
        $rooms = Room::where('user_id', $id)->latest()->paginate(10);
        return $this->sendSuccess([
            'owner' => $owner,
            'rooms' => RoomResource::collection($rooms),
        ]);
    }

    /**
     * Toggle block owner
     */
    public function toggleBlockOwner($id)
    {
        $owner = User::where('role', 'owner')->find($id);
        if (!$owner) return $this->sendError('Owner not found', [], 404);
        $blocking = ! $owner->is_blocked;
        $owner->update([
            'is_blocked' => $blocking,
            'block_reason' => $blocking ? 'Blocked by administrator' : null,
        ]);
        return $this->sendSuccess(['is_blocked' => $owner->is_blocked], $owner->is_blocked ? 'Owner blocked' : 'Owner unblocked');
    }

    /**
     * Admin create owner
     */
    public function createOwner(Request $request)
    {
        return $this->createMember($request, 'owner');
    }

    public function updateOwner(Request $request, $id)
    {
        return $this->updateMember($request, $id, 'owner');
    }

    public function deleteOwner($id)
    {
        return $this->deleteMember($id, 'owner');
    }

    private function createMember(Request $request, string $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'verification_status' => ['nullable', Rule::in(['pending', 'under_review', 'verified', 'rejected'])],
            'wallet_balance' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'free_unlocks' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'is_blocked' => ['nullable', 'boolean'],
            'block_reason' => ['nullable', 'string', 'max:255'],
            'email_verified' => ['nullable', 'boolean'],
        ]);
        $verificationStatus = $data['verification_status'] ?? 'verified';
        $isBlocked = $request->boolean('is_blocked');
        $blockReason = $data['block_reason'] ?? 'Blocked by administrator';
        unset($data['email_verified'], $data['is_blocked'], $data['block_reason']);
        $member = User::create(array_merge($data, [
            'role' => $role,
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => $request->boolean('email_verified', true) ? now() : null,
            'is_verified' => $verificationStatus === 'verified',
            'verified_at' => $verificationStatus === 'verified' ? now() : null,
            'verification_status' => $verificationStatus,
            'is_blocked' => $isBlocked,
            'block_reason' => $isBlocked ? $blockReason : null,
            'is_staff_active' => true,
        ]));
        return $this->sendSuccess($member, ucfirst($role) . ' created', 201);
    }

    private function updateMember(Request $request, int $id, string $role)
    {
        $member = User::where('role', $role)->find($id);
        if (!$member) return $this->sendError(ucfirst($role) . ' not found', [], 404);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($member->id)],
            'verification_status' => ['nullable', Rule::in(['pending', 'under_review', 'verified', 'rejected'])],
            'wallet_balance' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'free_unlocks' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'is_blocked' => ['nullable', 'boolean'],
            'block_reason' => ['nullable', 'string', 'max:255'],
            'email_verified' => ['nullable', 'boolean'],
        ]);
        if ($request->has('verification_status')) {
            $data['is_verified'] = $data['verification_status'] === 'verified';
            $data['verified_at'] = $data['is_verified'] ? ($member->verified_at ?: now()) : null;
        }
        if ($request->has('email_verified')) {
            $data['email_verified_at'] = $request->boolean('email_verified') ? ($member->email_verified_at ?: now()) : null;
            unset($data['email_verified']);
        }
        if ($request->has('is_blocked')) {
            $data['is_blocked'] = $request->boolean('is_blocked');
            $data['block_reason'] = $data['is_blocked'] ? ($data['block_reason'] ?? 'Blocked by administrator') : null;
        }
        $member->update($data);
        return $this->sendSuccess($member->fresh(), ucfirst($role) . ' updated');
    }

    private function deleteMember(int $id, string $role)
    {
        $member = User::where('role', $role)->find($id);
        if (!$member) return $this->sendError(ucfirst($role) . ' not found', [], 404);
        $member->delete();
        return $this->sendSuccess([], ucfirst($role) . ' deleted');
    }
}
