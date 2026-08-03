<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Room;
use App\Models\RejectionReason;
use App\Http\Resources\RoomResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminRoomController extends BaseApiController
{
    /**
     * List all rooms
     */
    public function index(Request $request)
    {
        $query = Room::with('owner')->latest();
        if ($request->filled('listing_status')) $query->where('listing_status', $request->listing_status);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('city')) $query->where('city', 'like', '%' . $request->city . '%');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")->orWhere('address', 'like', "%$search%");
            });
        }
        return RoomResource::collection($query->paginate($request->get('limit', 15)))->additional(['status' => 'success']);
    }

    /**
     * Approve room
     */
    public function approve($id)
    {
        $room = Room::find($id);
        if (!$room) return $this->sendError('Room not found');
        $room->update(['listing_status' => 'approved']);

        try {
            Mail::to($room->owner->email)->send(new \App\Mail\RoomApprovedMail($room, $room->owner));
            $alerts = \App\Models\CityAlert::with('user')->where('city', $room->city)->get();
            foreach ($alerts as $alert) {
                if ($alert->user && $alert->user->email) {
                    Mail::to($alert->user->email)->send(new \App\Mail\NewRoomInCityAlert($room, $room->city));
                }
            }
        } catch (\Exception $e) { Log::error('Mail fail: ' . $e->getMessage()); }

        \App\Services\NotificationService::notifyRoomApproved($room);

        return $this->sendSuccess(new RoomResource($room), 'Room approved');
    }

    /**
     * Reject room
     */
    public function reject(Request $request, $id)
    {
        $room = Room::find($id);
        if (!$room) return $this->sendError('Room not found');

        $validator = Validator::make($request->all(), [
            'reasons' => 'nullable|array',
            'reasons.*' => 'integer|exists:rejection_reasons,id',
            'custom_reason' => 'nullable|string|max:500',
        ]);
        if ($validator->fails()) return $this->sendError('Please check your input and try again.', $validator->errors(), 422);

        $room->update(['listing_status' => 'rejected']);
        if (!empty($request->reasons)) $room->rejectionReasons()->sync($request->reasons);

        $reasons = RejectionReason::whereIn('id', $request->reasons ?? [])->pluck('reason')->toArray();
        if ($request->custom_reason) $reasons[] = $request->custom_reason;

        try { Mail::to($room->owner->email)->send(new \App\Mail\RoomRejectedMail($room, $room->owner, $reasons)); } 
        catch (\Exception $e) { Log::error('Mail fail: ' . $e->getMessage()); }

        $reasonsText = implode(', ', $reasons);
        \App\Services\NotificationService::notifyRoomRejected($room, $reasonsText);

        return $this->sendSuccess(new RoomResource($room), 'Room rejected');
    }

    /**
     * Delete room
     */
    public function destroy($id)
    {
        $room = Room::find($id);
        if (!$room) return $this->sendError('Room not found');
        $room->delete();
        return $this->sendSuccess([], 'Room deleted');
    }

    /**
     * Rejection reasons CRUD
     */
    public function getReasons() { return $this->sendSuccess(RejectionReason::where('is_active', true)->get()); }

    public function storeReason(Request $request) {
        $validator = Validator::make($request->all(), ['reason' => 'required|string|max:255']);
        if ($validator->fails()) return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        $reason = RejectionReason::create(['reason' => $request->reason, 'is_active' => true]);
        return $this->sendSuccess($reason, 'Reason added', 201);
    }

    public function updateReason(Request $request, $id) {
        $reason = RejectionReason::find($id);
        if (!$reason) return $this->sendError('Reason not found');
        $reason->update(['reason' => $request->reason]);
        return $this->sendSuccess($reason, 'Reason updated');
    }

    public function deleteReason($id) {
        $reason = RejectionReason::find($id);
        if (!$reason) return $this->sendError('Reason not found');
        $reason->update(['is_active' => false]);
        return $this->sendSuccess([], 'Reason deleted');
    }
}
