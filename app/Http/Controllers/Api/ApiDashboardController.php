<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Room;
use App\Models\Enquiry;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiDashboardController extends BaseApiController
{
    /**
     * Get dashboard statistics
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $stats = [
            'wallet_points' => (float) ($user->wallet ?? 0),
            'wallet_balance' => (float) ($user->wallet_balance ?? 0),
        ];

        if ($user->role === 'owner') {
            $stats['role_stats'] = [
                'total_rooms' => Room::where('user_id', $user->id)->count(),
                'booked_rooms' => Room::where('user_id', $user->id)->where('status', 'booked')->count(),
                'featured_rooms' => Room::where('user_id', $user->id)->where('is_featured', true)->count(),
                'active_rooms' => Room::where('user_id', $user->id)->where('status', 'active')->count(),
                'total_enquiries' => Enquiry::whereIn('room_id', Room::where('user_id', $user->id)->pluck('id'))->count(),
            ];
        } else {
            $stats['role_stats'] = [
                'unlocked_contacts' => Enquiry::where('user_id', $user->id)->where('unlocked', true)->count(),
                'wishlist_count' => Wishlist::where('user_id', $user->id)->count(),
            ];
        }

        return $this->sendSuccess($stats, 'Dashboard data fetched successfully');
    }

    /**
     * Get referral stats and link
     */
    public function referralStats()
    {
        $user = Auth::user();
        
        $referrals = \App\Models\User::where('referred_by_id', $user->id)
            ->select('name', 'email', 'created_at')
            ->latest()
            ->get();

        return $this->sendSuccess([
            'referral_code' => $user->referral_code,
            'referral_link' => route('referral.track', ['code' => $user->referral_code]),
            'total_referrals' => $referrals->count(),
            'referrals' => $referrals,
            'rewards' => [
                'refer_reward' => (float) \App\Models\Setting::get('referral_reward', 10),
                'join_reward' => (float) \App\Models\Setting::get('join_reward', 5),
            ]
        ]);
    }
}
