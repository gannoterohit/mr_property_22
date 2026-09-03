<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'wallet_points' => (float) ($this->wallet ?? 0),
            'wallet_balance' => (float) ($this->wallet_balance ?? 0),
            'referral_code' => $this->referral_code,
            'is_blocked' => (bool) $this->is_blocked,
            'is_verified' => (bool) $this->is_verified,
            'verification_status' => $this->verification_status,
            'verified_at' => $this->verified_at,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
