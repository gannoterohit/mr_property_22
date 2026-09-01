<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokerPayment;
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

        return view('admin.brokers.show', compact('broker', 'properties', 'payments', 'subscriptions'));
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
}
