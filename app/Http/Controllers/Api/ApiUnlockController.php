<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Room;
use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SubscriptionUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiUnlockController extends BaseApiController
{
    /**
     * Unlock room contact details
     */
    public function unlock(Request $request, $id)
    {
        $room = Room::with('owner')->find($id);
        
        if (!$room) {
            return $this->sendError('Room not found');
        }
        if ($room->status !== 'active' || $room->listing_status !== 'approved' || !$room->listing_fee_paid) {
            return $this->sendError('This room is not available for contact unlock', [], 422);
        }

        // Check if user is owner
        if (Auth::id() === $room->user_id) {
            return $this->sendSuccess([
                'contact' => $room->owner->phone ?? $room->owner->email
            ], 'You are the owner of this room');
        }

        // Check if already unlocked
        $existingEnquiry = Enquiry::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->where('unlocked', true)
            ->first();

        if ($existingEnquiry) {
            return $this->sendSuccess([
                'contact' => $room->owner->phone ?? $room->owner->email
            ], 'Already unlocked');
        }

        DB::beginTransaction();
        try {
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
                \App\Services\NotificationService::notifyContactUnlocked(Auth::user(), $room);

                return $this->sendSuccess([
                    'contact' => $room->owner->phone ?? $room->owner->email,
                    'free_unlock' => true,
                ], 'Unlocked for free');
            }

            // 1. Check Subscription
            $activeSubscription = \App\Models\Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->whereDate('end_date', '>=', today())
                ->whereHas('plan', fn ($q) => $q->where('type', 'user')->where('is_active', true))
                ->lockForUpdate()
                ->with('plan')
                ->first();
            
            if ($activeSubscription && $activeSubscription->plan && $activeSubscription->plan->type === 'user') {
                $usedContacts = $activeSubscription->usages()->where('usage_type', 'contact')->count();
                
                $totalContacts = $activeSubscription->plan->contacts_limit ?? 0;
                $remaining = ($totalContacts === -1) ? 9999 : max(0, $totalContacts - $usedContacts);
                
                if ($remaining > 0) {
                    Enquiry::updateOrCreate(['user_id' => Auth::id(), 'room_id' => $room->id], ['unlocked' => true, 'unlocked_at' => now()]);
                    SubscriptionUsage::firstOrCreate(
                        ['subscription_id' => $activeSubscription->id, 'usage_type' => 'contact', 'room_id' => $room->id],
                        ['user_id' => Auth::id(), 'used_at' => now()]
                    );

                    DB::commit();
                    \App\Services\NotificationService::notifyContactUnlocked(Auth::user(), $room);

                    return $this->sendSuccess([
                        'contact' => $room->owner->phone ?? $room->owner->email
                    ], 'Unlocked via subscription');
                }
            }

            // 2. Check Wallet
            $unlockFee = Setting::get('unlock_fee', 49);
            $user = Auth::user();

            // Match the website flow: use an earned referral credit before
            // offering wallet or Razorpay payment.
            if ($user->free_unlocks > 0) {
                $user->decrement('free_unlocks', 1);

                $payment = Payment::create([
                    'user_id' => $user->id,
                    'type' => 'unlock',
                    'amount' => 0,
                    'gateway' => 'free_credit',
                    'reference_id' => $room->id,
                    'status' => 'completed',
                ]);
                Enquiry::updateOrCreate(
                    ['user_id' => $user->id, 'room_id' => $room->id],
                    ['payment_id' => $payment->id, 'unlocked' => true, 'unlocked_at' => now()]
                );

                DB::commit();
                \App\Services\NotificationService::notifyContactUnlocked(Auth::user(), $room);

                return $this->sendSuccess([
                    'contact' => $room->owner->phone ?? $room->owner->email,
                    'free_credit_used' => true,
                    'remaining_credits' => $user->free_unlocks,
                ], 'Unlocked with free credit');
            }

            if ($request->payment_method === 'wallet') {
                if ($user->wallet_balance >= $unlockFee) {
                    $user->decrement('wallet_balance', $unlockFee);
                    
                    $payment = Payment::create([
                        'user_id' => $user->id,
                        'type' => 'unlock',
                        'amount' => $unlockFee,
                        'gateway' => 'wallet',
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
                    \App\Services\NotificationService::notifyContactUnlocked(Auth::user(), $room);

                    return $this->sendSuccess([
                        'contact' => $room->owner->phone ?? $room->owner->email,
                        'new_balance' => (float) $user->wallet_balance
                    ], 'Unlocked via wallet');
                } else {
                    DB::rollBack();
                    return $this->sendError('Insufficient wallet balance', [], 422);
                }
            }

            // 3. Initiate Online Payment
            if ($unlockFee <= 0) {
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
                 return $this->sendSuccess([
                     'contact' => $room->owner->phone ?? $room->owner->email
                 ], 'Unlocked for free');
            }
            
            $payment = Payment::create([
                'user_id' => Auth::id(), 'type' => 'unlock', 'amount' => $unlockFee,
                'gateway' => 'razorpay', 'reference_id' => $room->id, 'status' => 'pending'
            ]);
            Enquiry::updateOrCreate(['user_id' => Auth::id(), 'room_id' => $room->id], ['payment_id' => $payment->id, 'unlocked' => false]);
            DB::commit();
            return $this->sendSuccess([
                'amount' => (float) $unlockFee,
                'payment_record_id' => $payment->id,
                'reference_id' => $room->id,
                'type' => 'unlock'
            ], 'Payment required to unlock contact');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($this->safeErrorMessage($e, 'Unable to unlock contact. Please try again.'), [], 500);
        }
    }
}
