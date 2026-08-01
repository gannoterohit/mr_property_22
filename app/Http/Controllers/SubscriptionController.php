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
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'nullable|in:wallet,online',
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
            $expired = $activeSubscription->end_date && $activeSubscription->end_date->endOfDay()->isPast();
            $exhausted = $limit !== -1 && $used >= (int) $limit;
            if ($expired || $exhausted) {
                $activeSubscription->update(['status' => 'expired']);
                $activeSubscription = null;
            }
        }

        if ($activeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription of this type!'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            // Wallet payment
            if ($paymentMethod === 'wallet') {
                if ($user->wallet_balance < $plan->price) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance. Your balance: ₹' . $user->wallet_balance
                    ], 400);
                }
                
                // Deduct from wallet_balance
                $user->decrement('wallet_balance', $plan->price);
                
                // Create subscription - active immediately
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays($plan->duration_days),
                    'status' => 'active'
                ]);

                // Create payment record for wallet usage
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'type' => 'subscription',
                    'amount' => $plan->price,
                    'gateway' => 'wallet',
                    'reference_id' => $subscription->id,
                    'status' => 'completed'
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'subscription_id' => $subscription->id,
                    'wallet_used' => true,
                    'new_balance' => $user->wallet_balance,
                    'message' => 'Subscription activated successfully using wallet balance!'
                ]);
            }

            // Online payment (Razorpay)
            // Create subscription - pending
            $subscription = Subscription::create([
                'user_id' => Auth::id(),
                'plan_id' => $plan->id,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays($plan->duration_days),
                'status' => 'pending'
            ]);

            // Create payment record
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'type' => 'subscription',
                'amount' => $plan->price,
                'gateway' => 'razorpay',
                'reference_id' => $subscription->id,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'subscription_id' => $subscription->id,
                'payment_id' => $payment->id,
                'amount' => $plan->price,
                'message' => 'Please complete payment to activate subscription.'
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
