<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiCouponController extends BaseApiController
{
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:30',
            'amount'  => 'required|numeric|min:1',
            'context' => 'required|string|in:owner_plans,user_plans,broker_plans,unlocks,all',
        ]);

        $offer = Offer::where('code', strtoupper(trim($validated['code'])))->first();

        if (!$offer) {
            return $this->sendError('Invalid coupon code. Please check and try again.', [], 422);
        }

        $userId = Auth::id();
        $amount = (float) $validated['amount'];
        $context = $validated['context'];

        $check = $offer->canBeUsedBy($userId, $context, $amount);

        if (!$check['valid']) {
            return $this->sendError($check['message'], [], 422);
        }

        $discount     = $offer->calculateDiscount($amount);
        $finalAmount  = max(0, $amount - $discount);

        return $this->sendSuccess([
            'coupon_id'       => $offer->id,
            'code'            => $offer->code,
            'discount_label'  => $offer->discount_label,
            'original_amount' => $amount,
            'discount_amount' => $discount,
            'final_amount'    => $finalAmount,
        ], 'Coupon applied! You save ₹' . number_format($discount, 0));
    }

    public function remove()
    {
        return $this->sendSuccess([], 'Coupon removed');
    }
}
