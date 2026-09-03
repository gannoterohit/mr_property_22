<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Room;
use App\Models\Enquiry;
use App\Models\Wishlist;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiOwnerController extends BaseApiController
{
    /**
     * Owner Dashboard — full stats + recent rooms
     */
    public function dashboard()
    {
        $user = Auth::user();

        $totalRooms     = Room::where('user_id', $user->id)->count();
        $activeRooms    = Room::where('user_id', $user->id)->where('status', 'active')->count();
        $bookedRooms    = Room::where('user_id', $user->id)->where('status', 'booked')->count();
        $pendingRooms   = Room::where('user_id', $user->id)->where('listing_status', 'pending')->count();
        $featuredRooms  = Room::where('user_id', $user->id)->where('is_featured', true)->count();
        $totalEnquiries = Enquiry::whereIn('room_id', Room::where('user_id', $user->id)->pluck('id'))
            ->where('unlocked', true)
            ->whereNotNull('unlocked_at')
            ->count();

        $activeSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('plan', fn ($q) => $q->where('type', 'owner')->where('is_active', true))
            ->with('plan')
            ->first();

        $recentRooms = Room::where('user_id', $user->id)->latest()->limit(5)->get();

        return $this->sendSuccess([
            'stats' => [
                'total_rooms'     => $totalRooms,
                'active_rooms'    => $activeRooms,
                'booked_rooms'    => $bookedRooms,
                'pending_rooms'   => $pendingRooms,
                'featured_rooms'  => $featuredRooms,
                'total_enquiries' => $totalEnquiries,
            ],
            'wallet' => [
                'points'          => (float) ($user->wallet ?? 0),
                'balance'         => (float) ($user->wallet_balance ?? 0),
            ],
            'active_subscription' => $activeSubscription ? [
                'id'              => $activeSubscription->id,
                'plan_name'       => $activeSubscription->plan->name,
                'end_date'        => $activeSubscription->end_date->toDateString(),
                'listing_limit'   => $activeSubscription->plan->listing_limit,
                'used_listings'   => $activeSubscription->usages()->where('usage_type', 'listing')->count(),
            ] : null,
            'recent_rooms' => RoomResource::collection($recentRooms),
        ]);
    }


    /**
     * Owner's enquiry/unlock history
     */
    public function enquiries(Request $request)
    {
        $roomIds = Room::where('user_id', Auth::id())->pluck('id');

        $enquiries = Enquiry::whereIn('room_id', $roomIds)
            ->where('unlocked', true)
            ->whereNotNull('unlocked_at')
            ->with([
                'user:id,name',
                'room:id,title,slug,city,status,user_id,photo,photos',
                'payment:id,amount,gateway,status',
            ])
            ->latest('unlocked_at')
            ->paginate($request->get('limit', 15));

        $todayCount = Enquiry::whereIn('room_id', $roomIds)
            ->where('unlocked', true)
            ->whereNotNull('unlocked_at')
            ->whereDate('unlocked_at', today())
            ->count();

        $distinctRooms = Enquiry::whereIn('room_id', $roomIds)
            ->where('unlocked', true)
            ->whereNotNull('unlocked_at')
            ->distinct('room_id')
            ->count('room_id');

        return $this->sendSuccess([
            'enquiries' => $enquiries,
            'stats' => [
                'total'          => $enquiries->total(),
                'today'          => $todayCount,
                'distinct_rooms' => $distinctRooms,
            ],
        ]);
    }
}
