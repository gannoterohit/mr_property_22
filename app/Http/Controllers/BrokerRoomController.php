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
}
