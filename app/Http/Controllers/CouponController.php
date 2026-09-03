<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Validate a coupon code and return discount info.
     * Called via AJAX from checkout modals.
     *
     * POST /coupon/apply
     * Body: { code, amount, context }
     * context: owner_plans | user_plans | broker_plans | unlocks
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:30',
            'amount'  => 'required|numeric|min:1',
            'context' => 'required|string|in:owner_plans,user_plans,broker_plans,unlocks,all',
        ]);

        $offer = Offer::where('code', strtoupper(trim($validated['code'])))->first();

        if (!$offer) {
            return response()->json([
                'valid'   => false,
                'message' => 'Invalid coupon code. Please check and try again.',
            ], 422);
        }

        $userId = Auth::id();
        $amount = (float) $validated['amount'];
        $context = $validated['context'];

        $check = $offer->canBeUsedBy($userId, $context, $amount);

        if (!$check['valid']) {
            return response()->json([
                'valid'   => false,
                'message' => $check['message'],
            ], 422);
        }

        $discount     = $offer->calculateDiscount($amount);
        $finalAmount  = max(0, $amount - $discount);

        return response()->json([
            'valid'          => true,
            'coupon_id'      => $offer->id,
            'code'           => $offer->code,
            'discount_label' => $offer->discount_label,
            'original_amount'=> $amount,
            'discount_amount'=> $discount,
            'final_amount'   => $finalAmount,
            'message'        => '🎉 Coupon applied! You save ₹' . number_format($discount, 0),
        ]);
    }

    /**
     * Remove a coupon (clear from session/UI — no DB action needed until payment confirmed)
     */
    public function remove()
    {
        return response()->json(['success' => true]);
    }
}
