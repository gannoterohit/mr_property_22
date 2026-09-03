<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'payment_method' => 'nullable|in:wallet,online',
            'coupon_code'    => 'nullable|string|max:30',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        abort_unless($plan->is_active, 422, 'This plan is not available.');
        abort_unless(Auth::user()->role === $plan->type, 403, 'This plan is not available for your account type.');
        $paymentMethod = $request->input('payment_method', 'online');
        
        // Check if user already has active subscription of same type
        $activeSubscription = Subscription::where('user_id', Auth::id())
            ->where('status', 'active')
            ->whereHas('plan', function($query) use ($plan) {
                $query->where('type', $plan->type);
            })
            ->first();

        if ($activeSubscription) {
            $limit = $plan->type === 'owner' ? $activeSubscription->plan->listing_limit : $activeSubscription->plan->contacts_limit;
            $usageType = $plan->type === 'owner' ? 'listing' : 'contact';
            $used = $activeSubscription->usages()->where('usage_type', $usageType)->count();
            $exhausted = $limit !== -1 && $used >= (int) $limit;
            if ($exhausted) {
                $activeSubscription->update(['status' => 'expired', 'end_date' => null]);
                $activeSubscription = null;
            }
        }

        if ($activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription of this type!'
            ], 400);
        }

        // Apply coupon if provided
        $appliedOffer = null;
        $originalPrice = (float) $plan->price;
        $discountAmount = 0.0;
        $finalPrice = $originalPrice;

        if ($request->filled('coupon_code')) {
            $code = strtoupper(trim($request->input('coupon_code')));
            $offer = \App\Models\Offer::where('code', $code)->first();
            if (!$offer) {
                return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
            }
            $context = match($plan->type) {
                'owner'  => 'owner_plans',
                'broker' => 'broker_plans',
                default  => 'user_plans',
            };
            $check = $offer->canBeUsedBy(Auth::id(), $context, $originalPrice);
            if (!$check['valid']) {
                return response()->json(['success' => false, 'message' => $check['message']], 422);
            }
            $appliedOffer = $offer;
            $discountAmount = $offer->calculateDiscount($originalPrice);
            $finalPrice = max(0, $originalPrice - $discountAmount);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user()->newQuery()->whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            $activeSubscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->whereHas('plan', fn ($query) => $query->where('type', $plan->type))
                ->with('plan')->lockForUpdate()->first();
            if ($activeSubscription) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'You already have an active subscription of this type!'], 400);
            }
            
            // 100% Free coupon scenario (finalPrice == 0)
            if ($finalPrice <= 0 && $appliedOffer) {
                $subscription = Subscription::create([
                    'user_id'    => $user->id,
                    'plan_id'    => $plan->id,
                    'start_date' => Carbon::now(),
                    'end_date'   => null,
                    'status'     => 'active',
                ]);

                $payment = Payment::create([
                    'user_id'      => $user->id,
                    'type'         => 'subscription',
                    'amount'       => 0,
                    'gateway'      => 'coupon_100_percent',
                    'reference_id' => $subscription->id,
                    'status'       => 'completed'
                ]);

                $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);

                DB::commit();

                return response()->json([
                    'success'         => true,
                    'free_activated'  => true,
                    'subscription_id' => $subscription->id,
                    'message'         => '🎉 100% Discount applied! Subscription activated immediately!'
                ]);
            }

            // Wallet payment
            if ($paymentMethod === 'wallet') {
                if ($user->wallet_balance < $finalPrice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance. Payable amount: ₹' . $finalPrice . ', Balance: ₹' . $user->wallet_balance
                    ], 400);
                }
                
                // Deduct from wallet_balance
                $user->decrement('wallet_balance', $finalPrice);
                $user->refresh();
                
                // Create subscription - active immediately
                $subscription = Subscription::create([
                    'user_id'    => $user->id,
                    'plan_id'    => $plan->id,
                    'start_date' => Carbon::now(),
                    'end_date'   => null,
                    'status'     => 'active',
                ]);

                // Create payment record for wallet usage
                $payment = Payment::create([
                    'user_id'      => $user->id,
                    'type'         => 'subscription',
                    'amount'       => $finalPrice,
                    'gateway'      => 'wallet',
                    'reference_id' => $subscription->id,
                    'status'       => 'completed'
                ]);

                if ($appliedOffer) {
                    $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);
                }

                DB::commit();

                return response()->json([
                    'success'         => true,
                    'subscription_id' => $subscription->id,
                    'wallet_used'     => true,
                    'new_balance'     => $user->wallet_balance,
                    'message'         => 'Subscription activated successfully using wallet balance!'
                ]);
            }

            // Online payment (Razorpay)
            // Create subscription - pending
            $subscription = Subscription::create([
                'user_id'    => Auth::id(),
                'plan_id'    => $plan->id,
                'start_date' => Carbon::now(),
                'end_date'   => null,
                'status'     => 'pending'
            ]);

            // Create payment record with discounted amount
            $payment = Payment::create([
                'user_id'      => Auth::id(),
                'type'         => 'subscription',
                'amount'       => $finalPrice,
                'gateway'      => 'razorpay',
                'reference_id' => $subscription->id,
                'status'       => 'pending'
            ]);

            // If coupon applied, record pending usage or record right now
            if ($appliedOffer) {
                $appliedOffer->recordUsage($user->id, 'subscription', $subscription->id, $originalPrice, $discountAmount);
            }

            DB::commit();

            return response()->json([
                'success'         => true,
                'subscription_id' => $subscription->id,
                'payment_id'      => $payment->id,
                'amount'          => $finalPrice,
                'original_amount' => $originalPrice,
                'discount_amount' => $discountAmount,
                'message'         => 'Please complete payment to activate subscription.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Unable to start the subscription. Please try again.'
            ], 500);
        }
    }
}
