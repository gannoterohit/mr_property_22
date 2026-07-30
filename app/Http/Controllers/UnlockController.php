<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SubscriptionUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnlockController extends Controller
{
    public function index()
    {
        $unlocks = Enquiry::where('user_id', Auth::id())
            ->where('unlocked', true)
            ->whereNotNull('unlocked_at')
            ->whereHas('room.owner', function ($query) {
                $query->where(function ($contactQuery) {
                    $contactQuery->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->orWhere(function ($emailQuery) {
                            $emailQuery->whereNotNull('email')
                                ->where('email', '!=', '');
                        });
                });
            })
            ->with(['room.owner', 'payment'])
            ->latest('unlocked_at')
            ->paginate(12);

        return view('user.unlocked-contacts', compact('unlocks'));
    }

    public function unlock(Request $request, Room $room)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to unlock contact details'
            ], 401);
        }

        if ($room->status !== 'active' || $room->listing_status !== 'approved' || !$room->listing_fee_paid) {
            return response()->json(['success' => false, 'message' => 'This room is not available for contact unlock.'], 422);
        }

        // Check if user is owner - owner can see their own room contact
        if (Auth::id() === $room->user_id && Auth::user()->role === 'owner') {
            return response()->json([
                'success' => true,
                'already_unlocked' => true,
                'is_owner' => true,
                'contact' => $room->owner->phone ?? $room->owner->email
            ]);
        }

        // Check if already unlocked
        $existingEnquiry = Enquiry::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->where('unlocked', true)
            ->first();

        if ($existingEnquiry) {
            return response()->json([
                'success' => true,
                'already_unlocked' => true,
                'contact' => $room->owner->phone ?? $room->owner->email
            ]);
        }

        DB::beginTransaction();
        try {
            // Free-launch mode must not consume a plan, referral credit or wallet.
            $unlockFeeEnabled = filter_var(Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
            if (!$unlockFeeEnabled) {
                $payment = Payment::create([
                    'user_id' => Auth::id(),
                    'type' => 'unlock',
                    'amount' => 0,
                    'gateway' => 'free',
                    'reference_id' => $room->id,
                    'status' => 'completed',
                ]);
                Enquiry::updateOrCreate(
                    ['user_id' => Auth::id(), 'room_id' => $room->id],
                    ['payment_id' => $payment->id, 'unlocked' => true, 'unlocked_at' => now()]
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'already_unlocked' => true,
                    'free_unlock' => true,
                    'contact' => $room->owner->phone ?? $room->owner->email,
                ]);
            }

            // Check subscription first - count based, not date based
            $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($query) => $query->where('type', 'user')->where('is_active', true))
                ->lockForUpdate()
                ->with('plan')
                ->first();
            
            if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                // Check subscription usage - count all unlocked contacts (lifetime, count-based)
                $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                
                $totalContacts = $activeSubscription->plan->contacts_limit ?? 0;
                
                // Check for Unlimited Plan (-1)
                $remaining = 0;
                if ($totalContacts === -1) {
                    $remaining = 9999; // Effectively unlimited
                } else {
                    $remaining = max(0, $totalContacts - $usedContacts);
                }
                
                if ($remaining > 0) {
                    // Unlock using subscription (no payment needed)
                    $enquiry = Enquiry::updateOrCreate(
                        ['user_id' => Auth::id(), 'room_id' => $room->id],
                        ['payment_id' => null, 'unlocked' => true, 'unlocked_at' => now()]
                    );
                    SubscriptionUsage::firstOrCreate(
                        ['subscription_id' => $activeSubscription->id, 'usage_type' => 'contact', 'room_id' => $room->id],
                        ['user_id' => Auth::id(), 'used_at' => now()]
                    );

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'already_unlocked' => true,
                        'subscription_used' => true,
                        'remaining_contacts' => $remaining - 1,
                        'contact' => $room->owner->phone ?? $room->owner->email
                    ]);
                }
            }

            // Check for Free Referral Unlocks Credit
            $user = Auth::user();
            if ($user && $user->free_unlocks > 0) {
                $user->decrement('free_unlocks', 1);
                
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'type' => 'unlock',
                    'amount' => 0,
                    'gateway' => 'free_credit',
                    'reference_id' => $room->id,
                    'status' => 'completed'
                ]);
                
                Enquiry::create([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'payment_id' => $payment->id,
                    'unlocked' => true,
                    'unlocked_at' => now()
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'already_unlocked' => true,
                    'free_credit_used' => true,
                    'remaining_credits' => $user->free_unlocks,
                    'contact' => $room->owner->phone ?? $room->owner->email
                ]);
            }

            // No subscription or subscription exhausted - charge single unlock fee
            $unlockFee = Setting::get('unlock_fee', 49);
            
            // Check if payment method is wallet
            if ($request->payment_method === 'wallet') {
                // Check if user has enough balance in wallet
                $user = Auth::user();
                if ($user->wallet_balance >= $unlockFee) {
                    // Deduct from wallet
                    $user->decrement('wallet_balance', $unlockFee);
                    
                    // Create payment record for wallet usage
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => 'unlock',
                        'amount' => $unlockFee,
                        'gateway' => 'wallet',
                        'reference_id' => $room->id,
                        'status' => 'completed'
                    ]);

                    // Create enquiry record
                    $enquiry = Enquiry::create([
                        'user_id' => $user->id,
                        'room_id' => $room->id,
                        'payment_id' => $payment->id,
                        'unlocked' => true,
                        'unlocked_at' => now()
                    ]);

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'already_unlocked' => true,
                        'wallet_used' => true,
                        'new_balance' => $user->wallet_balance,
                        'contact' => $room->owner->phone ?? $room->owner->email
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance'
                    ], 400);
                }
            }

            if ($unlockFee <= 0) {
                 // Zero fee - unlock for free
                 // Create dummy payment record for tracking
                 $payment = Payment::create([
                     'user_id' => Auth::id(),
                     'type' => 'unlock',
                     'amount' => 0,
                     'gateway' => 'free',
                     'reference_id' => $room->id,
                     'status' => 'completed'
                 ]);
 
                 // Create enquiry record
                 $enquiry = Enquiry::create([
                     'user_id' => Auth::id(),
                     'room_id' => $room->id,
                     'payment_id' => $payment->id,
                     'unlocked' => true,
                     'unlocked_at' => now()
                 ]);
 
                 DB::commit();
 
                 return response()->json([
                     'success' => true,
                     'already_unlocked' => true, // Frontend handles this by showing success message
                     'contact' => $room->owner->phone ?? $room->owner->email
                 ]);
            }
            
            // Create payment record
            $payment = Payment::create([
                'user_id' => Auth::id(),
                'type' => 'unlock',
                'amount' => $unlockFee,
                'gateway' => 'razorpay',
                'reference_id' => $room->id,
                'status' => 'pending'
            ]);

            // Create enquiry record
            $enquiry = Enquiry::updateOrCreate(
                ['user_id' => Auth::id(), 'room_id' => $room->id],
                ['payment_id' => $payment->id, 'unlocked' => false]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'enquiry_id' => $enquiry->id,
                'payment_id' => $payment->id,
                'amount' => $unlockFee,
                'message' => 'Please complete payment to unlock contact details'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock: ' . $e->getMessage()
            ], 500);
        }
    }
}
