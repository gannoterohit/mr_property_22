<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\City;
use App\Models\User;
use App\Services\CityOperations;
use Illuminate\Cache\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LandingPageController extends Controller
{
    public function city(string $citySlug, Request $request)
    {
        $city = City::where('slug', $citySlug)->firstOrFail();
        session(['user_city' => $city->name]);
        $request->merge(['city' => $city->name]);

        return $this->index($request);
    }

    public function index(Request $request)
    {
        $query = Room::query()
            ->where('status', 'active')
            ->where('listing_fee_paid', true)
            ->where('listing_status', 'approved')
            ->with(['user:id,name,avatar']);

        $userCity = session('user_city');
        $locationVerified = session('location_verified', false);
        $lat = $request->lat ?: ($request->filled('city') ? null : session('user_lat'));
        $lng = $request->lng ?: ($request->filled('city') ? null : session('user_lng'));

        if ($request->filled('city')) {
            session(['user_city' => $request->city]);
            session()->forget('no_auto');

            if ($request->has('lat') && $request->has('lng')) {
                session(['user_lat' => $request->lat, 'user_lng' => $request->lng]);
                if ($request->has('verified')) {
                    session(['location_verified' => true]);
                }
            } else {
                $lat = $lng = null;
                session()->forget(['user_lat', 'user_lng', 'location_verified']);
            }
        } else {
            // Server-side IP-based city auto-detection fallback
            if (!session('no_auto') && !$request->filled('city')) {
                try {
                    $ip = $request->ip();
                    if (!in_array($ip, ['127.0.0.1', '::1']) && !str_starts_with($ip, '192.168.') && !str_starts_with($ip, '10.')) {
                        $geoResponse = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,city,lat,lon");
                        if ($geoResponse->successful()) {
                            $geo = $geoResponse->json();
                            if (($geo['status'] ?? '') === 'success' && !empty($geo['city'])) {
                                $detectedCity = $geo['city'];
                                session(['user_city' => $detectedCity, 'user_lat' => $geo['lat'], 'user_lng' => $geo['lon'], 'location_verified' => true]);
                                $userCity = $detectedCity;
                                $lat = $geo['lat'];
                                $lng = $geo['lon'];
                                $locationVerified = true;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        }

        $cityContext = CityOperations::resolve($request->input('city'), session('user_city'));
        CityOperations::applyRoomCity($query, $cityContext);

        if ($lat && $lng && $locationVerified && !$request->filled('city') && !$cityContext['isFallback']) {
            $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
                  ->orderBy('distance', 'asc');
        }

        $rooms = $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        if ($request->ajax()) {
            $view = '';
            foreach ($rooms as $room) {
                $view .= view('partials.mobile-room-card', compact('room'))->render();
            }

            return response()->json([
                'html' => $view,
                'hasMore' => $rooms->hasMorePages(),
            ]);
        }

        $popularCities = CityOperations::selectorCities();

        // Room categories with dynamic counts from DB
        $roomCategories = Room::select('room_type_option_id', \DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->where('listing_status', 'approved')
            ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
            ->whereNotNull('room_type_option_id')
            ->groupBy('room_type_option_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $option = \App\Models\RoomOption::find($item->room_type_option_id);
                $item->room_type_option_id = $item->room_type_option_id;
                $item->label = $option ? $option->label : 'Room';
                $item->icon  = 'fas fa-home';
                return $item;
            });

        $latestBlogs = \App\Models\Blog::where('is_published', true)->orderBy('created_at', 'desc')->take(3)->get();

        // Hero room — cheapest featured/active room in current city
        $heroRoom = Room::where('status', 'active')
            ->where('listing_status', 'approved')
            ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
            ->orderByDesc('is_featured')
            ->orderBy('rent', 'asc')
            ->first();

        // DB-based stats for hero section
        $totalRooms  = Room::where('status', 'active')->where('listing_status', 'approved')->count();
        $totalOwners = Room::where('status', 'active')->where('listing_status', 'approved')->distinct('user_id')->count('user_id');
        $totalUsers  = User::where('role', 'user')->count();
        $totalAreas  = Room::where('status', 'active')->where('listing_status', 'approved')
            ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
            ->distinct('city')->count('city');
        // Popular city areas — dynamically extracted from address field of active city
        $homeCity = $cityContext['activeCityName'] ?: 'Bhopal';
        $popularAreas = FacadesCache::remember('popular_areas_' . $homeCity . '_v2', 3600, function () use ($homeCity) {
            return Room::where('status', 'active')
                ->where('listing_status', 'approved')
                ->where('city', 'like', '%' . $homeCity . '%')
                ->get()
                ->map(function ($room) {
                    $address = $room->address;
                    $area = null;
                    
                    if (stripos($address, 'Arera Colony') !== false) $area = 'Arera Colony';
                    elseif (stripos($address, 'BHEL') !== false) $area = 'BHEL';
                    elseif (stripos($address, 'MANIT') !== false) $area = 'MANIT';
                    elseif (stripos($address, 'New Market') !== false) $area = 'New Market';
                    elseif (stripos($address, 'Kolar Road') !== false) $area = 'Kolar Road';
                    elseif (stripos($address, 'MP Nagar') !== false) $area = 'MP Nagar';
                    elseif (stripos($address, 'Vijay Nagar') !== false) $area = 'Vijay Nagar';
                    elseif (stripos($address, 'Palasia') !== false) $area = 'Palasia';
                    elseif (stripos($address, 'Koramangala') !== false) $area = 'Koramangala';
                    elseif (stripos($address, 'Whitefield') !== false) $area = 'Whitefield';
                    elseif (stripos($address, 'HSR Layout') !== false) $area = 'HSR Layout';
                    elseif (stripos($address, 'Jayanagar') !== false) $area = 'Jayanagar';
                    
                    if (!$area) {
                        $parts = array_map('trim', explode(',', $address));
                        if (count($parts) > 1) {
                            $area = $parts[0];
                        } else {
                            $area = $address ?: 'Central Area';
                        }
                    }
                    
                    $room->parsed_area = $area;
                    return $room;
                })
                ->groupBy('parsed_area')
                ->map(function ($group, $key) {
                    return (object)[
                        'area_name' => $key,
                        'total' => $group->count(),
                        'min_rent' => $group->min('rent')
                    ];
                })
                ->sortByDesc('total')
                ->take(8)
                ->values();
        });

        return view('home-marketplace', compact(
            'rooms', 'popularCities', 'roomCategories', 'latestBlogs',
            'heroRoom', 'totalRooms', 'totalOwners', 'totalUsers', 'totalAreas', 'popularAreas',
            'cityContext'
        ));
    }
}
