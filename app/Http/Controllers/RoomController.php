<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Enquiry;
use App\Models\RoomOption;
use App\Models\PropertyCategory;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Models\SubscriptionUsage;
use App\Services\CityOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller {
    public function index(Request $request)
    {
        $query = Room::query();
    
        // Check if owner wants to see their OWN rooms (management view)
        $isOwnerManagementView = Auth::check() && Auth::user()->role === 'owner' && $request->get('view') === 'mine';

        if ($isOwnerManagementView) {
            $query->where('user_id', Auth::id());
        } 
        // Public/Explore view (default)
        else {
            $query->publicVisible();
        }
    
    // Search or Auto-Detect City
    if ($request->has('clear')) {
        session()->forget(['user_city', 'user_lat', 'user_lng', 'location_verified']);
        session(['no_auto' => true]); // Prevent auto-detection from re-triggering immediately
        return redirect()->route('rooms.index');
    }

    $userCity = session('user_city');
    $locationVerified = session('location_verified', false);

    // Prioritize Request > Session
    $lat = $request->lat ?: ($request->filled('city') ? null : session('user_lat'));
    $lng = $request->lng ?: ($request->filled('city') ? null : session('user_lng'));

    if ($request->filled('city')) {
        session(['user_city' => $request->city]);
        session()->forget('no_auto');

        if ($request->has('lat') && $request->has('lng')) {
            session(['user_lat' => $request->lat, 'user_lng' => $request->lng, 'location_verified' => true]);
        } else {
            // If they searched a new city manually, clear previous coordinates
            $lat = $lng = null;
            session()->forget(['user_lat', 'user_lng', 'location_verified']);
        }
    } else {
        // Server-side IP-based city auto-detection fallback
        // Runs only if no session city, no request city, and user hasn't opted out
        if (!session('no_auto') && !$request->filled('city')) {
            try {
                $ip = $request->ip();
                // Skip for localhost/private IPs
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
                // Fail silently — don't break page if geo API is down
            }
        }
    }

    $cityContext = CityOperations::resolve($request->input('city'), session('user_city'));
    if (!$isOwnerManagementView) {
        CityOperations::applyRoomCity($query, $cityContext);
    }

    if ($lat && $lng && $locationVerified && !$cityContext['isFallback']) {
        // SORT BY DISTANCE
        $query->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance", [$lat, $lng, $lat])
              ->orderBy('distance', 'asc');
    }
    // No more hidden fallback to session('user_city') if not verified or requested

    // Rent range filter
    if ($request->filled('min_rent')) {
        $query->where('rent', '>=', $request->min_rent);
    }

    if ($request->filled('max_rent')) {
        $query->where('rent', '<=', $request->max_rent);
    }

    // Advanced Filters
    if ($request->filled('furnishing_type')) {
        $this->applyOptionFilter($query, 'furnishing_type', $request->furnishing_type);
    }

    if ($request->filled('tenant_type')) {
        $this->applyOptionFilter($query, 'tenant_type', $request->tenant_type);
    }

    if ($request->filled('property_type_id')) {
        $query->whereIn('property_type_id', (array) $request->property_type_id);
    }

    if ($request->filled('property_category_id')) {
        $query->whereIn('property_category_id', (array) $request->property_category_id);
    }

    if ($request->filled('min_area_sqft')) {
        $query->where('area_sqft', '>=', $request->min_area_sqft);
    }

    if ($request->filled('max_area_sqft')) {
        $query->where('area_sqft', '<=', $request->max_area_sqft);
    }

    if ($request->filled('area')) {
        $area = trim($request->area);
        if ($area !== '') {
            $query->where(function ($q) use ($area) {
                $q->where('address', 'like', '%' . $area . '%');
            });
        }
    }

    if ($request->filled('room_type')) {
        $this->applyOptionFilter($query, 'room_type', $request->room_type);
    }

    if ($request->filled('amenities')) {
        $amenities = $request->amenities;
        if (is_array($amenities)) {
            foreach ($amenities as $amenity) {
                $query->whereJsonContains('amenities', $amenity);
            }
        } else {
            $query->whereJsonContains('amenities', $amenities);
        }
    }

    if ($request->filled('available_now') && $request->available_now == '1') {
        $query->where('availability_from', '<=', now()->toDateString());
    } elseif ($request->filled('availability_from')) {
        $query->where('availability_from', '<=', $request->availability_from);
    }
    
    // Sorting logic
    $sortBy = $request->get('sort_by', 'newest');
    $query->orderBy('is_featured', 'desc');

    if ($lat && $lng && $locationVerified && !$cityContext['isFallback']) {
        $query->orderBy('distance', 'asc');
    }

    if ($sortBy === 'rent_asc') {
        $query->orderBy('rent', 'asc');
    } elseif ($sortBy === 'rent_desc') {
        $query->orderBy('rent', 'desc');
    } else {
        $query->orderBy('created_at', 'desc');
    }
    
    $userWishlistIds = Auth::check() ? Auth::user()->wishlists()->pluck('room_id')->toArray() : [];

    $rooms = $query->with(['user:id,name,avatar', 'propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption'])
                   ->paginate(20)
                   ->withQueryString();

    // Handle AJAX request for mobile infinite scroll
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

    // Log the search or visit if city is detected
    if ($request->filled('city') || $request->filled('min_rent') || $request->filled('max_rent') || isset($userCity)) {
        try {
            \App\Models\SearchLog::create([
                'city' => $request->city ?? $userCity ?? 'Unknown',
                'search_term' => $request->city ?? 'Auto-Detected', 
                'filters' => [
                    'min_rent' => $request->min_rent,
                    'max_rent' => $request->max_rent,
                    'property_type_id' => $request->property_type_id,
                    'property_category_id' => $request->property_category_id,
                    'min_area_sqft' => $request->min_area_sqft,
                    'max_area_sqft' => $request->max_area_sqft,
                    'is_auto_detected' => ! $request->filled('city') // Flag to know it was passive
                ],
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
            ]);
        } catch(\Exception $e) {
            // Fail silently
        }
    }
    
    $propertyTypeCounts = Room::publicVisible()
        ->select('property_type_id', DB::raw('count(*) as total'))
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('property_type_id')
        ->groupBy('property_type_id')
        ->pluck('total', 'property_type_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    $propertyTypes = PropertyType::cachedActive();

    $propertyCategoryCounts = Room::publicVisible()
        ->select('property_category_id', DB::raw('count(*) as total'))
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('property_category_id')
        ->groupBy('property_category_id')
        ->pluck('total', 'property_category_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    $propertyCategories = PropertyCategory::with('propertyType:id,name')
        ->publicSelectable()
        ->orderBy('property_type_id')
        ->orderBy('name')
        ->get(['id', 'property_type_id', 'name']);

    // Dynamic rent bounds from actual DB data
    $rentBounds = Room::publicVisible()
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->selectRaw('MIN(rent) as min_rent, MAX(rent) as max_rent')
        ->first();

    // Tenant type counts (girls/boys/family/any)
    $tenantTypeCounts = Room::publicVisible()
        ->select('tenant_option_id', DB::raw('count(*) as total'))
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('tenant_option_id')
        ->groupBy('tenant_option_id')
        ->pluck('total', 'tenant_option_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    // Furnishing counts from DB
    $furnishingCounts = Room::publicVisible()
        ->select('furnishing_option_id', DB::raw('count(*) as total'))
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('furnishing_option_id')
        ->groupBy('furnishing_option_id')
        ->pluck('total', 'furnishing_option_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    return view('rooms.index', compact(
        'rooms', 'popularCities', 'propertyTypes', 'propertyTypeCounts',
        'propertyCategories', 'propertyCategoryCounts', 'rentBounds',
        'tenantTypeCounts', 'furnishingCounts', 'cityContext', 'userWishlistIds'
    ));
    }
    

    public function create() {
        return view('owner.rooms.create');
    }

    public function store(Request $req) {
        $data = $req->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'property_category_id' => [
                'required',
                'integer',
                Rule::exists('property_categories', 'id')->where(fn ($query) => $query->where('status', true)->where('property_type_id', $req->property_type_id)),
            ],
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'area_sqft' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'amenities' => 'nullable|array',
            'amenities.*' => ['string', Rule::in(RoomOption::activeLabelsFor('amenity')->all())],
            'landmarks' => 'nullable|array',
            'landmarks.*' => 'string',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'photos' => 'required|array|min:1|max:5',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'listing_type' => 'required|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
        ]);

        $newPhotoPaths = [];
        $newVideoPath = null;
        DB::beginTransaction();
        try {
            $data['user_id'] = Auth::id();

            // Set broker_id and listed_by based on role
            if (Auth::user()->role === 'broker') {
                $data['broker_id'] = Auth::id();
                $data['listed_by'] = 'broker';
            } else {
                $data['listed_by'] = 'owner';
            }

            $data['status'] = 'pending';
            $data['listing_fee_paid'] = false;
            
            // Convert empty latitude/longitude strings to null
            if (isset($data['latitude']) && $data['latitude'] === '') {
                $data['latitude'] = null;
            }
            if (isset($data['longitude']) && $data['longitude'] === '') {
                $data['longitude'] = null;
            }
            
            // Handle multiple photos with Compression
            if ($req->hasFile('photos')) {
                $photos = [];
                foreach ($req->file('photos') as $photo) {
                    $path = \App\Services\ImageOptimizer::optimize($photo, 'room_photo');
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $data['photos'] = $photos;
                $data['photo'] = $photos[0]; // First photo as main photo
            }

            // Handle video upload
            if ($req->hasFile('video')) {
                $newVideoPath = $req->file('video')->store('rooms/videos', 'public');
                $data['video'] = $newVideoPath;
            }

            $data = $this->mapRoomOptionData($data);

            $room = Room::create($data);
            \Illuminate\Support\Facades\Cache::forget('public_cities_list');
            \Illuminate\Support\Facades\Cache::forget('popular_cities_web');

            try {
                \App\Models\AdminNotification::send(
                    'room_posted',
                    'New Room Listed',
                    '"' . \Illuminate\Support\Str::limit($room->title, 35) . '" in ' . ($room->city ?: 'Unknown') . ' by ' . (Auth::user()?->name ?? 'Owner'),
                    route('admin.rooms.show', $room->id),
                    'fa-building'
                );
            } catch (\Throwable $e) {
                report($e);
            }

            // Free-launch mode: skip subscriptions, wallet and Razorpay while
            // keeping the configured listing amount saved for future use.
            $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);

            // Broker-specific listing fee logic
            $isBroker = Auth::user()->role === 'broker';
            $brokerListingChargesEnabled = \App\Models\BrokerSetting::isEnabled('broker_listing_charges_enabled', false);
            $brokerSubscriptionEnabled = \App\Models\BrokerSetting::isEnabled('broker_subscription_enabled', false);

            if ($isBroker && !$brokerListingChargesEnabled) {
                $listingFeeEnabled = false;
            }

            if (!$listingFeeEnabled) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_listing' : 'listing',
                    'amount' => 0,
                    'gateway' => 'free',
                    'reference_id' => $room->id,
                    'status' => 'completed',
                ]);
                $room->update([
                    'listing_payment_id' => $payment->id,
                    'listing_fee_paid' => true,
                    'status' => 'active',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'free_listing' => true,
                    'message' => 'Room submitted successfully. It will be visible after admin approval.',
                ]);
            }

            // Check broker subscription or credits for room listing
            $useSubscription = false;
            $useCredits = false;

            if ($isBroker && $brokerSubscriptionEnabled) {
                $activeSubscription = \App\Models\BrokerSubscription::where('broker_id', Auth::id())
                    ->where('status', 'active')
                    ->where('expires_at', '>=', now())
                    ->lockForUpdate()
                    ->with('plan')
                    ->first();

                if ($activeSubscription && $activeSubscription->is_active) {
                    if ($activeSubscription->remaining_listings > 0) {
                        $activeSubscription->increment('listings_used');
                        $room->update([
                            'listing_fee_paid' => true,
                            'status' => 'active',
                            'listing_payment_id' => null,
                        ]);
                        $useSubscription = true;
                    }
                }
            }

            if (!$useSubscription && $isBroker) {
                $credits = \App\Models\BrokerListingCredit::where('broker_id', Auth::id())
                    ->where('credits_remaining', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->where('type', 'listing')
                    ->lockForUpdate()
                    ->first();

                if ($credits) {
                    $credits->decrement('credits_remaining');
                    $room->update([
                        'listing_fee_paid' => true,
                        'status' => 'active',
                        'listing_payment_id' => null,
                    ]);
                    $useCredits = true;
                }
            }

            if ($useSubscription || $useCredits) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'subscription_used' => $useSubscription,
                    'credits_used' => $useCredits,
                    'message' => 'Room listed successfully!',
                ]);
            }

            // Owner subscription check or broker payment required
            $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($query) => $query->where('type', 'owner')->where('is_active', true))
                ->lockForUpdate()
                ->with('plan')
                ->first();
            
            $useSubscription = false;
            if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'owner') {
                // Count rooms listed using subscription (listing_fee_paid = true and listing_payment_id is null)
                $usedListings = $activeSubscription->usages()->where('usage_type', 'listing')->count();
                
                $totalListings = $activeSubscription->plan->listing_limit ?? 0;
                
                // Check for Unlimited Plan (-1)
                $remainingListings = 0;
                if ($totalListings === -1) {
                    $remainingListings = 9999;
                } else {
                    $remainingListings = max(0, $totalListings - $usedListings);
                }
                
                if ($remainingListings > 0) {
                    // Use subscription - mark as paid without payment
                    $room->update([
                        'listing_fee_paid' => true,
                        'status' => 'active',
                        'listing_payment_id' => null // null means used subscription
                    ]);
                    SubscriptionUsage::firstOrCreate(
                        ['subscription_id' => $activeSubscription->id, 'usage_type' => 'listing', 'room_id' => $room->id],
                        ['user_id' => Auth::id(), 'used_at' => now()]
                    );
                    $useSubscription = true;
                }
            }

            if (!$useSubscription) {
                // Create payment record for listing fee
                $isBroker = Auth::user()->role === 'broker';
                if ($isBroker) {
                    $listingFee = \App\Models\BrokerSetting::get('broker_per_listing_charge', 199);
                } else {
                    $listingFee = Setting::get('listing_fee', 199);
                }

                // Check if user has enough balance in wallet
                $user = Auth::user();
                // Check if payment method is wallet
                if ($req->payment_method === 'wallet') {
                    if ($user->wallet_balance >= $listingFee) {
                        // Deduct from wallet balance
                        $user->decrement('wallet_balance', $listingFee);
                        
                        // Create payment record for wallet usage
                        $payment = Payment::create([
                            'user_id' => $user->id,
                            'type' => $isBroker ? 'broker_listing' : 'listing',
                            'amount' => $listingFee,
                            'gateway' => 'wallet',
                            'reference_id' => $room->id,
                            'status' => 'completed'
                        ]);
                        
                        // Store payment_id in room for tracking
                        $room->update([
                            'listing_payment_id' => $payment->id,
                            'listing_fee_paid' => true,
                            'status' => 'active'
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'room_id' => $room->id,
                            'wallet_used' => true,
                            'new_balance' => $user->wallet_balance,
                            'message' => 'Room listed successfully using wallet balance!'
                        ]);
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient wallet balance'
                        ], 400);
                    }
                }

                if ($listingFee <= 0) {
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'listing',
                        'amount' => 0,
                        'gateway' => 'free',
                        'reference_id' => $room->id,
                        'status' => 'completed'
                    ]);
                    
                    // Store payment_id in room for tracking
                    $room->update([
                        'listing_payment_id' => $payment->id,
                        'listing_fee_paid' => true,
                        'status' => 'active'
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'room_id' => $room->id,
                        'free_listing' => true,
                        'message' => 'Room listed successfully!'
                    ]);
                }

                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_listing' : 'listing',
                    'amount' => $listingFee,
                    'gateway' => 'razorpay',
                    'reference_id' => $room->id,
                    'status' => 'pending'
                ]);
                
                // Store payment_id in room for tracking
                $room->update(['listing_payment_id' => $payment->id]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'room_id' => $room->id,
                    'payment_id' => $payment->id,
                    'amount' => $listingFee,
                    'message' => 'Room created. Please pay listing fee to activate.'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'room_id' => $room->id,
                'subscription_used' => true,
                'message' => 'Room listed successfully using subscription!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }
            return response()->json([
                'success' => false,
                'message' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'The room could not be created. Please try again.'
            ], 500);
        }
    }

    public function show(Request $request, Room $room) {
        // Redirect to slug if accessed by ID
        // We check the URL segment to see if it's numeric (ID) instead of the slug
        if (is_numeric($request->segment(2)) && $room->slug) {
            return redirect()->route('rooms.show', $room, 301);
        }

        $isOwner = false;
        $isAdmin = false;
        if (Auth::check()) {
            $isOwner = Auth::id() === $room->user_id && in_array(Auth::user()->role, ['owner', 'broker']);
            $isAdmin = Auth::user()->role === 'admin';
        }

        if (!$isOwner && !$isAdmin) {
            if (! Room::publicVisible()->whereKey($room->getKey())->exists()) {
                abort(404);
            }
        }

        $isUnlocked = false;
        $subscriptionRemaining = 0;
        
        if (Auth::check()) {
            // Check if user is the owner of this room
            if (
                Auth::check() &&
                (
                    (Auth::id() === $room->user_id && in_array(Auth::user()->role, ['owner', 'broker']))
                    || Auth::user()->role === 'admin'
                )
            ) {
                $isOwner = true;
                $isUnlocked = true; // Owner/broker can see their own room contact
            } else {
                // Check subscription first - count based, not date based
                $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', today())
                    ->whereHas('plan', fn ($query) => $query->where('type', 'user')->where('is_active', true))
                    ->with('plan')
                    ->first();
                
                if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                    // Check subscription usage - count only subscription unlocks (payment_id is null)
                    $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                    
                    $totalContacts = $activeSubscription->plan->contacts_limit ?? 0;
                    
                    if ($totalContacts === -1) {
                        $subscriptionRemaining = 9999;
                    } else {
                        $subscriptionRemaining = max(0, $totalContacts - $usedContacts);
                    }
                    
                    // Check if this specific room was unlocked via subscription
                    $roomUnlockedViaSubscription = \App\Models\Enquiry::where('user_id', Auth::id())
                        ->where('room_id', $room->id)
                        ->where('unlocked', true)
                        ->whereNull('payment_id') // Subscription unlocks have null payment_id
                        ->exists();
                    
                    if ($roomUnlockedViaSubscription) {
                        $isUnlocked = true; // Already unlocked via subscription
                    } elseif ($subscriptionRemaining > 0) {
                        // Has remaining subscription contacts but this room not unlocked yet
                        $isUnlocked = false; // Will unlock via subscription when clicked
                    }
                }
                
                // If not unlocked via subscription, check single unlock (paid unlock)
                if (!$isUnlocked) {
                    // Check if unlock fee is 0
                    $unlockFee = Setting::get('unlock_fee', 49);
                    if ($unlockFee <= 0) {
                        $isUnlocked = true;
                    } else {
                        $enquiry = Enquiry::where('user_id', Auth::id())
                            ->where('room_id', $room->id)
                            ->where('unlocked', true)
                            ->whereNotNull('payment_id') // Single paid unlock
                            ->first();
                        $isUnlocked = $enquiry ? true : false;
                    }
                }
            }
        }
        
        // Auto-unlock for brokers as per transparent model
        if ($room->listing_type === 'broker') {
            $isUnlocked = true;
        }
        
        $room->load(['owner', 'propertyType', 'propertyCategory', 'roomTypeOption', 'furnishingOption', 'tenantOption']);

        $relatedRooms = Room::publicVisible()
            ->whereKeyNot($room->getKey())
            ->where('city', $room->city)
            ->when($room->property_category_id, fn ($query) => $query->where('property_category_id', $room->property_category_id))
            ->when(!$room->property_category_id && $room->property_type_id, fn ($query) => $query->where('property_type_id', $room->property_type_id))
            ->with(['owner', 'propertyType', 'propertyCategory'])
            ->orderByDesc('is_featured')
            ->latest()
            ->take(4)
            ->get();

        return view('rooms.show', compact('room', 'isUnlocked', 'isOwner', 'subscriptionRemaining', 'relatedRooms'));
    }

    public function edit(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }
        return view('owner.rooms.edit', compact('room'));
    }

    public function update(Request $req, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }

        $data = $req->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'property_category_id' => [
                'required',
                'integer',
                Rule::exists('property_categories', 'id')->where(fn ($query) => $query->where('status', true)->where('property_type_id', $req->property_type_id)),
            ],
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'area_sqft' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'photos' => 'nullable|array|max:5',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'amenities' => 'nullable|array',
            'amenities.*' => ['string', Rule::in(RoomOption::activeLabelsFor('amenity')->all())],
            'landmarks' => 'nullable|array',
            'listing_type' => 'required|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
        ]);

        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        DB::beginTransaction();
        try {
            // Convert empty latitude/longitude strings to null
            if (isset($data['latitude']) && $data['latitude'] === '') {
                $data['latitude'] = null;
            }
            if (isset($data['longitude']) && $data['longitude'] === '') {
                $data['longitude'] = null;
            }
            
            // Handle multiple photos with Compression
            if ($req->hasFile('photos')) {
                $photos = [];
                foreach ($req->file('photos') as $photo) {
                    $path = \App\Services\ImageOptimizer::optimize($photo, 'room_photo');
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $oldPhotoPaths = collect($room->photos ?: [])
                    ->filter(fn ($path) => is_string($path) && !preg_match('/^https?:\/\//i', $path))
                    ->values()->all();
                $data['photos'] = $photos;
                $data['photo'] = $photos[0]; // First photo as main photo
            }

            // Handle video upload
            if ($req->hasFile('video')) {
                // Delete old video
                if ($room->video) {
                    Storage::disk('public')->delete($room->video);
                }
                $data['video'] = $req->file('video')->store('rooms/videos', 'public');
            }

            $data = $this->mapRoomOptionData($data);

            // Any owner edit requires moderation again before public display.
            $data['listing_status'] = 'pending';

            $room->update($data);
            \Illuminate\Support\Facades\Cache::forget('public_cities_list');
            \Illuminate\Support\Facades\Cache::forget('popular_cities_web');

            DB::commit();

            foreach ($oldPhotoPaths as $oldPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            report($e);
            return response()->json([
                'success' => false,
                'message' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'The room could not be updated. Please try again.'
            ], 500);
        }
    }

    public function destroy(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Delete photos
            if ($room->photos) {
                foreach ($room->photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            // Delete video
            if ($room->video) {
                Storage::disk('public')->delete($room->video);
            }

            $room->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete the room. Please try again.'
            ], 500);
        }
    }

    public function makeFeatured(Request $request, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return back()->with('error', 'Unauthorized');
        }

        if ($room->is_featured) {
            return back()->with('info', 'Room is already featured');
        }

        $isBroker = Auth::user()->role === 'broker';

        // Check broker featured toggle
        if ($isBroker && !\App\Models\BrokerSetting::isEnabled('broker_featured_enabled', true)) {
            return back()->with('error', 'Featured listing is currently disabled for brokers.');
        }

        DB::beginTransaction();
        try {
            if ($isBroker) {
                $featuredFee = \App\Models\BrokerSetting::get('broker_featured_charge', 99);
            } else {
                $featuredFee = Setting::get('featured_fee', 99);
            }
            $user = Auth::user();

            // Wallet Payment
            if ($request->payment_method === 'wallet' && $featuredFee > 0) {
                if ($user->wallet_balance >= $featuredFee) {
                    $user->decrement('wallet_balance', $featuredFee);
                    
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => $isBroker ? 'broker_featured' : 'featured',
                        'amount' => $featuredFee,
                        'gateway' => 'wallet',
                        'reference_id' => $room->id,
                        'status' => 'completed'
                    ]);

                    $room->update(['is_featured' => true]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'wallet_used' => true,
                        'new_balance' => $user->wallet_balance,
                        'message' => 'Room featured successfully using wallet balance!'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ], 400);
                }
            }
            
            if ($featuredFee <= 0) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => $isBroker ? 'broker_featured' : 'featured',
                    'amount' => 0,
                    'gateway' => 'free',
                    'reference_id' => $room->id,
                    'status' => 'completed'
                ]);

                $room->update(['is_featured' => true]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'free_feature' => true,
                    'message' => 'Room featured successfully!'
                ]);
            }

            $payment = Payment::create([
                'user_id' => Auth::id(),
                'type' => $isBroker ? 'broker_featured' : 'featured',
                'amount' => $featuredFee,
                'gateway' => 'razorpay',
                'reference_id' => $room->id,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'amount' => $featuredFee,
                'message' => 'Please complete payment to feature your room'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return back()->withInput()->with('error', 'Unable to feature the room. Please try again.');
        }
    }

    public function markBooked(Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // A booked room is removed from public inventory. Its previous listing
        // entitlement is released; publishing it again must pass the current
        // subscription/payment checks in markAvailable().
        $room->update([
            'status' => 'booked',
            'listing_fee_paid' => false,
            'listing_payment_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room marked as rented'
        ]);
    }

    public function markAvailable(Request $request, Room $room) {
        if ($room->user_id !== Auth::id() || !in_array(Auth::user()->role, ['owner', 'broker'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // If room is booked, charge listing fee to make it available again
        if ($room->status === 'booked') {
            DB::beginTransaction();
            try {
                $isBroker = Auth::user()->role === 'broker';
                $brokerListingChargesEnabled = \App\Models\BrokerSetting::isEnabled('broker_listing_charges_enabled', false);
                $brokerSubscriptionEnabled = \App\Models\BrokerSetting::isEnabled('broker_subscription_enabled', false);

                // For brokers, check broker-specific settings
                if ($isBroker && !$brokerListingChargesEnabled) {
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'broker_listing',
                        'amount' => 0,
                        'gateway' => 'free',
                        'reference_id' => $room->id,
                        'status' => 'completed',
                    ]);
                    $room->update([
                        'status' => 'active',
                        'listing_fee_paid' => true,
                        'listing_payment_id' => $payment->id,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'free_listing' => true,
                        'message' => 'Room marked as available successfully.',
                    ]);
                }

                $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
                if (!$isBroker && !$listingFeeEnabled) {
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'listing',
                        'amount' => 0,
                        'gateway' => 'free',
                        'reference_id' => $room->id,
                        'status' => 'completed',
                    ]);
                    $room->update([
                        'status' => 'active',
                        'listing_fee_paid' => true,
                        'listing_payment_id' => $payment->id,
                    ]);
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'free_listing' => true,
                        'message' => 'Room marked as available successfully.',
                    ]);
                }

                // Check owner subscription for room listing - count based, not date based
                $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', today())
                    ->whereHas('plan', fn ($query) => $query->where('type', 'owner')->where('is_active', true))
                    ->with('plan')
                    ->first();
                
                $useSubscription = false;
                if (!$isBroker && $activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'owner') {
                    // Count rooms listed using subscription (listing_payment_id is null)
                    $usedListings = Room::where('user_id', Auth::id())
                        ->where('listing_fee_paid', true)
                        ->whereNull('listing_payment_id') // Subscription listings have null listing_payment_id
                        ->count();
                    
                    $totalListings = $activeSubscription->plan->listing_limit ?? 0;
                    
                    // Check for Unlimited Plan (-1)
                    $remainingListings = 0;
                    if ($totalListings === -1) {
                        $remainingListings = 9999;
                    } else {
                        $remainingListings = max(0, $totalListings - $usedListings);
                    }
                    
                    if ($remainingListings > 0) {
                        // Use subscription - mark as paid without payment
                        $room->update([
                            'listing_fee_paid' => true,
                            'status' => 'active',
                            'listing_payment_id' => null // null means used subscription
                        ]);
                        $useSubscription = true;
                    }
                }

                // Check broker subscription for room listing
                if ($isBroker && $brokerSubscriptionEnabled) {
                    $activeBrokerSubscription = \App\Models\BrokerSubscription::where('broker_id', Auth::id())
                        ->where('status', 'active')
                        ->where('expires_at', '>=', now())
                        ->with('plan')
                        ->first();

                    if ($activeBrokerSubscription && $activeBrokerSubscription->is_active) {
                        if ($activeBrokerSubscription->remaining_listings > 0) {
                            $activeBrokerSubscription->increment('listings_used');
                            $room->update([
                                'listing_fee_paid' => true,
                                'status' => 'active',
                                'listing_payment_id' => null,
                            ]);
                            $useSubscription = true;
                        }
                    }
                }

                if (!$useSubscription) {
                    if ($isBroker) {
                        $listingFee = \App\Models\BrokerSetting::get('broker_per_listing_charge', 199);
                    } else {
                        $listingFee = Setting::get('listing_fee', 199);
                    }
                    
                    // Check if payment method is wallet
                    if ($request->payment_method === 'wallet') {
                        // Check if user has enough balance in wallet
                        $user = Auth::user();
                        if ($user->wallet_balance >= $listingFee) {
                            // Deduct from wallet
                            $user->decrement('wallet_balance', $listingFee);
                            
                            // Create payment record for wallet usage
                            $payment = Payment::create([
                                'user_id' => $user->id,
                                'type' => $isBroker ? 'broker_listing' : 'listing',
                                'amount' => $listingFee,
                                'gateway' => 'wallet',
                                'reference_id' => $room->id,
                                'status' => 'completed'
                            ]);
                            
                            // Store payment_id in room for tracking
                            $room->update([
                                'listing_payment_id' => $payment->id,
                                'listing_fee_paid' => true,
                                'status' => 'active'
                            ]);

                            DB::commit();

                            return response()->json([
                                'success' => true,
                                'wallet_used' => true,
                                'new_balance' => $user->wallet_balance,
                                'message' => 'Room made available successfully using wallet balance!'
                            ]);
                        } else {
                            DB::rollBack();
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient wallet balance'
                            ], 400);
                        }
                    }

                    if ($listingFee <= 0) {
                        $payment = Payment::create([
                            'user_id' => Auth::id(),
                            'type' => $isBroker ? 'broker_listing' : 'listing',
                            'amount' => 0,
                            'gateway' => 'free',
                            'reference_id' => $room->id,
                            'status' => 'completed'
                        ]);
                        
                        // Store payment_id in room for tracking
                        $room->update([
                            'listing_payment_id' => $payment->id,
                            'listing_fee_paid' => true,
                            'status' => 'active'
                        ]);
    
                        DB::commit();
    
                        return response()->json([
                            'success' => true,
                            'free_listing' => true,
                            'message' => 'Room made available successfully!'
                        ]);
                    }

                    // Create payment record
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => $isBroker ? 'broker_listing' : 'listing',
                        'amount' => $listingFee,
                        'gateway' => 'razorpay',
                        'reference_id' => $room->id,
                        'status' => 'pending'
                    ]);
                    
                    // Store payment_id in room for tracking
                    $room->update(['listing_payment_id' => $payment->id]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'payment_id' => $payment->id,
                        'amount' => $listingFee,
                        'message' => 'Please complete payment to make room available again'
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'subscription_used' => true,
                    'message' => 'Room made available using subscription!'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to make the room available. Please try again.'
                ], 500);
            }
        } else {
            // If not booked, just update status
            $room->update(['status' => 'active']);
            return response()->json([
                'success' => true,
                'message' => 'Room marked as available'
            ]);
        }
    }
    
    public function setCity(Request $request) {
        $city = $request->get('city');
        if ($city) {
            session(['user_city' => $city]);
            session()->forget('no_auto');
            if ($request->has('lat') && $request->has('lng')) {
                session(['user_lat' => $request->lat, 'user_lng' => $request->lng]);
            }
            if ($request->has('verified')) {
                session(['location_verified' => true]);
            }
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 400);
    }
}
