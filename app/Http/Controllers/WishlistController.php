<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlists = Wishlist::where('user_id', Auth::id())->with('room')->latest()->get();
        return view('account.wishlist', compact('wishlists'));
    }

    public function toggle(Request $request, $roomId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login first'], 401);
        }

        $wishlist = Wishlist::where('user_id', Auth::id())->where('room_id', $roomId)->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json(['success' => true, 'status' => 'removed', 'message' => 'Removed from wishlist']);
        } else {
            Wishlist::create([
                'user_id' => Auth::id(),
                'room_id' => $roomId
            ]);
            return response()->json(['success' => true, 'status' => 'added', 'message' => 'Added to wishlist']);
        }
    }
}
