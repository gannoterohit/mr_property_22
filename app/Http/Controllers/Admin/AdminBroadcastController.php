<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BrandedMessageMail;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class AdminBroadcastController extends Controller
{
    /**
     * Show the Broadcast Announcement Sender page and past history.
     */
    public function index()
    {
        $pastBroadcasts = AdminNotification::where('type', 'broadcast')
            ->latest()
            ->paginate(15);

        $totalUsers  = User::where('role', 'user')->count();
        $totalOwners = User::where('role', 'owner')->count();
        $totalBrokers = User::where('role', 'broker')->count();

        // Get distinct active operational cities
        $dbCities = \App\Models\City::where('is_active', true)->pluck('name')->toArray();
        $roomCities = \App\Models\Room::pluck('city')->filter()->toArray();
        $cities = array_values(array_unique(array_filter(array_merge($dbCities, $roomCities))));
        sort($cities);

        return view('admin.broadcast.index', compact('pastBroadcasts', 'totalUsers', 'totalOwners', 'totalBrokers', 'cities'));
    }

    /**
     * Send Broadcast Notification to selected audience via selected channels.
     */
    public function send(Request $request)
    {
        $request->validate([
            'target_audience' => 'required|in:all,user,owner,broker',
            'target_city'     => 'nullable|string|max:100',
            'channels'        => 'required|array|min:1',
            'channels.*'      => 'in:bell,firebase,email',
            'title'           => 'required|string|max:255',
            'message'         => 'required|string|max:2000',
            'link'            => 'nullable|url|max:500',
            'banner_image'    => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ], [
            'channels.required' => 'Please select at least one notification channel (Bell Icon, Push, or Email).',
        ]);

        $imageUrl = null;
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('broadcasts', 'public');
            $imageUrl = asset('storage/' . $path);

            // Public copy fallback for XAMPP / Windows compatibility
            try {
                $destDir = public_path('storage/' . dirname($path));
                if (!is_dir($destDir)) {
                    @mkdir($destDir, 0755, true);
                }
                @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            } catch (\Exception $e) {}
        }

        // Query target users
        $query = User::query();
        if ($request->target_audience === 'user') {
            $query->where('role', 'user');
        } elseif ($request->target_audience === 'owner') {
            $query->where('role', 'owner');
        } elseif ($request->target_audience === 'broker') {
            $query->where('role', 'broker');
        } else {
            $query->where('role', '!=', 'admin');
        }

        // Apply City Filter if selected
        if ($request->filled('target_city')) {
            $city = $request->target_city;
            $query->where(function ($q) use ($city) {
                $q->whereHas('rooms', fn($rq) => $rq->where('city', $city))
                  ->orWhereHas('cityAlerts', fn($aq) => $aq->where('city', $city));
            });
        }

        $sentCount = 0;
        $channels = $request->channels;
        $title    = $request->title;
        $message  = $request->message;
        $link     = $request->link ?: route('home');

        $query->chunk(100, function ($users) use ($channels, $title, $message, $link, $imageUrl, &$sentCount) {
            foreach ($users as $user) {
                $sentCount++;

                // 1. In-App Bell Icon Notification
                if (in_array('bell', $channels, true)) {
                    try {
                        UserNotification::send(
                            $user->id,
                            'broadcast',
                            $title,
                            $message,
                            $link,
                            'fa-bullhorn',
                            $imageUrl
                        );
                    } catch (\Exception $e) {
                        Log::warning("Broadcast Bell Notification failed for User #{$user->id}: " . $e->getMessage());
                    }
                }

                // 2. Firebase Push Notification (Mobile + Web)
                if (in_array('firebase', $channels, true)) {
                    try {
                        FirebaseService::sendToUser(
                            $user,
                            $title,
                            $message,
                            ['type' => 'broadcast', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK'],
                            $link,
                            $imageUrl
                        );
                    } catch (\Exception $e) {
                        Log::warning("Broadcast Push Notification failed for User #{$user->id}: " . $e->getMessage());
                    }
                }

                // 3. Branded Email Notification
                if (in_array('email', $channels, true) && !empty($user->email)) {
                    try {
                        Mail::to($user->email)->send(new BrandedMessageMail(
                            $title,
                            $title,
                            $message,
                            'Special Announcement',
                            'View Details',
                            $link,
                            [],
                            'primary',
                            null,
                            $imageUrl
                        ));
                    } catch (\Exception $e) {
                        Log::warning("Broadcast Email failed for User #{$user->id}: " . $e->getMessage());
                    }
                }
            }
        });

        // Save entry in Admin Notification history
        $audienceLabel = match ($request->target_audience) {
            'user'  => 'Renters only',
            'owner' => 'Owners only',
            'broker' => 'Brokers only',
            default => 'All Users & Owners',
        };

        if ($request->filled('target_city')) {
            $audienceLabel .= " ({$request->target_city})";
        }

        $logPayload = [
            'audience'    => $audienceLabel,
            'sent_count'  => $sentCount,
            'channels'    => $channels,
            'image_url'   => $imageUrl,
            'target_link' => $request->link,
            'message'     => $message,
        ];

        AdminNotification::send(
            'broadcast',
            $title,
            json_encode($logPayload),
            $request->link ?: $imageUrl,
            'fa-bullhorn'
        );

        $successMessage = "Broadcast announcement successfully sent to {$sentCount} recipient(s) in " . ($request->target_city ?: 'all cities') . "!";

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => $successMessage,
                'data' => ['sent_count' => $sentCount],
            ]);
        }

        return back()->with('success', $successMessage);
    }
}
