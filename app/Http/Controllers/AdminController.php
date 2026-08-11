<?php

namespace App\Http\Controllers;

use App\Mail\RoomApprovedMail;
use App\Mail\RoomRejectedMail;
use App\Models\Payment;
use App\Models\RejectionReason;
use App\Models\Room;
use App\Models\RoomOption;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $admin = request()->user();
        $allowed = fn (string $permission) => $admin->hasAdminPermission($permission);
        $access = [
            'listings' => $allowed('listings.view'), 'people' => $allowed('people.view'),
            'support' => $allowed('support.view'), 'finance' => $allowed('finance.view'),
            'content' => $allowed('content.view'), 'reports' => $allowed('reports.view'),
            'settings' => $allowed('settings.manage'), 'staff' => $allowed('staff.manage'),
            'activity' => $allowed('activity.view'),
            'listings_manage' => $allowed('listings.manage'), 'people_manage' => $allowed('people.manage'),
            'support_manage' => $allowed('support.manage'), 'finance_manage' => $allowed('finance.manage'),
            'content_manage' => $allowed('content.manage'), 'reports_manage' => $allowed('reports.manage'),
        ];
        $data = ['access' => $access, 'actionQueues' => [], 'quickLinks' => [], 'revenueData' => array_fill(0, 12, 0)];

        if ($access['listings']) {
            $data += [
                'rooms' => Room::where('listing_fee_paid', true)->count(),
                'activeRooms' => Room::where('status', 'active')->where('listing_fee_paid', true)->count(),
                'approvedRooms' => Room::where('listing_status', 'approved')->count(),
                'pendingRooms' => Room::where('listing_status', 'pending')->count(),
                'rejectedRooms' => Room::where('listing_status', 'rejected')->count(),
                'recentRooms' => Room::with('owner')->latest()->limit(5)->get(),
            ];
            $data['actionQueues'][] = ['label' => 'Pending room approvals', 'count' => $data['pendingRooms'], 'route' => route('admin.all-rooms', ['listing_status' => 'pending']), 'icon' => 'fa-house-circle-exclamation'];
            $data['quickLinks'][] = ['label' => 'All listings', 'route' => route('admin.all-rooms'), 'icon' => 'fa-building'];
            if ($access['listings_manage']) {
                $data['quickLinks'][] = ['label' => 'Add room', 'route' => route('admin.rooms.create'), 'icon' => 'fa-plus'];
            }
        }
        if ($access['people']) {
            $data += [
                'users' => User::where('role', 'user')->count(),
                'owners' => User::where('role', 'owner')->count(),
                'recentUsers' => User::where('role', 'user')->latest()->limit(5)->get(),
                'recentOwners' => User::where('role', 'owner')->withCount('rooms')->latest()->limit(5)->get(),
            ];
            $data['actionQueues'][] = ['label' => 'Pending owner KYC', 'count' => User::where('role', 'owner')->where('verification_status', 'pending')->count(), 'route' => route('admin.owners', ['verification_status' => 'pending']), 'icon' => 'fa-id-card'];
            $data['quickLinks'][] = ['label' => 'Users & owners', 'route' => route('admin.members.index'), 'icon' => 'fa-users'];
            if ($access['people_manage']) {
                $data['quickLinks'][] = ['label' => 'Add owner', 'route' => route('admin.owners.create'), 'icon' => 'fa-user-plus'];
            }
        }
        if ($access['support']) {
            $data['openComplaints'] = \App\Models\Complaint::whereNotIn('status', ['resolved', 'rejected', 'closed'])->count();
            $data['unreadContacts'] = \App\Models\ContactMessage::where('is_read', false)->count();
            $data['recentComplaints'] = \App\Models\Complaint::with('assignee:id,name')->latest()->limit(5)->get();
            $data['actionQueues'][] = ['label' => 'Unresolved complaints', 'count' => $data['openComplaints'], 'route' => route('admin.complaints.index', ['status' => 'open']), 'icon' => 'fa-shield-halved'];
            $data['actionQueues'][] = ['label' => 'Unread contact enquiries', 'count' => $data['unreadContacts'], 'route' => route('admin.contact-messages.index'), 'icon' => 'fa-envelope'];
            $data['quickLinks'][] = ['label' => 'Support tickets', 'route' => route('admin.complaints.index'), 'icon' => 'fa-headset'];
            $data['quickLinks'][] = ['label' => 'Contact enquiries', 'route' => route('admin.contact-messages.index'), 'icon' => 'fa-envelope'];
        }
        if ($access['finance']) {
            $types = ['listing', 'featured', 'unlock', 'subscription'];
            $completed = Payment::where('status', 'completed')->whereIn('type', $types);
            $data += [
                'totalEarnings' => (clone $completed)->sum('amount'),
                'todayEarnings' => (clone $completed)->whereDate('created_at', today())->sum('amount'),
                'currentMonthEarnings' => (clone $completed)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('amount'),
                'lastMonthEarnings' => (clone $completed)->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('amount'),
                'recentPayments' => Payment::with('user')->latest()->limit(5)->get(),
            ];
            $monthSql = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
            $monthly = (clone $completed)->selectRaw("{$monthSql} month, SUM(amount) total")->whereYear('created_at', now()->year)->groupByRaw($monthSql)->pluck('total', 'month');
            $data['revenueData'] = collect(range(1, 12))->map(fn ($month) => (float) ($monthly[$month] ?? 0))->all();
            $data['percentageChange'] = $data['lastMonthEarnings'] > 0 ? (($data['currentMonthEarnings'] - $data['lastMonthEarnings']) / $data['lastMonthEarnings']) * 100 : 0;
            $data['actionQueues'][] = ['label' => 'Failed / pending payments', 'count' => Payment::whereIn('status', ['failed', 'pending'])->count(), 'route' => route('admin.payments.index', ['status' => 'pending']), 'icon' => 'fa-credit-card'];
            $data['quickLinks'][] = ['label' => 'Payments', 'route' => route('admin.payments.index'), 'icon' => 'fa-credit-card'];
            $data['quickLinks'][] = ['label' => 'Plans', 'route' => route('admin.plans.index'), 'icon' => 'fa-tags'];
            if ($access['finance_manage']) {
                $data['quickLinks'][] = ['label' => 'Payments', 'route' => route('admin.payments.index'), 'icon' => 'fa-credit-card'];
            }
        }
        if ($access['content']) {
            $data['contentStats'] = [
                'blogs' => \App\Models\Blog::count(),
                'offers' => \App\Models\Offer::count(),
                'pages' => \App\Models\CmsPage::where('slug', '!=', 'how-it-works')->count(),
                'home_features' => \App\Models\HomeFeature::count(),
                'how_it_works' => \App\Models\HowItWorksItem::count(),
                'testimonials' => \App\Models\Testimonial::count(),
            ];
            $data['quickLinks'][] = ['label' => 'Blogs', 'route' => route('admin.blogs.index'), 'icon' => 'fa-newspaper'];
            $data['quickLinks'][] = ['label' => 'Offers', 'route' => route('admin.offers.index'), 'icon' => 'fa-bullhorn'];
            $data['quickLinks'][] = ['label' => 'Why Choose Us', 'route' => route('admin.home-features.index'), 'icon' => 'fa-circle-check'];
            $data['quickLinks'][] = ['label' => 'How It Works', 'route' => route('admin.how-it-works.index'), 'icon' => 'fa-route'];
            $data['quickLinks'][] = ['label' => 'Testimonials', 'route' => route('admin.testimonials.index'), 'icon' => 'fa-star'];
        }
        if ($access['reports']) {
            $data['reportStats'] = ['searches_today' => \App\Models\SearchLog::whereDate('created_at', today())->count(), 'unlocks_today' => \App\Models\Enquiry::where('unlocked', true)->whereDate('unlocked_at', today())->count()];
            $data['quickLinks'][] = ['label' => 'Business reports', 'route' => route('admin.reports'), 'icon' => 'fa-chart-pie'];
            $data['quickLinks'][] = ['label' => 'Search analytics', 'route' => route('admin.analytics'), 'icon' => 'fa-chart-line'];
        }
        if ($access['settings']) {
            $data['quickLinks'][] = ['label' => 'Business settings', 'route' => route('admin.settings'), 'icon' => 'fa-gear'];
            $data['quickLinks'][] = ['label' => 'Data maintenance', 'route' => route('admin.data-maintenance.index'), 'icon' => 'fa-database'];
        }
        if ($access['staff']) {
            $data['quickLinks'][] = ['label' => 'Staff & roles', 'route' => route('admin.staff.index'), 'icon' => 'fa-users-gear'];
        }
        if ($access['activity']) {
            $data['quickLinks'][] = ['label' => 'Activity logs', 'route' => route('admin.activity.index'), 'icon' => 'fa-clock-rotate-left'];
        }

        return view('admin.dashboard', $data);
    }

    public function rooms(Request $request)
    {
        $query = Room::with(['owner', 'roomTypeOption', 'propertyType', 'propertyCategory'])->whereHas('owner');
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q->where('title', 'like', '%'.$request->search.'%')->orWhere('city', 'like', '%'.$request->search.'%'));
        }
        if ($request->filled('listing_status')) {
            $query->where('listing_status', $request->listing_status);
        }
        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->moderation_status);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('room_type')) {
            $query->where('room_type_option_id', $request->room_type);
        }
        if ($request->filled('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }
        if ($request->filled('property_category_id')) {
            $query->where('property_category_id', $request->property_category_id);
        }
        if ($request->filled('min_area_sqft')) {
            $query->where('area_sqft', '>=', $request->min_area_sqft);
        }
        if ($request->filled('max_area_sqft')) {
            $query->where('area_sqft', '<=', $request->max_area_sqft);
        }
        if ($request->filled('kyc')) {
            $query->whereHas('owner', fn ($q) => $q->where('verification_status', $request->kyc));
        }
        $perPage = in_array((int) $request->input('per_page', 10), [10, 25, 50], true)
            ? (int) $request->input('per_page', 10)
            : 10;
        $allrooms = $query->latest()->paginate($perPage)->withQueryString();
        $rejectionReasons = RejectionReason::where('is_active', true)->get();
        $cities = Room::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        $propertyTypes = \App\Models\PropertyType::cachedActive();
        $propertyCategories = \App\Models\PropertyCategory::with('propertyType:id,name')->where('status', true)->orderBy('property_type_id')->orderBy('name')->get(['id', 'property_type_id', 'name']);

        return view('admin.rooms.index', compact('allrooms', 'rejectionReasons', 'cities', 'propertyTypes', 'propertyCategories'));
    }

    public function bulkRooms(Request $request)
    {
        $data = $request->validate(['room_ids' => 'required|array|min:1', 'room_ids.*' => 'exists:rooms,id', 'action' => 'required|in:approve,suspend,activate,mark_reported']);
        $updates = match ($data['action']) {
            'approve' => ['listing_status' => 'approved', 'moderation_status' => 'normal'],'suspend' => ['moderation_status' => 'suspended'],'activate' => ['moderation_status' => 'normal', 'status' => 'active'],'mark_reported' => ['moderation_status' => 'reported']
        };
        Room::whereIn('id', $data['room_ids'])->update($updates);

        return back()->with('success', count($data['room_ids']).' listings updated.');
    }

    public function approveRoom(Room $room)
    {
        try {
            // Update room status
            $room->listing_status = 'approved';
            $room->save();

            // Send email to owner (try-catch to prevent failure if mail not configured)
            try {
                $owner = $room->owner;
                Mail::to($owner->email)->send(new RoomApprovedMail($room, $owner));

                // Trigger City Alerts
                $alerts = \App\Models\CityAlert::with('user')->where('city', $room->city)->get();
                foreach ($alerts as $alert) {
                    if ($alert->user && $alert->user->email) {
                        Mail::to($alert->user->email)->send(new \App\Mail\NewRoomInCityAlert($room, $room->city));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Mail sending failed: '.$e->getMessage());
                // Continue execution even if mail fails
            }

            \App\Services\NotificationService::notifyRoomApproved($room);

            return response()->json(['success' => true, 'message' => 'Room approved successfully']);
        } catch (\Exception $e) {
            \Log::error('Room approval error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Unable to approve the room. Please try again.'], 500);
        }
    }

    public function rejectRoom(Request $request, Room $room)
    {
        try {
            $request->validate([
                'reasons' => 'required_without:customReason|array|min:1',
                'reasons.*' => 'exists:rejection_reasons,id',
                'customReason' => 'nullable|string|max:500|required_without:reasons',
            ]);

            // Update room status
            $room->listing_status = 'rejected';
            $room->save();

            // Save rejection reasons
            if (! empty($request->reasons)) {
                $room->rejectionReasons()->sync($request->reasons);
            }

            // Get all rejection reasons for email
            $reasons = [];
            if (! empty($request->reasons)) {
                $selectedReasons = RejectionReason::whereIn('id', $request->reasons)->get();
                foreach ($selectedReasons as $reason) {
                    $reasons[] = $reason->reason;
                }
            }

            if (! empty($request->customReason)) {
                $reasons[] = $request->customReason;
            }

            // Send email to owner
            $owner = $room->owner;
            try {
                if ($owner?->email) {
                    Mail::to($owner->email)->send(new RoomRejectedMail($room, $owner, $reasons));
                }
            } catch (\Exception $mailError) {
                \Log::warning('Room rejection email failed: '.$mailError->getMessage());
            }

            $reasonsText = implode(', ', $reasons);
            \App\Services\NotificationService::notifyRoomRejected($room, $reasonsText);

            return response()->json(['success' => true, 'message' => 'Room rejected successfully']);
        } catch (\Exception $e) {
            \Log::error('Room rejection error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Unable to reject the room. Please try again.'], 500);
        }
    }

    public function deleteRoom(Room $room)
    {
        try {
            foreach (($room->photos ?? []) as $photo) {
                if ($photo && ! str_starts_with($photo, 'http')) {
                    Storage::disk('public')->delete($photo);
                }
            }
            if ($room->photo && ! str_starts_with($room->photo, 'http') && ! in_array($room->photo, $room->photos ?? [], true)) {
                Storage::disk('public')->delete($room->photo);
            }
            if ($room->video && ! str_starts_with($room->video, 'http')) {
                Storage::disk('public')->delete($room->video);
            }
            $room->delete();

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back()->with('success', 'Room deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Room deletion error: '.$e->getMessage());

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unable to delete the room. Please try again.'], 500);
            }

            return redirect()->back()->with('error', 'Unable to delete the room. Please try again.');
        }
    }

    public function toggleRoomStatus(Request $request, Room $room)
    {
        try {
            $room->update([
                'status' => $room->status === 'active' ? 'booked' : 'active',
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'new_status' => $room->status]);
            }

            return redirect()->back()->with('success', 'Room status updated.');
        } catch (\Exception $e) {
            \Log::error('Room status toggle error: '.$e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unable to update room status. Please try again.'], 500);
            }

            return redirect()->back()->with('error', 'Unable to update room status. Please try again.');
        }
    }

    public function reports(Request $request)
    {
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfDay();
        $paymentsBase = Payment::whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
        $revenueByType = (clone $paymentsBase)->where('status', 'completed')->selectRaw('type, SUM(amount) total')->groupBy('type')->pluck('total', 'type');
        $dailyCollections = (clone $paymentsBase)->where('status', 'completed')->selectRaw('DATE(created_at) day, SUM(amount) total')->groupBy('day')->orderBy('day')->get();
        $failedPayments = (clone $paymentsBase)->where('status', 'failed')->count();
        $totalUsers = User::where('role', 'user')->whereBetween('created_at', [$from, $to])->count();
        $unlocks = \App\Models\Enquiry::where('unlocked', true)->whereBetween('unlocked_at', [$from, $to])->count();
        $cityDemand = \App\Models\Enquiry::join('rooms', 'rooms.id', '=', 'enquiries.room_id')->whereBetween('enquiries.created_at', [$from, $to])->selectRaw('rooms.city, COUNT(*) total')->groupBy('rooms.city')->orderByDesc('total')->limit(10)->get();
        $ownerGrowth = User::where('role', 'owner')->whereBetween('created_at', [$from, $to])->count();
        $listingGrowth = Room::whereBetween('created_at', [$from, $to])->count();
        $resolutionHours = \App\Models\Complaint::whereNotNull('closed_at')->whereBetween('closed_at', [$from, $to])->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) avg_hours')->value('avg_hours');

        return view('admin.analytics.reports', compact('from', 'to', 'revenueByType', 'dailyCollections', 'failedPayments', 'totalUsers', 'unlocks', 'cityDemand', 'ownerGrowth', 'listingGrowth', 'resolutionHours'));
    }


    public function users(Request $request)
    {
        $query = User::withTrashed()->where('role', 'user');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->status === 'blocked') {
            $query->where('is_blocked', true);
        }
        if ($request->status === 'active') {
            $query->where('is_blocked', false)->whereNull('deleted_at');
        }
        if ($request->status === 'deleted') {
            $query->onlyTrashed();
        }

        $users = $query->latest()->paginate(10);
        $memberStats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'user')->where('is_blocked', false)->count(),
            'blocked' => User::where('role', 'user')->where('is_blocked', true)->count(),
            'deleted' => User::onlyTrashed()->where('role', 'user')->count(),
        ];

        return view('admin.members.users', compact('users', 'memberStats'));
    }

    public function member360(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $matches = collect();

        if ($term !== '') {
            $matches = User::withTrashed()
                ->whereIn('role', ['user', 'owner'])
                ->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('referral_code', 'like', "%{$term}%");
                    if (ctype_digit($term)) {
                        $query->orWhere('id', (int) $term);
                    }
                })
                ->withCount(['rooms', 'payments', 'enquiries', 'complaints'])
                ->latest()
                ->limit(20)
                ->get();
        }

        $member = null;
        $history = [];
        if ($request->filled('member_id')) {
            $member = User::withTrashed()
                ->whereIn('role', ['user', 'owner'])
                ->withCount(['rooms', 'payments', 'subscriptions', 'enquiries', 'wishlists', 'complaints', 'cityAlerts', 'referrals'])
                ->findOrFail($request->integer('member_id'));

            $history = [
                'rooms' => Room::where('user_id', $member->id)->latest()->limit(25)->get(),
                'payments' => \App\Models\Payment::where('user_id', $member->id)->latest()->limit(25)->get(),
                'subscriptions' => \App\Models\Subscription::with('plan')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'enquiries' => \App\Models\Enquiry::with('room')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'complaints' => \App\Models\Complaint::with(['room', 'againstUser'])
                    ->where(fn ($query) => $query->where('user_id', $member->id)->orWhere('against_user_id', $member->id))
                    ->latest()->limit(25)->get(),
                'wishlists' => \App\Models\Wishlist::with('room')->where('user_id', $member->id)->latest()->limit(25)->get(),
                'city_alerts' => \App\Models\CityAlert::where('user_id', $member->id)->latest()->limit(25)->get(),
                'referrals' => User::withTrashed()->where('referred_by_id', $member->id)->latest()->limit(25)->get(),
                'activities' => \App\Models\AdminActivityLog::with('actor')
                    ->where('subject_type', User::class)->where('subject_id', $member->id)
                    ->latest()->limit(25)->get(),
            ];
        }

        return view('admin.members.index', compact('term', 'matches', 'member', 'history'));
    }

    public function owners(Request $request)
    {
        $query = User::withTrashed()->where('role', 'owner')->withCount('rooms');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->status === 'active') {
            $query->where('is_blocked', false)->whereNull('deleted_at');
        }
        if ($request->status === 'blocked') {
            $query->where('is_blocked', true);
        }
        if ($request->status === 'deleted') {
            $query->onlyTrashed();
        }

        $owners = $query->latest()->paginate(10);
        $memberStats = [
            'total' => User::where('role', 'owner')->count(),
            'verified' => User::where('role', 'owner')->where('verification_status', 'verified')->count(),
            'blocked' => User::where('role', 'owner')->where('is_blocked', true)->count(),
            'deleted' => User::onlyTrashed()->where('role', 'owner')->count(),
        ];

        return view('admin.members.owners', compact('owners', 'memberStats'));
    }

    public function userDetail(User $user)
    {
        abort_unless($user->role === 'user', 404);
        $user->load(['payments', 'subscriptions.plan', 'complaints', 'enquiries.room', 'adminActivities.actor']);

        return view('admin.members.user-detail', compact('user'));
    }

    public function ownerDetail(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->load(['rooms', 'payments', 'subscriptions.plan', 'complaints', 'adminActivities.actor']);
        $rooms = $owner->rooms()->latest()->paginate(10);

        return view('admin.members.owner-detail', compact('owner', 'rooms'));
    }

    public function toggleBlock(Request $request, User $user)
    {
        $request->validate(['block_reason' => 'nullable|string|max:255']);
        $blocking = ! $user->is_blocked;
        $user->update([
            'is_blocked' => $blocking,
            'block_reason' => $blocking ? ($request->block_reason ?: 'Blocked by administrator') : null,
        ]);

        $status = $user->is_blocked ? 'blocked' : 'unblocked';

        return back()->with('success', "User {$status} successfully!");
    }

    public function updateMemberNotes(Request $request, User $user)
    {
        $data = $request->validate(['admin_notes' => 'nullable|string|max:5000', 'verification_status' => 'required|in:pending,under_review,verified,rejected']);
        $data['is_verified'] = $data['verification_status'] === 'verified';
        $data['verified_at'] = $data['is_verified'] ? now() : null;
        $user->update($data);

        return back()->with('success', 'Member notes and verification updated.');
    }

    /**
     * Send direct message / notification to an individual User or Owner via selected channels.
     */
    public function sendDirectMessage(Request $request, User $user)
    {
        $request->validate([
            'channels'     => 'required|array|min:1',
            'channels.*'   => 'in:bell,firebase,email,sms',
            'title'        => 'required|string|max:255',
            'message'      => 'required|string|max:2000',
            'link'         => 'nullable|url|max:500',
            'banner_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:3072',
        ], [
            'channels.required' => 'Please select at least one notification channel.',
        ]);

        $imageUrl = null;
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('broadcasts', 'public');
            $imageUrl = asset('storage/' . $path);
            try {
                $destDir = public_path('storage/' . dirname($path));
                if (!is_dir($destDir)) { @mkdir($destDir, 0755, true); }
                @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            } catch (\Exception $e) {}
        }

        $channels = $request->channels;
        $title    = $request->title;
        $message  = $request->message;
        $link     = $request->link ?: route('home');

        $sentChannels = [];

        // 1. In-App Bell Icon
        if (in_array('bell', $channels, true)) {
            try {
                \App\Models\UserNotification::send(
                    $user->id,
                    'announcement',
                    $title,
                    $message,
                    $link,
                    'fa-envelope-open-text',
                    $imageUrl
                );
                $sentChannels[] = 'Bell Icon';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Direct Bell Notification failed for User #{$user->id}: " . $e->getMessage());
            }
        }

        // 2. Firebase Push Notification (Mobile + Web)
        if (in_array('firebase', $channels, true)) {
            try {
                \App\Services\FirebaseService::sendToUser(
                    $user,
                    $title,
                    $message,
                    ['type' => 'announcement', 'click_action' => 'FLUTTER_NOTIFICATION_CLICK'],
                    $link,
                    $imageUrl
                );
                $sentChannels[] = 'Firebase Push';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Direct Push Notification failed for User #{$user->id}: " . $e->getMessage());
            }
        }

        // 3. Email Notification
        if (in_array('email', $channels, true) && !empty($user->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\BrandedMessageMail(
                    $title,
                    $title,
                    $message,
                    'Direct Message from ApnaNest Support',
                    'View Details',
                    $link,
                    [],
                    'primary',
                    null,
                    $imageUrl
                ));
                $sentChannels[] = 'Email';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Direct Email failed for User #{$user->id}: " . $e->getMessage());
            }
        }

        // 4. SMS Notification
        if (in_array('sms', $channels, true) && !empty($user->phone)) {
            try {
                $smsSuccess = \App\Services\SmsService::sendMessage($user->phone, "ApnaNest: {$title}\n{$message}");
                if ($smsSuccess) {
                    $sentChannels[] = 'SMS';
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Direct SMS failed for User #{$user->id}: " . $e->getMessage());
            }
        }

        if (empty($sentChannels)) {
            return back()->with('error', 'Could not deliver message. Please check user contact details or token.');
        }

        return back()->with('success', 'Direct message sent to ' . $user->name . ' via ' . implode(', ', $sentChannels) . '!');
    }


    public function restoreMember(int $user)
    {
        $member = User::withTrashed()->findOrFail($user);
        $member->restore();

        return back()->with('success', 'Account restored.');
    }

    public function createUser()
    {
        return view('admin.members.form', ['member' => new User, 'memberRole' => 'user']);
    }

    public function storeUser(Request $request)
    {
        $user = $this->createMember($request, 'user');

        return redirect()->route('admin.users.detail', $user)->with('success', 'User account created successfully.');
    }

    public function editUser(User $user)
    {
        abort_unless($user->role === 'user', 404);

        return view('admin.members.form', ['member' => $user, 'memberRole' => 'user']);
    }

    public function updateUser(Request $request, User $user)
    {
        abort_unless($user->role === 'user', 404);
        $this->updateMember($request, $user);

        return redirect()->route('admin.users.detail', $user)->with('success', 'User account updated successfully.');
    }

    public function destroyUser(User $user)
    {
        abort_unless($user->role === 'user', 404);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User account deleted. It can be restored from the Deleted filter.');
    }

    public function cityAlerts(Request $request)
    {
        $query = \App\Models\CityAlert::with('user');
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(fn ($q) => $q->where('city', 'like', "%{$term}%")->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
        }
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }
        $alerts = $query->latest()->paginate(15)->withQueryString();
        $cities = \App\Models\CityAlert::whereNotNull('city')->distinct()->orderBy('city')->pluck('city');
        $cityStats = \App\Models\CityAlert::selectRaw('city,COUNT(*) total')->groupBy('city')->orderByDesc('total')->limit(4)->get();

        return view('admin.city-alerts.index', compact('alerts', 'cities', 'cityStats'));
    }

    public function deleteCityAlert($id)
    {
        $alert = \App\Models\CityAlert::findOrFail($id);
        $alert->delete();

        return back()->with('success', 'City alert subscription removed successfully!');
    }

    // New Owner Registration by Admin
    public function createOwner()
    {
        return view('admin.members.form', ['member' => new User, 'memberRole' => 'owner']);
    }

    public function storeOwner(Request $request)
    {
        $owner = $this->createMember($request, 'owner');

        return redirect()->route('admin.owners.detail', $owner)->with('success', 'Owner account created successfully.');
    }

    public function editOwner(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);

        return view('admin.members.form', ['member' => $owner, 'memberRole' => 'owner']);
    }

    public function updateOwner(Request $request, User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $this->updateMember($request, $owner);

        return redirect()->route('admin.owners.detail', $owner)->with('success', 'Owner account updated successfully.');
    }

    public function destroyOwner(User $owner)
    {
        abort_unless($owner->role === 'owner', 404);
        $owner->delete();

        return redirect()->route('admin.owners')->with('success', 'Owner account deleted. Listings are retained and the account can be restored.');
    }

    private function createMember(Request $request, string $role): User
    {
        $data = $this->validateMember($request);
        // Members authenticate with an email OTP. Keep an unusable random value only
        // because the legacy users table still requires a non-null password column.
        $data['password'] = Hash::make(Str::random(64));
        $data['role'] = $role;
        $data['is_verified'] = $data['verification_status'] === 'verified';
        $data['verified_at'] = $data['is_verified'] ? now() : null;
        $data['email_verified_at'] = $request->boolean('email_verified') ? now() : null;
        $data['is_blocked'] = $request->boolean('is_blocked');
        $data['block_reason'] = $data['is_blocked'] ? ($data['block_reason'] ?: 'Blocked by administrator') : null;
        unset($data['email_verified']);

        return User::create($data);
    }

    private function updateMember(Request $request, User $member): void
    {
        $data = $this->validateMember($request, $member);
        $data['is_verified'] = $data['verification_status'] === 'verified';
        $data['verified_at'] = $data['is_verified'] ? ($member->verified_at ?: now()) : null;
        $data['email_verified_at'] = $request->boolean('email_verified') ? ($member->email_verified_at ?: now()) : null;
        $data['is_blocked'] = $request->boolean('is_blocked');
        $data['block_reason'] = $data['is_blocked'] ? ($data['block_reason'] ?: 'Blocked by administrator') : null;
        unset($data['email_verified']);
        $member->update($data);
    }

    private function validateMember(Request $request, ?User $member = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($member?->id)],
            'verification_status' => ['required', Rule::in(['pending', 'under_review', 'verified', 'rejected'])],
            'wallet_balance' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'free_unlocks' => ['required', 'integer', 'min:0', 'max:100000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'block_reason' => ['nullable', 'string', 'max:255'],
            'is_blocked' => ['nullable', 'boolean'],
            'email_verified' => ['nullable', 'boolean'],
        ]);
    }

    // Room Management by Admin
    public function createRoom()
    {
        $owners = User::where('role', 'owner')->get();

        return view('admin.rooms.create', compact('owners'));
    }

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'owner_id' => ['required', Rule::exists('users', 'id')->where('role', 'owner')],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'property_category_id' => [
                'required',
                'integer',
                Rule::exists('property_categories', 'id')->where(fn ($query) => $query->where('status', true)->where('property_type_id', $request->property_type_id)),
            ],
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'area_sqft' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'required|string',
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
            'listing_type' => 'nullable|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
            'is_featured' => 'nullable|boolean',
            'listing_fee_paid' => 'nullable|boolean',
        ]);

        $data['user_id'] = $request->owner_id;
        unset($data['owner_id']);
        $data['listing_status'] = 'approved';
        $data['listing_fee_paid'] = true;
        $data['is_featured'] = $request->has('is_featured');
        $data['listing_type'] = $data['listing_type'] ?? 'owner';
        $data['broker_fee'] = $data['listing_type'] === 'broker' ? ($data['broker_fee'] ?? 0) : 0;
        $data['status'] = 'active';

        // Convert empty latitude/longitude strings to null
        if (isset($data['latitude']) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }

        $newPhotoPaths = [];
        $newVideoPath = null;
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
                    throw new \RuntimeException('One of the selected images could not be processed.');
                }
                $photos[] = $path;
                $newPhotoPaths[] = $path;
            }
            $data['photos'] = $photos;
            $data['photo'] = $photos[0];
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $newVideoPath = $request->file('video')->store('rooms/videos', 'public');
            $data['video'] = $newVideoPath;
        }

        $data = $this->mapRoomOptionData($data);

        Room::create($data);
        DB::commit();

        return redirect()->route('admin.all-rooms')->with('success', 'Room created successfully by Admin!');
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }
            report($e);

            return back()->withInput()->with('error', 'The room could not be created. Please try again.');
        }
    }

    public function editRoom(Room $room)
    {
        $owners = User::where('role', 'owner')->get();

        return view('admin.rooms.edit', compact('room', 'owners'));
    }

    public function updateRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'owner_id' => ['required', Rule::exists('users', 'id')->where('role', 'owner')],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'property_type_id' => ['required', 'integer', Rule::exists('property_types', 'id')->where('status', true)],
            'property_category_id' => [
                'required',
                'integer',
                Rule::exists('property_categories', 'id')->where(fn ($query) => $query->where('status', true)->where('property_type_id', $request->property_type_id)),
            ],
            'rent' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'area_sqft' => 'nullable|numeric|min:0',
            'city' => 'required|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'furnishing_type' => ['required', Rule::in(RoomOption::validIdsFor('furnishing_type'))],
            'tenant_type' => ['required', Rule::in(RoomOption::validIdsFor('tenant_type'))],
            'amenities' => 'nullable|array',
            'amenities.*' => ['string', Rule::in(RoomOption::activeLabelsFor('amenity')->all())],
            'landmarks' => 'nullable|array',
            'landmarks.*' => 'string',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'photos' => 'nullable|array|max:5',
            'video' => 'nullable|mimes:mp4,avi,mov,wmv|max:10240',
            'video_url' => 'nullable|url|max:255',
            'listing_type' => 'nullable|in:owner,broker',
            'broker_fee' => 'nullable|numeric|min:0',
            'is_featured' => 'nullable|boolean',
            'listing_fee_paid' => 'nullable|boolean',
            'listing_status' => 'nullable|in:pending,approved,rejected',
            'status' => 'nullable|in:active,booked',
        ]);

        $data['user_id'] = $request->owner_id;
        unset($data['owner_id']);
        $data['listing_type'] = $data['listing_type'] ?? $room->listing_type ?? 'owner';
        $data['broker_fee'] = $data['listing_type'] === 'broker' ? ($data['broker_fee'] ?? 0) : 0;
        $data['is_featured'] = $request->has('is_featured');
        $data['listing_fee_paid'] = $request->has('listing_fee_paid');
        $data['listing_status'] = $request->listing_status ?? $room->listing_status;
        $data['status'] = $request->status ?? $room->status;

        // Convert empty latitude/longitude strings to null
        if (isset($data['latitude']) && $data['latitude'] === '') {
            $data['latitude'] = null;
        }
        if (isset($data['longitude']) && $data['longitude'] === '') {
            $data['longitude'] = null;
        }

        $oldPhotosToDelete = [];
        $newPhotoPaths = [];
        $oldVideoToDelete = null;
        $newVideoPath = null;
        DB::beginTransaction();
        try {
        if ($request->hasFile('photos')) {
            // Store the replacement first. Old media is removed only after the DB update succeeds.
            $oldPhotosToDelete = is_array($room->photos) ? $room->photos : (json_decode($room->photos ?: '[]', true) ?: []);
            if ($room->photo && ! in_array($room->photo, $oldPhotosToDelete, true)) {
                $oldPhotosToDelete[] = $room->photo;
            }
            $photos = [];
            foreach ($request->file('photos') as $photo) {
                $filename = uniqid('room_').'.jpg';
                $path = 'rooms/'.$filename;
                $fullPath = storage_path('app/public/'.$path);
                if (! file_exists(storage_path('app/public/rooms'))) {
                    mkdir(storage_path('app/public/rooms'), 0755, true);
                }
                if (! \App\Helpers\ImageHelper::compressImage($photo->getRealPath(), $fullPath, 70)) {
                    throw new \RuntimeException('One of the selected images could not be processed.');
                }
                $photos[] = $path;
                $newPhotoPaths[] = $path;
            }
            $data['photos'] = $photos;
            $data['photo'] = $photos[0];
        }

        // Handle video upload
        if ($request->hasFile('video')) {
            $oldVideoToDelete = $room->video;
            $newVideoPath = $request->file('video')->store('rooms/videos', 'public');
            $data['video'] = $newVideoPath;
        }

        $data = $this->mapRoomOptionData($data);

        $room->update($data);
        DB::commit();

        foreach ($oldPhotosToDelete as $oldPhoto) {
            if ($oldPhoto && ! str_starts_with($oldPhoto, 'http') && ! in_array($oldPhoto, $data['photos'] ?? [], true)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }
        if ($oldVideoToDelete) {
            Storage::disk('public')->delete($oldVideoToDelete);
        }

        return redirect()->route('admin.rooms.edit', $room->fresh())->with('success', 'Room details and media updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            if ($newVideoPath) {
                Storage::disk('public')->delete($newVideoPath);
            }
            report($e);

            return back()->withInput()->with('error', 'The room could not be updated. Please try again.');
        }
    }

    public function showRoom(Room $room)
    {
        $isUnlocked = true;
        $isOwner = true;
        $subscriptionRemaining = 0;
        $room->load([
            'owner',
            'rejectionReasons',
            'propertyType',
            'propertyCategory',
            'roomTypeOption',
            'furnishingOption',
            'tenantOption',
        ]);
        $rejectionReasons = RejectionReason::where('is_active', true)->orderBy('reason')->get();

        return view('admin.rooms.show', compact('room', 'isUnlocked', 'isOwner', 'subscriptionRemaining', 'rejectionReasons'));
    }

    public function paymentsIndex(Request $request)
    {
        $query = Payment::with('user');
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(fn ($q) => $q->where('transaction_id', 'like', "%{$term}%")
                ->orWhere('gateway_order_id', 'like', "%{$term}%")->orWhere('reference_id', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('gateway')) {
            $query->where('gateway', $request->gateway);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (float) $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (float) $request->max_amount);
        }
        $filtered = (clone $query);
        $paymentStats = [
            'total' => $filtered->count(),
            'collected' => (clone $filtered)->where('status', 'completed')->sum('amount'),
            'completed' => (clone $filtered)->where('status', 'completed')->count(),
            'pending' => (clone $filtered)->where('status', 'pending')->count(),
            'failed' => (clone $filtered)->where('status', 'failed')->count(),
        ];
        $payments = $query->latest()->paginate(20)->withQueryString();
        $types = Payment::whereNotNull('type')->distinct()->orderBy('type')->pluck('type');
        $gateways = Payment::whereNotNull('gateway')->distinct()->orderBy('gateway')->pluck('gateway');

        return view('admin.payments.index', compact('payments', 'paymentStats', 'types', 'gateways'));
    }

    public function contactMessages()
    {
        $messages = \App\Models\ContactMessage::latest()->paginate(15);

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function deleteContactMessage($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->delete();

        return back()->with('success', 'Contact message deleted successfully!');
    }

    public function markMessageAsRead($id)
    {
        $message = \App\Models\ContactMessage::findOrFail($id);
        $message->update(['is_read' => true]);

        \App\Models\AdminNotification::where('type', 'contact_inquiry')
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Message marked as read.');
    }
}
