<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;
use App\Services\PaymentFulfillmentService;

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
            'payment_record_id' => 'required|integer|exists:payments,id',
            'idempotency_key' => 'required|string|max:100',
        ]);

        try {
            $key = trim(Setting::get('razorpay_key', ''));
            if (!$this->api) {
                return $this->sendError('Payment gateway is not configured. Please contact support.', [], 503);
            }

            $payment = Payment::whereKey($request->payment_record_id)
                ->where('user_id', Auth::id())->where('status', 'pending')->firstOrFail();
            $existing = Payment::where('user_id', Auth::id())->where('idempotency_key', $request->idempotency_key)
                ->where('id', '<>', $payment->id)->first();
            if ($existing) {
                return $this->sendError('Idempotency key has already been used.', [], 409);
            }
            if ($payment->idempotency_key === $request->idempotency_key && $payment->gateway_order_id) {
                return $this->sendSuccess([
                    'order_id' => $payment->gateway_order_id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'key' => $key,
                ], 'Order already created');
            }
            $amount_paise = (int) round($payment->amount * 100);
            if ($amount_paise <= 0) {
                return $this->sendError('Payment amount must be greater than zero.', [], 422);
            }

            $order = $this->api->order->create([
                'receipt' => 'payment_' . $payment->id,
                'amount' => $amount_paise,
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            $payment->update(['gateway_order_id' => $order->id, 'idempotency_key' => $request->idempotency_key]);

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
            'payment_record_id' => 'required',
            'idempotency_key' => 'required|string|max:100',
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
            return $this->sendError('Payment signature verification failed.', [], 400);
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

            if ($payment->idempotency_key !== $request->idempotency_key) {
                DB::rollBack();
                return $this->sendError('Payment idempotency key mismatch', [], 409);
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
            ]);

            // Execute action based on type
            app(PaymentFulfillmentService::class)->fulfill($payment);

            DB::commit();
            return $this->sendSuccess([], 'Payment verified successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                config('app.debug') ? 'Action failed: ' . $e->getMessage() : 'Payment processing failed'
            );
        }
    }

}
