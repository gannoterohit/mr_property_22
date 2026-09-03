<?php

namespace App\Http\Controllers;

use App\Models\BrokerListingCredit;
use App\Models\BrokerPayment;
use App\Models\BrokerTransaction;
use App\Models\BrokerWallet;
use App\Models\Enquiry;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrokerDashboardController extends Controller
{
    public function dashboard()
    {
        $broker = Auth::user();
        abort_if($broker->role !== 'broker', 403);

        if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        $stats = [
            'total_properties' => Room::where('broker_id', $broker->id)->count(),
            'active_properties' => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending_properties' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'expired_properties' => Room::where('broker_id', $broker->id)->where('expires_at', '<', now())->count(),
            'featured_properties' => Room::where('broker_id', $broker->id)->where('is_featured', true)->count(),
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

        return view('broker.dashboard', compact(
            'broker', 'stats', 'wallet', 'credits',
            'recentProperties', 'recentTransactions', 'recentPayments'
        ));
    }

    public function pending()
    {
        $broker = Auth::user();
        abort_if($broker->role !== 'broker', 403);

        return view('broker.pending', compact('broker'));
    }

    public function properties(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        $query = Room::where('broker_id', $broker->id);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($listingStatus = $request->get('listing_status')) {
            $query->where('listing_status', $listingStatus);
        }

        $properties = $query->latest()->paginate(20);

        $roomCounts = [
            'all' => Room::where('broker_id', $broker->id)->count(),
            'active' => Room::where('broker_id', $broker->id)->where('status', 'active')->where('listing_status', 'approved')->count(),
            'pending' => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
            'booked' => Room::where('broker_id', $broker->id)->where('status', 'booked')->count(),
        ];

        return view('broker.properties.index', compact('properties', 'roomCounts'));
    }

    public function enquiries(Request $request)
    {
        $broker = Auth::user();
        abort_if(!$broker->is_broker_active, 403);

        $query = Enquiry::whereHas('room', function ($q) use ($broker) {
            $q->where('broker_id', $broker->id);
        })->with(['user', 'room', 'payment']);

        if ($request->filled('status')) {
            $query->where('unlocked', $request->boolean('status'));
        }

        $enquiries = $query->latest()->paginate(20);

        return view('broker.enquiries.index', compact('enquiries'));
    }

    public function payments(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        $payments = BrokerPayment::where('broker_id', $broker->id)->latest()->paginate(20);

        return view('broker.payments.index', compact('payments'));
    }

    public function transactions(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        $transactions = BrokerTransaction::where('broker_id', $broker->id)->latest()->paginate(20);

        return view('broker.transactions.index', compact('transactions'));
    }

    public function profile(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        return view('broker.profile.show', compact('broker'));
    }

    public function updateProfile(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'agency_name' => 'nullable|string|max:255',
            'agency_address' => 'nullable|string|max:500',
            'agency_gst' => 'nullable|string|max:50',
            'broker_license' => 'nullable|string|max:100',
        ]);

        $broker->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
