<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\BrokerListingCredit;
use App\Models\BrokerPayment;
use App\Models\BrokerTransaction;
use App\Models\Enquiry;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiBrokerController extends BaseApiController
{
    public function dashboard()
    {
        $broker = Auth::user();

        if (!$broker->is_broker_active) {
            return $this->sendSuccess([
                'pending_approval' => true,
                'message' => 'Your broker account is pending approval.',
            ], 'Pending approval');
        }

        $stats = [
            'total_properties'   => Room::where('broker_id', $broker->id)->count(),
            'active_properties'  => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending_properties' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'expired_properties' => Room::where('broker_id', $broker->id)->where('expires_at', '<', now())->count(),
            'featured_properties'=> Room::where('broker_id', $broker->id)->where('is_featured', true)->count(),
        ];

        $wallet = $broker->brokerWallet;
        $credits = BrokerListingCredit::where('broker_id', $broker->id)
            ->where('credits_remaining', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        $recentProperties = Room::where('broker_id', $broker->id)->latest()->limit(5)->get();
        $recentTransactions = BrokerTransaction::where('broker_id', $broker->id)->latest()->limit(5)->get();
        $recentPayments = BrokerPayment::where('broker_id', $broker->id)->latest()->limit(5)->get();

        return $this->sendSuccess([
            'stats'              => $stats,
            'wallet'             => $wallet,
            'credits'            => $credits,
            'recent_properties'  => $recentProperties,
            'recent_transactions'=> $recentTransactions,
            'recent_payments'    => $recentPayments,
        ]);
    }

    public function pending()
    {
        return $this->sendSuccess([
            'pending_approval' => ! Auth::user()->is_broker_active,
            'broker' => Auth::user(),
        ], 'Broker approval status fetched successfully.');
    }

    public function properties(Request $request)
    {
        $broker = Auth::user();
        $query = Room::where('broker_id', $broker->id);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($listingStatus = $request->get('listing_status')) {
            $query->where('listing_status', $listingStatus);
        }

        $properties = $query->latest()->paginate(max(1, min(50, $request->integer('limit', 20))));

        $roomCounts = [
            'all'     => Room::where('broker_id', $broker->id)->count(),
            'active'  => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'booked'  => Room::where('broker_id', $broker->id)->where('status', 'booked')->count(),
        ];

        return $this->sendSuccess([
            'properties' => $properties,
            'counts'     => $roomCounts,
        ]);
    }

    public function enquiries(Request $request)
    {
        $broker = Auth::user();
        $query = Enquiry::whereHas('room', function ($q) use ($broker) {
            $q->where('broker_id', $broker->id);
        })->with(['user', 'room', 'payment']);

        if ($request->filled('status')) {
            $query->where('unlocked', $request->boolean('status'));
        }

        $enquiries = $query->latest()->paginate(max(1, min(50, $request->integer('limit', 20))));

        return $this->sendSuccess($enquiries);
    }

    public function payments(Request $request)
    {
        $payments = BrokerPayment::where('broker_id', Auth::id())
            ->latest()
            ->paginate($request->get('limit', 20));

        return $this->sendSuccess($payments);
    }

    public function transactions(Request $request)
    {
        $transactions = BrokerTransaction::where('broker_id', Auth::id())
            ->latest()
            ->paginate($request->get('limit', 20));

        return $this->sendSuccess($transactions);
    }

    public function profile()
    {
        $broker = Auth::user();
        return $this->sendSuccess($broker);
    }

    public function updateProfile(Request $request)
    {
        $broker = Auth::user();

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone'           => 'nullable|string|max:20',
            'agency_name'     => 'nullable|string|max:255',
            'agency_address'  => 'nullable|string|max:500',
            'agency_gst'      => 'nullable|string|max:50',
            'broker_license'  => 'nullable|string|max:100',
        ]);

        $broker->update($data);

        return $this->sendSuccess($broker, 'Profile updated successfully.');
    }
}
