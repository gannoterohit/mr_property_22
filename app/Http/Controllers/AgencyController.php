<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * Display the public profile of a verified broker / agency.
     */
    public function show(User $user, ?Request $request = null)
    {
        $request = $request ?? request();

        // Only allow active brokers
        if ($user->role !== 'broker' || !$user->is_broker_active) {
            abort(404, 'Agency profile not found or inactive.');
        }

        $query = Room::publicVisible()
            ->where(function ($q) use ($user) {
                $q->where('broker_id', $user->id)
                  ->orWhere('user_id', $user->id);
            })
            ->with(['propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption']);

        // Filters
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type_option_id', $request->room_type);
        }

        if ($request->filled('furnishing')) {
            $query->where('furnishing_option_id', $request->furnishing);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'featured');
        if ($sortBy === 'rent_asc') {
            $query->orderBy('rent', 'asc');
        } elseif ($sortBy === 'rent_desc') {
            $query->orderBy('rent', 'desc');
        } elseif ($sortBy === 'newest') {
            $query->latest();
        } else {
            $query->orderByDesc('is_featured')->latest();
        }

        $properties = $query->paginate(12)->withQueryString();

        // Agency statistics
        $totalListingsCount = Room::publicVisible()
            ->where(function ($q) use ($user) {
                $q->where('broker_id', $user->id)->orWhere('user_id', $user->id);
            })->count();

        $activeCities = Room::publicVisible()
            ->where(function ($q) use ($user) {
                $q->where('broker_id', $user->id)->orWhere('user_id', $user->id);
            })
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city');

        // Clean phone digits for WhatsApp
        $rawPhone = trim((string) ($user->phone ?? ''));
        $digits = preg_replace('/\D+/', '', $rawPhone);
        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = '91' . substr($digits, 1);
        }
        $agencyName = $user->agency_name ?: ($user->name . ' Real Estate');
        $siteName = \App\Models\Setting::get('website_name', 'RoomRental');
        $waMessage = "Hello {$agencyName}! Maine aapki agency profile {$siteName} par dekhi hai. Mujhe rental property ke baare mein baat karni hai.";
        $waLink = "https://wa.me/{$digits}?text=" . rawurlencode($waMessage);

        return view('agency.show', compact(
            'user',
            'properties',
            'totalListingsCount',
            'activeCities',
            'waLink',
            'rawPhone',
            'digits',
            'agencyName'
        ));
    }
}
