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
            ->with(['user:id,name,avatar', 'propertyType', 'propertyCategory']);

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

        $otherRooms = Room::query()
            ->where('status', 'active')
            ->where('listing_fee_paid', true)
            ->where('listing_status', 'approved')
            ->when($cityContext['activeCityName'], fn($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
            ->with(['user:id,name,avatar', 'propertyType', 'propertyCategory'])
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(32)
            ->get();

        $otherRoomGroups = $otherRooms->filter(fn($room) => $room->propertyType?->id)
            ->groupBy(fn($room) => $room->propertyType->id)
            ->map(function ($group, $typeId) {
                $first = $group->first();

                return (object)[
                    'label' => $first->propertyType->name,
                    'params' => ['property_type_id' => $typeId],
                    'rooms' => $group->take(4),
                ];
            });

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

        $otherRoomGroups = $otherRoomGroups->filter(function ($group) {
            return $group->rooms->count() > 0;
        });

        $popularCities = CityOperations::selectorCities();

        // Room categories with dynamic counts from DB
        $propertyTypes = \App\Models\PropertyType::where('status', true)
            ->orderBy('name')
            ->get();

        $propertyCategories = Room::select('property_category_id', \DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->where('listing_status', 'approved')
            ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
            ->whereNotNull('property_category_id')
            ->groupBy('property_category_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                $category = \App\Models\PropertyCategory::find($item->property_category_id);
                if (! $category) {
                    return null;
                }

                $item->label = $category->name;
                $item->property_type_id = $category->property_type_id;
                $item->icon = 'fas fa-building';
                return $item;
            })
            ->filter()
            ->values();

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

        return view('home.index', compact(
            'rooms', 'otherRooms', 'otherRoomGroups', 'popularCities', 'propertyTypes', 'propertyCategories', 'latestBlogs',
            'heroRoom', 'totalRooms', 'totalOwners', 'totalUsers', 'totalAreas',
            'cityContext'
        ));
    }
}
