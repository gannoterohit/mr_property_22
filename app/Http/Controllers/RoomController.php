<?php

namespace App\Http\Controllers;
use App\Models\Room;
use App\Models\Payment;
use App\Models\Enquiry;
use App\Models\RoomOption;
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
            $query->where('status', 'active')
                  ->where('listing_fee_paid', true)
                  ->where('listing_status', 'approved');
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
    
    $rooms = $query->with(['user:id,name,avatar'])
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
                    'is_auto_detected' => ! $request->filled('city') // Flag to know it was passive
                ],
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
            ]);
        } catch(\Exception $e) {
            // Fail silently
        }
    }
    
    // Fetch room type counts dynamically from DB
    $roomTypeCounts = Room::select('room_type_option_id', DB::raw('count(*) as total'))
        ->where('status', 'active')
        ->where('listing_status', 'approved')
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('room_type_option_id')
        ->groupBy('room_type_option_id')
        ->pluck('total', 'room_type_option_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    // Property types are controlled exclusively from Admin > Room Options.
    // Do not use model fallback values on the public listing page.
    $roomTypeOptions = RoomOption::query()
        ->active()
        ->where('group', 'room_type')
        ->orderBy('sort_order')
        ->orderBy('label')
        ->get(['id', 'key', 'label']);

    // Dynamic rent bounds from actual DB data
    $rentBounds = Room::where('status', 'active')
        ->where('listing_status', 'approved')
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->selectRaw('MIN(rent) as min_rent, MAX(rent) as max_rent')
        ->first();

    // Tenant type counts (girls/boys/family/any)
    $tenantTypeCounts = Room::select('tenant_option_id', DB::raw('count(*) as total'))
        ->where('status', 'active')
        ->where('listing_status', 'approved')
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('tenant_option_id')
        ->groupBy('tenant_option_id')
        ->pluck('total', 'tenant_option_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    // Furnishing counts from DB
    $furnishingCounts = Room::select('furnishing_option_id', DB::raw('count(*) as total'))
        ->where('status', 'active')
        ->where('listing_status', 'approved')
        ->when($cityContext['activeCityName'], fn ($q) => $q->where('city', 'like', '%' . $cityContext['activeCityName'] . '%'))
        ->whereNotNull('furnishing_option_id')
        ->groupBy('furnishing_option_id')
        ->pluck('total', 'furnishing_option_id')
        ->map(fn ($total) => (int) $total)
        ->toArray();

    return view('rooms.index', compact(
        'rooms', 'popularCities', 'roomTypeCounts', 'roomTypeOptions',
        'rentBounds', 'tenantTypeCounts', 'furnishingCounts', 'cityContext'
    ));
    }
    

    public function create() {
        return view('owner.rooms.create');
    }

    public function store(Request $req) {
        $data = $req->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'room_type' => ['required', Rule::in(RoomOption::validIdsFor('room_type'))],
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
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
                    $filename = uniqid('room_') . '.jpg';
                    $path = 'rooms/' . $filename;
                    $fullPath = storage_path('app/public/' . $path);
                    
                    // Create directory if not exists
                    if (!file_exists(storage_path('app/public/rooms'))) {
                        mkdir(storage_path('app/public/rooms'), 0755, true);
                    }

                    // Compress and save
                    if (! \App\Helpers\ImageHelper::compressImage($photo->getRealPath(), $fullPath, 70)) {
                        throw new \RuntimeException('One of the selected images could not be processed. Please use JPG, PNG or WebP files.');
                    }
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

            // Free-launch mode: skip subscriptions, wallet and Razorpay while
            // keeping the configured listing amount saved for future use.
            $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
            if (!$listingFeeEnabled) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => 'listing',
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

            // Check owner subscription for room listing - count based, not date based
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
                $listingFee = Setting::get('listing_fee', 199);

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
                            'type' => 'listing',
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
                    'type' => 'listing',
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
        $isUnlocked = false;
        $isOwner = false;
        $subscriptionRemaining = 0;
        
        if (Auth::check()) {
            // Check if user is the owner of this room
            if (
                Auth::check() &&
                (
                    (Auth::id() === $room->user_id && Auth::user()->role === 'owner')
                    || Auth::user()->role === 'admin'
                )
            ) {
                $isOwner = true;
                $isUnlocked = true; // Owner can see their own room contact
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
        
        $room->load('owner');

        $relatedRooms = Room::query()
            ->whereKeyNot($room->getKey())
            ->where('city', $room->city)
            ->where('status', 'active')
            ->where('listing_status', 'approved')
            ->with('owner')
            ->orderByDesc('is_featured')
            ->latest()
            ->take(4)
            ->get();

        return view('rooms.show', compact('room', 'isUnlocked', 'isOwner', 'subscriptionRemaining', 'relatedRooms'));
    }

    public function edit(Room $room) {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('owner.rooms.edit', compact('room'));
    }

    public function update(Request $req, Room $room) {
        if ($room->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $data = $req->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
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
            'room_type' => ['required', Rule::in(RoomOption::validIdsFor('room_type'))],
            'amenities' => 'nullable|array',
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
                    $filename = uniqid('room_') . '.jpg';
                    $path = 'rooms/' . $filename;
                    $fullPath = storage_path('app/public/' . $path);
                    
                    // Create directory if not exists
                    if (!file_exists(storage_path('app/public/rooms'))) {
                        mkdir(storage_path('app/public/rooms'), 0755, true);
                    }

                    // Compress and save
                    if (!\App\Helpers\ImageHelper::compressImage($photo->getRealPath(), $fullPath, 70)) {
                        throw new \RuntimeException('One of the selected images could not be processed. Please use JPG, PNG or WebP files.');
                    }
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
        if ($room->user_id !== Auth::id()) {
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
        if ($room->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized');
        }

        if ($room->is_featured) {
            return back()->with('info', 'Room is already featured');
        }

        DB::beginTransaction();
        try {
            $featuredFee = Setting::get('featured_fee', 99);
            $user = Auth::user();

            // Wallet Payment
            if ($request->payment_method === 'wallet' && $featuredFee > 0) {
                if ($user->wallet_balance >= $featuredFee) {
                    $user->decrement('wallet_balance', $featuredFee);
                    
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => 'featured',
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
                    'type' => 'featured',
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
                'type' => 'featured',
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
        if ($room->user_id !== Auth::id()) {
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
        if ($room->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // If room is booked, charge listing fee to make it available again
        if ($room->status === 'booked') {
            DB::beginTransaction();
            try {
                $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
                if (!$listingFeeEnabled) {
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
                if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'owner') {
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

                if (!$useSubscription) {
                    $listingFee = Setting::get('listing_fee', 199);
                    
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
                                'type' => 'listing',
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
                            'free_listing' => true,
                            'message' => 'Room made available successfully!'
                        ]);
                    }

                    // Create payment record
                    $payment = Payment::create([
                        'user_id' => Auth::id(),
                        'type' => 'listing',
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
