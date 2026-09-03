<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Offer;
use App\Http\Resources\PlanResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiSubscriptionController extends BaseApiController
{
    /**
     * List all available plans (role-filtered)
     */
    public function plans()
    {
        $user = Auth::user();
        $plans = Plan::where('is_active', true)
            ->where('type', $user->role)
            ->get();

        $activeSubscription = \App\Models\Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('plan', fn ($q) => $q->where('type', $user->role))
            ->with('plan')
            ->first();

        $applicableCoupons = Offer::where('is_active', true)
            ->where(function ($q) use ($user) {
                $q->where('applicable_for', 'all')
                  ->orWhere('applicable_for', match ($user->role) {
                      'owner' => 'owner_plans',
                      'broker' => 'broker_plans',
                      default => 'user_plans',
                  });
            })
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', today());
            })
            ->get();

        return $this->sendSuccess([
            'plans' => PlanResource::collection($plans),
            'active_subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan_name' => $activeSubscription->plan->name,
                'end_date' => $activeSubscription->end_date?->toDateString(),
            ] : null,
            'applicable_coupons' => $applicableCoupons->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'label' => $c->discount_label,
                'applicable_for' => $c->applicable_for,
            ]),
        ]);
    }

    /**
     * Purchase a subscription
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'payment_method' => 'nullable|in:wallet,online',
            'coupon_code'    => 'nullable|string|max:30',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $user = Auth::user();
        if (!$plan->is_active || $user->role !== $plan->type) {
            return $this->sendError('This plan is not available for your account type', [], 403);
        }

        $activeSub = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('plan', fn ($q) => $q->where('type', $plan->type))
            ->first();

        if ($activeSub) {
            $usageType = $plan->type === 'owner' ? 'listing' : 'contact';
            $limit = $plan->type === 'owner' ? $activeSub->plan->listing_limit : $activeSub->plan->contacts_limit;
            $exhausted = $limit !== -1 && $activeSub->usages()->where('usage_type', $usageType)->count() >= (int) $limit;
            if ($exhausted) {
                $activeSub->update(['status' => 'expired', 'end_date' => null]);
                $activeSub = null;
            }
        }
        if ($activeSub) {
            return $this->sendError('You already have an active ' . $plan->type . ' subscription', [], 400);
        }

        $appliedOffer = null;
        $originalPrice = (float) $plan->price;
        $discountAmount = 0.0;
        $finalPrice = $originalPrice;

        if ($request->filled('coupon_code')) {
            $code = strtoupper(trim($request->input('coupon_code')));
            $offer = Offer::where('code', $code)->first();
            if (!$offer) {
                return $this->sendError('Invalid coupon code.', [], 422);
            }
            $context = match ($plan->type) {
                'owner' => 'owner_plans',
                'broker' => 'broker_plans',
                default => 'user_plans',
            };
            $check = $offer->canBeUsedBy($user->id, $context, $originalPrice);
            if (!$check['valid']) {
                return $this->sendError($check['message'], [], 422);
            }
            $appliedOffer = $offer;
            $discountAmount = $offer->calculateDiscount($originalPrice);
            $finalPrice = max(0, $originalPrice - $discountAmount);
        }

        $paymentMethod = $request->input('payment_method', 'online');

        DB::beginTransaction();
        try {
            $user = Auth::user()->newQuery()->whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            $activeSub = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereHas('plan', fn ($q) => $q->where('type', $plan->type))
                ->with('plan')->lockForUpdate()->first();
            if ($activeSub) {
                DB::rollBack();
                return $this->sendError('You already have an active ' . $plan->type . ' subscription', [], 400);
            }

            if ($finalPrice <= 0 && $appliedOffer) {
                $subscription = Subscription::create([
                    'user_id'    => $user->id,
                    'plan_id'    => $plan->id,
                    'start_date' => now(),
                    'end_date'   => null,
                    'status'     => 'active',
                ]);

                Payment::create([
                    'user_id'      => $user->id,
                    'type'         => 'subscription',
                    'amount'       => 0,
                    'gateway'      => 'coupon_100_percent',
                    'reference_id' => $subscription->id,
                    'status'       => 'completed',
                ]);

                $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);

                DB::commit();

                return $this->sendSuccess([
                    'subscription_id' => $subscription->id,
                    'free_activated'  => true,
                ], '100% Discount applied! Subscription activated immediately!');
            }

            if ($paymentMethod === 'wallet') {
                if ($user->wallet_balance < $finalPrice) {
                    DB::rollBack();
                    return $this->sendError('Insufficient wallet balance. Payable amount: ₹' . $finalPrice . ', Balance: ₹' . $user->wallet_balance, [], 400);
                }

                $user->decrement('wallet_balance', $finalPrice);
                $user->refresh();

                $subscription = Subscription::create([
                    'user_id'    => $user->id,
                    'plan_id'    => $plan->id,
                    'start_date' => now(),
                    'end_date'   => null,
                    'status'     => 'active',
                ]);

                Payment::create([
                    'user_id'      => $user->id,
                    'type'         => 'subscription',
                    'amount'       => $finalPrice,
                    'gateway'      => 'wallet',
                    'reference_id' => $subscription->id,
                    'status'       => 'completed',
                ]);

                if ($appliedOffer) {
                    $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);
                }

                DB::commit();

                return $this->sendSuccess([
                    'new_balance'     => (float) $user->wallet_balance,
                    'subscription_id' => $subscription->id,
                ], 'Subscription activated successfully using wallet balance');
            }

            $subscription = Subscription::create([
                'user_id'    => $user->id,
                'plan_id'    => $plan->id,
                'start_date' => now(),
                'end_date'   => null,
                'status'     => 'pending',
            ]);

            $payment = Payment::create([
                'user_id'      => $user->id,
                'type'         => 'subscription',
                'amount'       => $finalPrice,
                'gateway'      => 'razorpay',
                'reference_id' => $subscription->id,
                'status'       => 'pending',
            ]);

            if ($appliedOffer) {
                $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);
            }

            DB::commit();

            return $this->sendSuccess([
                'subscription_id'    => $subscription->id,
                'payment_record_id'  => $payment->id,
                'amount'             => $finalPrice,
                'original_amount'    => $originalPrice,
                'discount_amount'    => $discountAmount,
                'type'               => 'subscription',
            ], 'Payment required to activate subscription');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($this->safeErrorMessage($e, 'Unable to complete subscription. Please try again.'), [], 500);
        }
    }
}
