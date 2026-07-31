<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Room;
use App\Models\Subscription;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;

class ApiPaymentController extends BaseApiController
{
    protected $api;

    public function __construct()
    {
        try {
            $key = trim(Setting::get('razorpay_key', ''));
            $secret = trim(Setting::get('razorpay_secret', ''));
            if (!empty($key) && !empty($secret)) {
                $this->api = new Api($key, $secret);
            }
        } catch (\Exception $e) {
            Log::error('Razorpay initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'payment_record_id' => 'required|integer|exists:payments,id'
        ]);

        try {
            $key = trim(Setting::get('razorpay_key', ''));
            if (!$this->api) {
                return $this->sendError('Payment gateway is not configured. Please contact support.', [], 503);
            }

            $payment = Payment::whereKey($request->payment_record_id)
                ->where('user_id', Auth::id())->where('status', 'pending')->firstOrFail();
            $amount_paise = (int) round($payment->amount * 100);

            $order = $this->api->order->create([
                'receipt' => 'payment_' . $payment->id,
                'amount' => $amount_paise,
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            $payment->update(['gateway_order_id' => $order->id]);

            return $this->sendSuccess([
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'key' => $key
            ], 'Order created successfully');

        } catch (\Exception $e) {
            return $this->sendError(
                config('app.debug') ? 'Failed to create order: ' . $e->getMessage() : 'Failed to create order'
            );
        }
    }

    /**
     * Verify Razorpay Payment
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
            'payment_record_id' => 'required'
        ]);

        $key = trim(Setting::get('razorpay_key', ''));
        $secret = trim(Setting::get('razorpay_secret', ''));

        if ($key === '' || $secret === '') {
            return $this->sendError('Payment gateway is not configured. Please contact support.', [], 503);
        }

        try {
            $api = new Api($key, $secret);
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];
            $api->utility->verifyPaymentSignature($attributes);
            $gatewayPayment = $api->payment->fetch($request->razorpay_payment_id);
        } catch (\Exception $e) {
            return $this->sendError('Invalid signature');
        }

        DB::beginTransaction();
        try {
            $payment = Payment::where('id', $request->payment_record_id)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'completed') {
                DB::commit();
                return $this->sendSuccess([], 'Payment already verified');
            }

            if ($payment->status !== 'pending') {
                DB::rollBack();
                return $this->sendError('Payment is no longer pending', [], 409);
            }

            if ($payment->gateway_order_id !== $request->razorpay_order_id
                || ($gatewayPayment['order_id'] ?? null) !== $payment->gateway_order_id
                || (int) ($gatewayPayment['amount'] ?? 0) !== (int) round($payment->amount * 100)
                || ($gatewayPayment['currency'] ?? null) !== 'INR') {
                DB::rollBack();
                return $this->sendError('Payment order mismatch', [], 400);
            }

            $payment->update([
                'transaction_id' => $request->razorpay_payment_id,
                'status' => 'completed'
            ]);

            // Execute action based on type
            $this->processPaymentAction($payment);

            DB::commit();
            return $this->sendSuccess([], 'Payment verified successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                config('app.debug') ? 'Action failed: ' . $e->getMessage() : 'Payment processing failed'
            );
        }
    }

    private function processPaymentAction($payment)
    {
        $type = $payment->type;
        $refId = $payment->reference_id;

        if ($type === 'listing') {
            Room::where('id', $refId)->update(['listing_fee_paid' => true, 'status' => 'active']);
        } elseif ($type === 'featured') {
            Room::where('id', $refId)->update(['is_featured' => true]);
        } elseif ($type === 'unlock') {
            Enquiry::updateOrCreate(
                ['room_id' => $refId, 'user_id' => $payment->user_id],
                ['payment_id' => $payment->id, 'unlocked' => true, 'unlocked_at' => now()]
            );
        } elseif ($type === 'subscription') {
            $sub = Subscription::find($refId);
            if ($sub) {
                $sub->update([
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addDays($sub->plan->duration_days),
                    'payment_id' => $payment->id
                ]);
            }
        } elseif ($type === 'booking') {
            $booking = \App\Models\Booking::find($refId);
            if ($booking) {
                $booking->update([
                    'status' => 'confirmed',
                    'payment_id' => $payment->id
                ]);

                // Update room status
                \App\Models\Room::where('id', $booking->room_id)->update(['status' => 'booked']);

                // Create Payout for owner
                \App\Models\Payout::create([
                    'owner_id' => $booking->room->user_id,
                    'booking_id' => $booking->id,
                    'amount' => $booking->owner_payout,
                    'status' => 'pending',
                    'release_date' => now()->addDays(7)
                ]);
            }
        }
    }
}
