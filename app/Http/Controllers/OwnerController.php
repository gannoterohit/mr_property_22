<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class OwnerController extends Controller
{
    public function dashboard()
    {
        $rooms = Room::where('user_id', Auth::id())->count();
        $contactUnlocks = \App\Models\Enquiry::where('unlocked', true)->whereHas('room', function ($q) {
            $q->where('user_id', Auth::id());
        })->count();
        $featuredRooms = Room::where('user_id', Auth::id())
            ->where('is_featured', true)
            ->count();
        $activeRooms = Room::where('user_id', Auth::id())
            ->where('status', 'active')
            ->count();
        $recentRooms = Room::where('user_id', Auth::id())
            ->latest()
            ->take(3)
            ->get();

        // Active subscription plan
        $activePlan = \App\Models\Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->with('plan')
            ->latest()
            ->first();

        return view('owner.dashboard', compact('rooms', 'activeRooms', 'contactUnlocks', 'featuredRooms', 'recentRooms', 'activePlan'));
    }

    public function rooms()
    {
        $myRooms = Room::where('user_id', Auth::id())
            ->latest()
            ->paginate(9);

        $roomCounts = [
            'all' => Room::where('user_id', Auth::id())->count(),
            'active' => Room::where('user_id', Auth::id())->where('status', 'active')->count(),
            'pending' => Room::where('user_id', Auth::id())->where('status', 'pending')->count(),
            'booked' => Room::where('user_id', Auth::id())->where('status', 'booked')->count(),
        ];

        return view('owner.rooms.index', compact('myRooms', 'roomCounts'));
    }

    public function enquiries()
    {
        $roomIds = Room::where('user_id', Auth::id())->pluck('id');

        $enquiries = \App\Models\Enquiry::whereIn('room_id', $roomIds)
            ->where('unlocked', true)
            ->with(['user:id,name', 'room:id,title,slug,city,photo,photos,status,user_id', 'payment:id,gateway,amount,status'])
            ->latest('unlocked_at')
            ->paginate(15);

        $stats = [
            'total' => \App\Models\Enquiry::whereIn('room_id', $roomIds)->where('unlocked', true)->count(),
            'today' => \App\Models\Enquiry::whereIn('room_id', $roomIds)->where('unlocked', true)->whereDate('unlocked_at', today())->count(),
            'rooms' => \App\Models\Enquiry::whereIn('room_id', $roomIds)->where('unlocked', true)->distinct('room_id')->count('room_id'),
        ];

        return view('owner.enquiries.index', compact('enquiries', 'stats'));
    }
}
