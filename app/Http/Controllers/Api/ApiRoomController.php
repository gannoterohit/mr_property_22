<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RoomResource;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomOption;
use App\Models\Setting;
use App\Models\SubscriptionUsage;
use App\Services\CityOperations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiRoomController extends BaseApiController
{
    /**
     * List rooms with filters (Public)
     */
    public function index(Request $request)
    {
        $query = Room::query()
            ->with('owner')
            ->where('status', 'active')
            ->where('listing_fee_paid', true)
            ->where('listing_status', 'approved');

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        if ($request->filled('min_rent')) {
            $query->where('rent', '>=', $request->min_rent);
        }

        if ($request->filled('max_rent')) {
            $query->where('rent', '<=', $request->max_rent);
        }

        $roomTypeFilter = $request->input('room_type_option_id', $request->input('room_type'));
        $furnishingFilter = $request->input('furnishing_option_id', $request->input('furnishing_type'));
        $tenantFilter = $request->input('tenant_option_id', $request->input('tenant_type'));

        if ($roomTypeFilter !== null && $roomTypeFilter !== '') {
            $this->applyOptionFilter($query, 'room_type', $roomTypeFilter);
        }

        if ($furnishingFilter !== null && $furnishingFilter !== '') {
            $this->applyOptionFilter($query, 'furnishing_type', $furnishingFilter);
        }

        if ($tenantFilter !== null && $tenantFilter !== '') {
            $this->applyOptionFilter($query, 'tenant_type', $tenantFilter);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%")
                    ->orWhere('address', 'like', "%$search%");
            });
        }

        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = $request->lat;
            $lng = $request->lng;
            $query->selectRaw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$lat, $lng, $lat])
                ->orderBy('distance', 'asc');
        } else {
            $query->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc');
        }

        $rooms = $query->paginate(max(1, min(50, $request->integer('limit', 10))));

        // Log the search
        if ($request->filled('city') || $request->filled('min_rent') || $request->filled('max_rent') || $request->filled('search')) {
            try {
                \App\Models\SearchLog::create([
                    'city' => $request->city ?? 'Unknown',
                    'search_term' => $request->search ?? $request->city ?? 'Filter Applied',
                    'filters' => [
                        'min_rent' => $request->min_rent,
                        'max_rent' => $request->max_rent,
                        'room_type_option_id' => $roomTypeFilter,
                        'furnishing_option_id' => $furnishingFilter,
                        'tenant_option_id' => $tenantFilter,
                        'is_api' => true,
                    ],
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                ]);
            } catch (\Exception $e) {
                // Fail silently
            }
        }

        return $this->sendPaginated(
            RoomResource::collection($rooms),
            'Rooms fetched successfully.'
        );
    }

    /**
     * Get single room details
     */
    public function show(Request $request, $id)
    {
        $room = Room::with('owner')
            ->where('status', 'active')
            ->where('listing_status', 'approved')
            ->where('listing_fee_paid', true)
            ->where(fn ($query) => $query->where('id', $id)->orWhere('slug', $id))
            ->first();

        if (! $room) {
            return $this->sendError('Room not found');
        }

        $viewer = $request->user('sanctum');
        $isUnlocked = false;
        if ($viewer) {
            $isUnlocked = ($viewer->id === $room->user_id) ||
                          Enquiry::where('user_id', $viewer->id)->where('room_id', $room->id)->where('unlocked', true)->exists();
        }

        if ($room->listing_type === 'broker') {
            $isUnlocked = true;
        }

        $resource = new RoomResource($room);

        return $this->sendSuccess([
            'room' => $resource,
            'is_unlocked' => $isUnlocked,
            'owner_contact' => $isUnlocked ? [
                'phone' => $room->owner?->phone,
                'email' => $room->owner?->email,
            ] : null,
        ]);
    }

    /**
     * Get similar rooms
     */
    public function similar(Request $request, $id)
    {
        $room = Room::find($id);
        if (! $room) {
            return $this->sendError('Room not found');
        }

        $rooms = Room::where('id', '!=', $id)
            ->where('city', $room->city)
            ->where('status', 'active')
            ->where('listing_fee_paid', true)
            ->where('listing_status', 'approved')
            ->limit(4)
            ->get();

        return $this->sendSuccess(RoomResource::collection($rooms));
    }

    /**
     * Detect city from coordinates
     */
    public function detectCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Coordinates required', $validator->errors(), 422);
        }

        // Logic to find closest city from our database
        $closestRoom = Room::selectRaw('city, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance', [$request->lat, $request->lng, $request->lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('distance', 'asc')
            ->first();

        if ($closestRoom && $closestRoom->distance < 50) { // Within 50km
            return $this->sendSuccess(['city' => $closestRoom->city]);
        }

        return $this->sendError('No nearby city found with listings', [], 404);
    }

    /**
     * List rooms owned by the authenticated user
     */
    public function myRooms(Request $request)
    {
        $rooms = Room::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('limit', 10));

        return RoomResource::collection($rooms)->additional(['status' => 'success']);
    }

    /**
     * Store a new room (Owner)
     */
    public function store(Request $request)
    {
        $this->normalizeRoomOptionInput($request);

        $validator = Validator::make($request->all(), [
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
            'furnishing_type' => ['required', 'in:'.implode(',', RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', 'in:'.implode(',', RoomOption::validIdsFor('tenant_type'))],
            'room_type' => ['required', 'in:'.implode(',', RoomOption::validIdsFor('room_type'))],
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'landmarks' => 'nullable|array',
            'landmarks.*' => 'string',
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'listing_type' => 'required|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:wallet,online',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        $newPhotoPaths = [];
        $newVideoPath = null;
        DB::beginTransaction();
        try {
            $data = $validator->validated();
            unset($data['photos'], $data['video'], $data['payment_method']);
            $data['user_id'] = Auth::id();
            $data['status'] = 'active';
            $data['listing_status'] = 'pending';
            $data['listing_fee_paid'] = false;

            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $filename = uniqid('room_').'.jpg';
                    $path = 'rooms/'.$filename;
                    $fullPath = storage_path('app/public/'.$path);

                    if (! file_exists(storage_path('app/public/rooms'))) {
                        mkdir(storage_path('app/public/rooms'), 0755, true);
                    }

                    if (! \App\Helpers\ImageHelper::compressImage($photo->getRealPath(), $fullPath, 70)) {
                        throw new \RuntimeException('One of the selected images could not be processed. Please use JPG, PNG or WebP files.');
                    }
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $data['photos'] = $photos;
                $data['photo'] = $photos[0];
            }

            if ($request->hasFile('video')) {
                $newVideoPath = $request->file('video')->store('rooms/videos', 'public');
                $data['video'] = $newVideoPath;
            }

            $data = $this->mapRoomOptionData($data);

            $room = Room::create($data);
            \Illuminate\Support\Facades\Cache::forget('public_cities_list');

            // Notify admin about new room listing (API side)
            try {
                \App\Models\AdminNotification::send(
                    'room_posted',
                    'New Room Listed (App)',
                    '"' . \Illuminate\Support\Str::limit($room->title, 35) . '" in ' . ($room->city ?: 'Unknown') . ' by ' . (Auth::user()?->name ?? 'Owner'),
                    route('admin.rooms.show', $room->id),
                    'fa-building'
                );
            } catch (\Throwable $notifEx) {
                report($notifEx);
            }

            $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
            if (! $listingFeeEnabled) {
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

                return $this->sendSuccess(new RoomResource($room->fresh()), 'Room submitted successfully. It will be visible after admin approval.');
            }

            // Check Owner Subscription
            $activeSub = \App\Models\Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($q) => $q->where('type', 'owner')->where('is_active', true))
                ->lockForUpdate()
                ->with('plan')
                ->first();

            if ($activeSub) {
                $used = $activeSub->usages()->where('usage_type', 'listing')->count();
                $limit = $activeSub->plan->listing_limit;
                if ($limit === -1 || $used < $limit) {
                    $room->update(['listing_fee_paid' => true, 'status' => 'active']);
                    SubscriptionUsage::firstOrCreate(
                        ['subscription_id' => $activeSub->id, 'usage_type' => 'listing', 'room_id' => $room->id],
                        ['user_id' => Auth::id(), 'used_at' => now()]
                    );
                    DB::commit();

                    return $this->sendSuccess(new RoomResource($room), 'Room listed successfully using subscription!');
                }
            }

            $listingFee = (float) Setting::get('listing_fee', 199);

            // Handle Wallet Payment
            if ($request->payment_method === 'wallet') {
                $user = Auth::user();
                if ($user->wallet_balance >= $listingFee) {
                    $user->decrement('wallet_balance', $listingFee);
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => 'listing',
                        'amount' => $listingFee,
                        'gateway' => 'wallet',
                        'reference_id' => $room->id,
                        'status' => 'completed',
                    ]);
                    $room->update([
                        'listing_payment_id' => $payment->id,
                        'listing_fee_paid' => true,
                        'status' => 'active',
                    ]);
                    DB::commit();

                    return $this->sendSuccess(new RoomResource($room), 'Room listed successfully using wallet balance!');
                } else {
                    DB::rollBack();

                    return $this->sendError('Insufficient wallet balance', [], 400);
                }
            }

            $payment = Payment::create([
                'user_id' => Auth::id(), 'type' => 'listing', 'amount' => $listingFee,
                'gateway' => 'razorpay', 'reference_id' => $room->id, 'status' => 'pending',
            ]);
            $room->update(['listing_payment_id' => $payment->id]);
            DB::commit();

            return $this->sendSuccess([
                'room' => new RoomResource($room),
                'payment_record_id' => $payment->id,
                'amount' => $listingFee,
                'type' => 'listing',
            ], 'Room created. Please complete payment to activate.');

        } catch (\Exception $e) {
            DB::rollBack();

            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }

            return $this->sendError($this->safeErrorMessage($e, 'Unable to save room. Please try again.'), [], 500);
        }
    }

    /**
     * Update room details
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (! $room || $room->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized or room not found', [], 403);
        }

        $this->normalizeRoomOptionInput($request);

        $validator = Validator::make($request->all(), [
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
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'listing_type' => 'required|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        $data = $validator->validated();
        unset($data['photos'], $data['video']);

        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        $newVideoPath = null;
        $oldVideoPath = null;

        DB::beginTransaction();
        try {
            if ($request->hasFile('photos')) {
                $photos = [];
                foreach ($request->file('photos') as $photo) {
                    $filename = uniqid('room_').'.jpg';
                    $path = 'rooms/'.$filename;
                    $fullPath = storage_path('app/public/'.$path);

                    if (! file_exists(storage_path('app/public/rooms'))) {
                        mkdir(storage_path('app/public/rooms'), 0755, true);
                    }

                    if (! \App\Helpers\ImageHelper::compressImage($photo->getRealPath(), $fullPath, 70)) {
                        throw new \RuntimeException('One of the selected images could not be processed. Please use JPG, PNG or WebP files.');
                    }
                    $photos[] = $path;
                    $newPhotoPaths[] = $path;
                }
                $oldPhotoPaths = collect($room->photos ?: [])->filter(fn ($path) => is_string($path) && ! preg_match('/^https?:\/\//i', $path))->values()->all();
                $data['photos'] = $photos;
                $data['photo'] = $photos[0];
            }

            if ($request->hasFile('video')) {
                $oldVideoPath = $room->video;
                $newVideoPath = $request->file('video')->store('rooms/videos', 'public');
                $data['video'] = $newVideoPath;
            }

            $data = $this->mapRoomOptionData($data);
            $data['listing_status'] = 'pending';
            $room->update($data);
            DB::commit();

            foreach ($oldPhotoPaths as $oldPhotoPath) {
                Storage::disk('public')->delete($oldPhotoPath);
            }
            if ($oldVideoPath) {
                Storage::disk('public')->delete($oldVideoPath);
            }

            \Illuminate\Support\Facades\Cache::forget('public_cities_list');

            return $this->sendSuccess(new RoomResource($room->fresh()), 'Room updated successfully and submitted for approval');
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }

            return $this->sendError($this->safeErrorMessage($e, 'Unable to update room. Please try again.'), [], 500);
        }
    }

    public function ownerShow(Request $request, $id)
    {
        $room = Room::where('user_id', $request->user()->id)
            ->with('owner')
            ->findOrFail($id);

        return $this->sendSuccess(new RoomResource($room), 'Owner room fetched successfully');
    }

    /**
     * Accept canonical Room model foreign-key names while retaining the
     * original mobile API aliases. Canonical *_option_id values take priority.
     */
    private function normalizeRoomOptionInput(Request $request): void
    {
        $aliases = [
            'room_type_option_id' => 'room_type',
            'furnishing_option_id' => 'furnishing_type',
            'tenant_option_id' => 'tenant_type',
        ];

        foreach ($aliases as $canonical => $alias) {
            if ($request->has($canonical)) {
                $request->merge([$alias => $request->input($canonical)]);
            }
        }
    }

    /**
     * Delete room
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (! $room || $room->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized or room not found', [], 403);
        }

        $room->delete();

        return $this->sendSuccess([], 'Room deleted successfully');
    }

    /**
     * Toggle room status (Rented/Available)
     */
    public function toggleStatus(Request $request, $id)
    {
        $room = Room::find($id);

        if (! $room || $room->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized or room not found', [], 403);
        }

        if ($room->status === 'active') {
            $room->update([
                'status' => 'booked',
                'listing_fee_paid' => false,
                'listing_payment_id' => null,
            ]);

            return $this->sendSuccess(['new_status' => 'booked'], 'Room marked as rented');
        } else {
            // Making available again! In web, this charges listing fee again.

            DB::beginTransaction();
            try {
                $listingFeeEnabled = filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
                if (! $listingFeeEnabled) {
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

                    return $this->sendSuccess(['new_status' => 'active', 'free_listing' => true], 'Room marked as available');
                }

                // 1. Check Owner Subscription
                $activeSub = \App\Models\Subscription::where('user_id', Auth::id())
                    ->where('status', 'active')
                    ->whereDate('end_date', '>=', today())
                    ->whereHas('plan', fn ($q) => $q->where('type', 'owner')->where('is_active', true))
                    ->with('plan')
                    ->first();

                if ($activeSub) {
                    $used = Room::where('user_id', Auth::id())->where('listing_fee_paid', true)->whereNull('listing_payment_id')->count();
                    if ($activeSub->plan->listing_limit === -1 || $used < $activeSub->plan->listing_limit) {
                        $room->update([
                            'status' => 'active',
                            'listing_fee_paid' => true,
                            'listing_payment_id' => null,
                        ]);
                        DB::commit();

                        return $this->sendSuccess(['new_status' => 'active'], 'Room marked as available using subscription');
                    }
                }

                $listingFee = (float) Setting::get('listing_fee', 199);

                // 2. Handle Wallet
                if ($request->payment_method === 'wallet') {
                    $user = Auth::user();
                    if ($user->wallet_balance >= $listingFee) {
                        $user->decrement('wallet_balance', $listingFee);
                        $payment = Payment::create([
                            'user_id' => $user->id,
                            'type' => 'listing',
                            'amount' => $listingFee,
                            'gateway' => 'wallet',
                            'reference_id' => $room->id,
                            'status' => 'completed',
                        ]);
                        $room->update([
                            'status' => 'active',
                            'listing_fee_paid' => true,
                            'listing_payment_id' => $payment->id,
                        ]);
                        DB::commit();

                        return $this->sendSuccess(['new_status' => 'active', 'new_balance' => $user->wallet_balance], 'Room marked as available using wallet balance');
                    } else {
                        DB::rollBack();

                        return $this->sendError('Insufficient wallet balance', [], 400);
                    }
                }

                // 3. Create the same pending listing payment used by the web flow.
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => 'listing',
                    'amount' => $listingFee,
                    'gateway' => 'razorpay',
                    'reference_id' => $room->id,
                    'status' => 'pending',
                ]);
                $room->update(['listing_payment_id' => $payment->id]);
                DB::commit();

                return $this->sendSuccess([
                    'payment_id' => $payment->id,
                    'amount' => $listingFee,
                    'type' => 'listing',
                    'action' => 'mark_available',
                ], 'Payment required to make room available again');

            } catch (\Exception $e) {
                DB::rollBack();

                return $this->sendError($this->safeErrorMessage($e, 'Unable to complete this action. Please try again.'), [], 500);
            }
        }
    }

    /**
     * Make room featured
     */
    public function makeFeatured(Request $request, $id)
    {
        $room = Room::find($id);

        if (! $room || $room->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized or room not found', [], 403);
        }

        if ($room->is_featured) {
            return $this->sendError('Room is already featured', [], 400);
        }

        $featuredFee = Setting::get('featured_fee', 99);
        $user = Auth::user();

        if ($request->payment_method === 'wallet') {
            if ($user->wallet_balance >= $featuredFee) {
                $user->decrement('wallet_balance', $featuredFee);

                Payment::create([
                    'user_id' => $user->id,
                    'type' => 'featured',
                    'amount' => $featuredFee,
                    'gateway' => 'wallet',
                    'reference_id' => $room->id,
                    'status' => 'completed',
                ]);

                $room->update(['is_featured' => true]);

                return $this->sendSuccess([
                    'new_balance' => $user->wallet_balance,
                ], 'Room featured successfully using wallet balance');
            } else {
                return $this->sendError('Insufficient wallet balance', [], 400);
            }
        }

        // Return payment details for Flutter to handle Razorpay
        return $this->sendSuccess([
            'amount' => (float) $featuredFee,
            'reference_id' => $room->id,
            'type' => 'featured',
        ], 'Proceed to payment to feature this room');
    }

    /**
     * Get list of unique cities with room listings
     */
    public function getCities()
    {
        $cities = CityOperations::selectorCities()->pluck('name')->values();

        return $this->sendSuccess($cities);
    }
}
