<?php

namespace App\Http\Controllers;

use App\Models\BrokerListingCredit;
use App\Models\BrokerPayment;
use App\Models\BrokerSubscription;
use App\Models\BrokerTransaction;
use App\Models\BrokerWallet;
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

        $subscription = $broker->brokerSubscription()->first();
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
            'broker', 'stats', 'subscription', 'wallet', 'credits',
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

        return view('broker.properties.index', compact('properties'));
    }

    public function subscription(Request $request)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }

        if (!\App\Models\BrokerSetting::isEnabled('broker_subscription_enabled', true)) {
            abort(404, 'Broker subscriptions are currently disabled.');
        }

        $subscription = $broker->brokerSubscription()->first();
        $plans = \App\Models\BrokerPlan::active()->get();
        $credits = BrokerListingCredit::where('broker_id', $broker->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();

        return view('broker.subscription.index', compact('subscription', 'plans', 'credits'));
    }

    public function purchaseSubscription(Request $request, \App\Models\BrokerPlan $plan)
    {
        $broker = Auth::user();
                if (!$broker->is_broker_active) {
            return redirect()->route('agent.pending');
        }
        abort_if(!\App\Models\BrokerSetting::isEnabled('broker_subscription_enabled', true), 404, 'Broker subscriptions are currently disabled.');
        abort_if(!$plan->is_active, 404, 'This plan is no longer available.');

        $request->validate([
            'payment_method' => 'nullable|in:wallet,razorpay',
        ]);

        $paymentMethod = $request->payment_method ?? 'razorpay';

        if ($paymentMethod === 'wallet') {
            $wallet = $broker->brokerWallet;
            if (!$wallet || $wallet->balance < $plan->price) {
                return back()->with('error', 'Insufficient wallet balance.');
            }

            DB::beginTransaction();
            try {
                $wallet->decrement('balance', $plan->price);
                $wallet->increment('total_withdrawn', $plan->price);

                $startsAt = now();
                $expiresAt = $plan->duration_days ? now()->addDays($plan->duration_days) : null;

                $subscription = BrokerSubscription::create([
                    'broker_id' => $broker->id,
                    'plan_id' => $plan->id,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'max_listings' => $plan->max_listings ?? 0,
                    'listings_used' => 0,
                    'amount_paid' => $plan->price,
                    'payment_id' => null,
                ]);

                $broker->update([
                    'broker_subscription_expires_at' => $expiresAt,
                    'broker_subscription_listings_limit' => $plan->max_listings ?? 0,
                    'broker_subscription_listings_used' => 0,
                ]);

                BrokerPayment::create([
                    'broker_id' => $broker->id,
                    'payment_type' => 'subscription',
                    'amount' => $plan->price,
                    'status' => 'completed',
                    'method' => 'wallet',
                    'plan_id' => $plan->id,
                ]);

                BrokerTransaction::create([
                    'broker_id' => $broker->id,
                    'type' => 'debit',
                    'category' => 'subscription',
                    'amount' => $plan->price,
                    'description' => "Subscription purchased: {$plan->name}",
                ]);

                DB::commit();

                return redirect()->route('agent.subscription')->with('success', 'Subscription activated successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                report($e);
                return back()->with('error', 'Unable to purchase subscription. Please try again.');
            }
        }

        // Razorpay flow
        $payment = BrokerPayment::create([
            'broker_id' => $broker->id,
            'payment_type' => 'subscription',
            'amount' => $plan->price,
            'status' => 'pending',
            'method' => 'razorpay',
            'plan_id' => $plan->id,
        ]);

        // TODO: Integrate with Razorpay API to create order
        // For now, return pending payment info
        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'amount' => $plan->price,
            'message' => 'Please complete payment to activate subscription',
        ]);
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
