<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * Display a public directory of all verified real estate agencies.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'broker')
            ->where('is_broker_active', true)
            ->withCount([
                'rooms as active_rooms_count' => function ($q) {
                    $q->publicVisible();
                }
            ]);

        // Search query (agency name, agent name, license, address)
        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('agency_name', 'like', $term)
                  ->orWhere('name', 'like', $term)
                  ->orWhere('agency_address', 'like', $term)
                  ->orWhere('broker_license', 'like', $term)
                  ->orWhereHas('rooms', function ($rq) use ($term) {
                      $rq->publicVisible()->where(function ($sq) use ($term) {
                          $sq->where('city', 'like', $term)
                             ->orWhere('address', 'like', $term)
                             ->orWhere('title', 'like', $term);
                      });
                  });
            });
        }

        // City filter
        if ($request->filled('city')) {
            $city = trim($request->city);
            $query->whereHas('rooms', function ($rq) use ($city) {
                $rq->publicVisible()->where('city', $city);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'popular');
        if ($sortBy === 'properties') {
            $query->orderByDesc('active_rooms_count');
        } elseif ($sortBy === 'name') {
            $query->orderBy('agency_name', 'asc')->orderBy('name', 'asc');
        } else {
            $query->orderByDesc('active_rooms_count')->latest('id');
        }

        $agencies = $query->paginate(12)->withQueryString();

        // Eager load active room cities for displayed agencies
        $agencies->load(['rooms' => function ($rq) {
            $rq->publicVisible()->select('rooms.id', 'rooms.broker_id', 'rooms.user_id', 'rooms.city');
        }]);

        // Get list of distinct cities where active broker rooms exist
        $availableCities = Room::publicVisible()
            ->where('listing_type', 'broker')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values();

        $totalAgenciesCount = User::where('role', 'broker')->where('is_broker_active', true)->count();
        $totalBrokerProperties = Room::publicVisible()->where('listing_type', 'broker')->count();

        return view('agency.index', compact('agencies', 'availableCities', 'totalAgenciesCount', 'totalBrokerProperties'));
    }

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
