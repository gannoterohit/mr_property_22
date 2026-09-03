<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentFulfillmentService
{
    public function fulfill(Payment $payment): Payment
    {
        $payment = DB::transaction(function () use ($payment) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status === 'completed') {
                return $lockedPayment;
            }

            if ($lockedPayment->status !== 'pending') {
                throw new \RuntimeException('Payment is no longer pending.');
            }

            $this->applyAction($lockedPayment);
            $lockedPayment->update(['status' => 'completed']);

            return $lockedPayment->fresh();
        });

        $user = User::find($payment->user_id);
        if ($user) {
            if ($payment->type === 'unlock' && $payment->reference_id) {
                $room = Room::find($payment->reference_id);
                if ($room) {
                    NotificationService::notifyContactUnlocked($user, $room);
                }
            }
            NotificationService::notifyPaymentSuccess($user, $payment);
        }

        return $payment;
    }

    private function applyAction(Payment $payment): void
    {
        if (in_array($payment->type, ['listing', 'broker_listing'], true)) {
            $query = Room::whereKey($payment->reference_id);
            if ($payment->type === 'broker_listing') {
                $query->where(function ($roomQuery) use ($payment) {
                    $roomQuery->where('broker_id', $payment->user_id)->orWhere('user_id', $payment->user_id);
                });
            } else {
                $query->where('user_id', $payment->user_id);
            }
            $updated = $query->update(['listing_fee_paid' => true, 'status' => 'active', 'listing_payment_id' => $payment->id]);
            if ($updated !== 1) {
                throw new \RuntimeException('Listing room was not found or is not owned by the payer.');
            }
        } elseif ($payment->type === 'featured') {
            $updated = Room::whereKey($payment->reference_id)->where('user_id', $payment->user_id)
                ->update(['is_featured' => true]);
            if ($updated !== 1) {
                throw new \RuntimeException('Featured room was not found or is not owned by the payer.');
            }
        } elseif ($payment->type === 'unlock') {
            if (!Room::whereKey($payment->reference_id)->exists()) {
                throw new \RuntimeException('Unlock room was not found.');
            }
            Enquiry::updateOrCreate(
                ['room_id' => $payment->reference_id, 'user_id' => $payment->user_id],
                ['payment_id' => $payment->id, 'unlocked' => true, 'unlocked_at' => now()]
            );
        } elseif ($payment->type === 'subscription') {
            $subscription = Subscription::whereKey($payment->reference_id)
                ->where('user_id', $payment->user_id)->with('plan')->first();
            if (!$subscription || !$subscription->plan) {
                throw new \RuntimeException('Subscription was not found or is not owned by the payer.');
            }
            $subscription->update([
                'status' => 'active',
                'start_date' => now(),
                    'end_date' => null,
                'payment_id' => $payment->id,
            ]);
        }
    }
}