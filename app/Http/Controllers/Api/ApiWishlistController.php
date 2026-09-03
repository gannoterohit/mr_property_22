<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Room;
use App\Http\Resources\RoomResource;
use Illuminate\Support\Facades\Auth;

class ApiWishlistController extends BaseApiController
{
    /**
     * Get user wishlist
     */
    public function index()
    {
        $userId = Auth::id();
        $roomIds = Wishlist::where('user_id', $userId)->pluck('room_id');
        $rooms = Room::with('owner')->whereIn('id', $roomIds)->get();

        return $this->sendSuccess(RoomResource::collection($rooms));
    }

    /**
     * Toggle room in wishlist
     */
    public function toggle($roomId)
    {
        $userId = Auth::id();
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('room_id', $roomId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return $this->sendSuccess(['added' => false], 'Removed from wishlist');
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'room_id' => $roomId
            ]);
            return $this->sendSuccess(['added' => true], 'Added to wishlist');
        }
    }
}
