<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokerPayment;
use App\Models\BrokerReview;
use App\Models\BrokerSubscription;
use App\Models\BrokerTransaction;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminBrokerController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.view'), 403);

        User::cleanupExpiredFeaturedAgencies();

        $query = User::where('role', 'broker');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('agency_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('verification_status')) {
            $query->where('broker_verification_status', $status);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured_agency', $request->featured === '1');
        }

        if ($active = $request->get('is_broker_active')) {
            $query->where('is_broker_active', $active === '1' || $active === 'true');
        } elseif ($accountStatus = $request->get('status')) {
            if ($accountStatus === 'active') {
                $query->where('is_broker_active', true);
            } elseif ($accountStatus === 'suspended') {
                $query->where(function ($q) {
                    $q->where('is_broker_active', false)
                      ->orWhere('broker_verification_status', 'suspended');
                });
            }
        }

        $brokers = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => User::where('role', 'broker')->count(),
            'pending' => User::where('role', 'broker')->where('broker_verification_status', 'pending')->count(),
            'approved' => User::where('role', 'broker')->where('broker_verification_status', 'approved')->count(),
            'rejected' => User::where('role', 'broker')->where('broker_verification_status', 'rejected')->count(),
            'suspended' => User::where('role', 'broker')->where('broker_verification_status', 'suspended')->count(),
            'featured' => User::where('role', 'broker')->where('is_featured_agency', true)->count(),
        ];

        return view('admin.brokers.index', compact('brokers', 'stats'));
    }

    public function show(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.view'), 403);
        abort_if($broker->role !== 'broker', 404);

        $broker->load(['brokerProperties', 'brokerSubscription.plan', 'brokerPayments', 'brokerWallet']);

        $properties = $broker->brokerProperties()->latest()->paginate(10);
        $payments = $broker->brokerPayments()->latest()->paginate(10);
        $subscriptions = $broker->brokerSubscription()->latest()->paginate(5);
        $reviews = $broker->brokerReviews()->with('user:id,name,email,avatar')->latest()->paginate(10, ['*'], 'reviews_page');

        return view('admin.brokers.show', compact('broker', 'properties', 'payments', 'subscriptions', 'reviews'));
    }

    public function approve(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $broker->update([
            'broker_verification_status' => 'approved',
            'broker_verified_at' => now(),
            'is_broker_active' => true,
            'broker_approved_at' => now(),
            'broker_rejected_reason' => null,
        ]);

        \App\Services\NotificationService::notifyBrokerStatusChanged($broker, 'approved');

        return back()->with('success', 'Broker approved successfully.');
    }

    public function reject(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $broker->update([
            'broker_verification_status' => 'rejected',
            'is_broker_active' => false,
            'broker_rejected_reason' => $request->reason,
        ]);

        \App\Services\NotificationService::notifyBrokerStatusChanged($broker, 'rejected', $request->reason);

        return back()->with('success', 'Broker rejected successfully.');
    }

    public function suspend(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $broker->update([
            'broker_verification_status' => 'suspended',
            'is_broker_active' => false,
        ]);

        \App\Services\NotificationService::notifyBrokerStatusChanged($broker, 'suspended');

        return back()->with('success', 'Broker suspended successfully.');
    }

    public function activate(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $broker->update([
            'broker_verification_status' => 'approved',
            'is_broker_active' => true,
            'broker_rejected_reason' => null,
        ]);

        \App\Services\NotificationService::notifyBrokerStatusChanged($broker, 'approved');

        return back()->with('success', 'Broker activated successfully.');
    }

    public function destroy(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $broker->brokerProperties()->update(['listed_by' => 'owner', 'broker_id' => null]);
        $broker->delete();

        return redirect()->route('admin.brokers.index')->with('success', 'Broker deleted successfully.');
    }

    public function toggleFeatured(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $newStatus = !$broker->is_featured_agency;
        $broker->update([
            'is_featured_agency' => $newStatus,
            'featured_agency_expires_at' => $newStatus ? now()->addMonths(1) : null,
        ]);

        $agencyName = $broker->agency_name ?: $broker->name;
        $msg = $newStatus
            ? "Agency '{$agencyName}' is now marked as Featured Agency (Spotlight Active)."
            : "Agency '{$agencyName}' is removed from Featured Agencies.";

        return back()->with('success', $msg);
    }

    public function setFeaturedDuration(Request $request, User $broker)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($broker->role !== 'broker', 404);

        $duration = $request->input('duration', '1_month');

        if ($duration === 'remove') {
            $broker->update([
                'is_featured_agency' => false,
                'featured_agency_expires_at' => null,
            ]);
            $agencyName = $broker->agency_name ?: $broker->name;
            return back()->with('success', "Featured spotlight removed for {$agencyName}.");
        }

        $expiresAt = match ($duration) {
            '1_month' => now()->addMonth(),
            '3_months' => now()->addMonths(3),
            '6_months' => now()->addMonths(6),
            '1_year' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };

        $broker->update([
            'is_featured_agency' => true,
            'featured_agency_expires_at' => $expiresAt,
        ]);

        $agencyName = $broker->agency_name ?: $broker->name;
        $expiryText = $expiresAt ? 'until ' . $expiresAt->format('M d, Y') : 'with permanent spotlight';
        return back()->with('success', "Featured spotlight active for '{$agencyName}' {$expiryText}.");
    }

    public function toggleReviewStatus(Request $request, User $broker, BrokerReview $review)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($review->broker_id !== $broker->id, 404);

        $review->status = $review->status === 'approved' ? 'pending' : 'approved';
        $review->save();

        $broker->recalculateBrokerRating();

        if ($review->status === 'approved') {
            \App\Services\NotificationService::notifyBrokerReviewReceived($broker, $review);
        }

        return back()->with('success', "Review status updated to " . ucfirst($review->status) . ".");
    }

    public function destroyReview(Request $request, User $broker, BrokerReview $review)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);
        abort_if($review->broker_id !== $broker->id, 404);

        $review->delete();

        $broker->recalculateBrokerRating();

        return back()->with('success', 'Review deleted and broker rating updated successfully.');
    }

    public function toggleReviewStatusDirect(Request $request, BrokerReview $review)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);

        $review->status = $review->status === 'approved' ? 'pending' : 'approved';
        $review->save();

        if ($review->broker) {
            $review->broker->recalculateBrokerRating();
            if ($review->status === 'approved') {
                \App\Services\NotificationService::notifyBrokerReviewReceived($review->broker, $review);
            }
        }

        return back()->with('success', "Review status updated to " . ucfirst($review->status) . ".");
    }

    public function destroyReviewDirect(Request $request, BrokerReview $review)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.manage'), 403);

        $broker = $review->broker;
        $review->delete();

        if ($broker) {
            $broker->recalculateBrokerRating();
        }

        return back()->with('success', 'Review deleted and broker rating updated successfully.');
    }


    /**
     * Display a central listing of all client reviews across all brokers.
     */
    public function reviewsIndex(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.view'), 403);

        $query = BrokerReview::with(['broker:id,name,agency_name,avatar', 'user:id,name,email,avatar']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('broker', function ($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%")
                        ->orWhere('agency_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($rating = $request->get('rating')) {
            $query->where('rating', (int) $rating);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $reviews = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => BrokerReview::count(),
            'approved' => BrokerReview::where('status', 'approved')->count(),
            'pending' => BrokerReview::where('status', 'pending')->count(),
            'five_star' => BrokerReview::where('rating', 5)->count(),
            'one_star' => BrokerReview::where('rating', 1)->count(),
        ];

        return view('admin.brokers.reviews', compact('reviews', 'stats'));
    }
}
