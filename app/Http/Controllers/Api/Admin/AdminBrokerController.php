<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Models\Room;
use Illuminate\Http\Request;

class AdminBrokerController extends BaseApiController
{
    public function index(Request $request)
    {
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
            $query->where('is_featured_agency', $request->boolean('featured'));
        }

        if ($request->filled('is_broker_active')) {
            $query->where('is_broker_active', $request->boolean('is_broker_active'));
        }

        $brokers = $query->withCount('brokerRooms')->latest()->paginate(max(1, min(50, $request->integer('limit', 15))));

        $stats = [
            'total'     => User::where('role', 'broker')->count(),
            'pending'   => User::where('role', 'broker')->where('broker_verification_status', 'pending')->count(),
            'approved'  => User::where('role', 'broker')->where('broker_verification_status', 'approved')->count(),
            'suspended' => User::where('role', 'broker')->where('broker_verification_status', 'suspended')->count(),
            'active'    => User::where('role', 'broker')->where('is_broker_active', true)->count(),
        ];

        return $this->sendSuccess([
            'brokers' => $brokers,
            'stats'   => $stats,
        ]);
    }

    public function show($id)
    {
        $broker = User::where('role', 'broker')
            ->with(['brokerRooms' => fn ($q) => $q->latest()->limit(10)])
            ->find($id);

        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $stats = [
            'total_listings'    => Room::where('broker_id', $broker->id)->count(),
            'active_listings'   => Room::where('broker_id', $broker->id)->where('status', 'active')->count(),
            'pending_listings'  => Room::where('broker_id', $broker->id)->where('listing_status', 'pending')->count(),
        ];

        return $this->sendSuccess([
            'broker' => $broker,
            'stats'  => $stats,
        ]);
    }

    public function approve($id)
    {
        $broker = User::where('role', 'broker')->find($id);
        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $broker->update([
            'broker_verification_status' => 'approved',
            'is_broker_active'           => true,
            'broker_verified_at'         => now(),
            'broker_rejection_reason'    => null,
        ]);

        return $this->sendSuccess($broker, 'Broker approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $broker = User::where('role', 'broker')->find($id);
        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $broker->update([
            'broker_verification_status' => 'rejected',
            'is_broker_active'           => false,
            'broker_rejection_reason'    => $request->input('reason', 'Application rejected by administration.'),
        ]);

        return $this->sendSuccess($broker, 'Broker rejected successfully.');
    }

    public function suspend(Request $request, $id)
    {
        $broker = User::where('role', 'broker')->find($id);
        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $broker->update([
            'broker_verification_status' => 'suspended',
            'is_broker_active'           => false,
            'broker_rejection_reason'    => $request->input('reason', 'Account suspended by administration.'),
        ]);

        return $this->sendSuccess($broker, 'Broker suspended successfully.');
    }

    public function activate($id)
    {
        $broker = User::where('role', 'broker')->find($id);
        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $broker->update([
            'broker_verification_status' => 'approved',
            'is_broker_active'           => true,
            'broker_rejection_reason'    => null,
        ]);

        return $this->sendSuccess($broker, 'Broker activated successfully.');
    }

    public function toggleFeatured(Request $request, $id)
    {
        $broker = User::where('role', 'broker')->find($id);
        if (!$broker) {
            return $this->sendError('Broker not found', [], 404);
        }

        $isFeatured = !$broker->is_featured_agency;
        $broker->update([
            'is_featured_agency' => $isFeatured,
            'featured_until'     => $isFeatured ? now()->addDays((int) $request->input('days', 30)) : null,
        ]);

        return $this->sendSuccess($broker, 'Broker featured status updated.');
    }
}
