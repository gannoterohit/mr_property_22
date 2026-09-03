<?php

namespace App\Http\Controllers\Api;

use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ApiAccountController extends BaseApiController
{
    public function payments(Request $request)
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($this->limit($request));

        return $this->sendSuccess($payments, 'Payment history fetched successfully');
    }

    public function subscriptions(Request $request)
    {
        $subscriptions = Subscription::where('user_id', $request->user()->id)
            ->with(['plan', 'usages'])
            ->latest()
            ->paginate($this->limit($request));

        return $this->sendSuccess($subscriptions, 'Subscription history fetched successfully');
    }

    public function unlocks(Request $request)
    {
        $unlocks = Enquiry::where('user_id', $request->user()->id)
            ->where('unlocked', true)
            ->with(['room.owner', 'payment'])
            ->latest('unlocked_at')
            ->paginate($this->limit($request));

        return $this->sendSuccess($unlocks, 'Contact unlock history fetched successfully');
    }

    private function limit(Request $request): int
    {
        return max(1, min(50, $request->integer('limit', 15)));
    }
}
