<?php

namespace App\Http\Controllers;

use App\Models\PropertyType;
use App\Models\Room;
use App\Models\RoomDraft;
use App\Models\RoomOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BrokerRoomController extends Controller
{
    public function create(Request $request)
    {
        $propertyTypes = PropertyType::orderBy('name')->get(['id', 'name']);
        $amenities = RoomOption::optionsFor('amenity')->pluck('label')->all();
        if (empty($amenities)) {
            $amenities = ['WiFi', 'Parking', 'AC', 'Power Backup', 'Lift', 'Security', 'CCTV'];
        }

        $storeRoute = route('agent.rooms.store');
        $draftsIndex = route('agent.rooms.drafts');

        return view('broker.rooms.create-multistep', compact('propertyTypes', 'amenities', 'storeRoute', 'draftsIndex'));
    }

    public function duplicate(Room $room)
    {
        $broker = Auth::user();
        if ($room->user_id !== $broker->id && $room->broker_id !== $broker->id) {
            abort(403, 'Unauthorized');
        }

        $newRoom = $room->replicate([
            'slug',
            'is_featured',
            'listing_payment_id',
            'created_at',
            'updated_at',
        ]);

        $newRoom->title = '[Copy] ' . $room->title;
        $newRoom->slug = Room::generateUniqueSlug($newRoom->title);
        $newRoom->status = 'pending';
        $newRoom->listing_status = 'pending';
        $newRoom->is_featured = false;
        $newRoom->listing_fee_paid = true;
        $newRoom->user_id = $broker->id;
        $newRoom->broker_id = $broker->id;
        $newRoom->listed_by = 'broker';
        $newRoom->listing_type = 'broker';

        $newRoom->expires_at = null; // Listings are credit/count based, no day expiration
        $newRoom->save();

        return redirect()->route('agent.rooms.edit', $newRoom)
            ->with('success', 'Property duplicated successfully! You can now edit unit number, rent, and publish.');
    }
}
