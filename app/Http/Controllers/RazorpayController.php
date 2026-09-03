<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Enquiry;
use App\Models\Room;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PaymentFulfillmentService;

class RazorpayController extends Controller
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

    // Create order on Razorpay then return order info to frontend
    public function createOrder(Request $request)
    {
        $request->validate(['payment_id' => 'required|integer|exists:payments,id']);
        try {
            $key = trim(Setting::get('razorpay_key', ''));
            $secret = trim(Setting::get('razorpay_secret', ''));
            
            if (empty($key) || empty($secret)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Razorpay keys not configured. Please add them in Business Settings.'
                ], 400);
            }

            // Initialize API if not already initialized
            if (!$this->api) {
                $this->api = new Api($key, $secret);
            }

            $payment = Payment::whereKey($request->payment_id)
                ->where('user_id', Auth::id())
                ->where('status', 'pending')
                ->lockForUpdate()
                ->firstOrFail();

            $amount = (int) $payment->amount;
            
            if ($amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid amount'
                ], 400);
            }
            
            $amount_paise = $amount * 100;

            $order = $this->api->order->create([
                'receipt' => 'payment_' . $payment->id,
                'amount' => $amount_paise,
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            $payment->update(['gateway_order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'amount' => $amount,
                'currency' => 'INR',
                'key' => $key
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? 'Failed to create order: ' . $e->getMessage()
                    : 'Unable to start payment. Please try again.'
            ], 500);
        }
    }

    // Verify payment signature after checkout (optional double-check)
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'payment_id' => 'required|integer|exists:payments,id',
        ]);
        $key = trim(Setting::get('razorpay_key', ''));
        $secret = trim(Setting::get('razorpay_secret', ''));
        
        // Ensure API is initialized
        if (!$this->api) {
            if (!empty($key) && !empty($secret)) {
                $this->api = new Api($key, $secret);
            } else {
                 Log::error('Razorpay keys missing during verification');
                 return response()->json(['status'=>'fail','message'=>'Configuration Error'], 500);
            }
        }

        $payload = $request->all();
        $signature = $payload['razorpay_signature'] ?? null;
        $orderId = $payload['razorpay_order_id'] ?? null;
        $paymentId = $payload['razorpay_payment_id'] ?? null;
        
        // Use request inputs (from POST body OR Query Params for callback flow)
        $dbPaymentId = $request->input('payment_id');
        $payment = Payment::whereKey($dbPaymentId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $paymentType = $payment->type;
        $referenceId = $payment->reference_id;

        if ($payment->gateway_order_id !== $orderId) {
            return response()->json(['status' => 'fail', 'message' => 'Payment order mismatch'], 400);
        }

        // Verify signature before any session or DB changes
        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature
            ];

            $this->api->utility->verifyPaymentSignature($attributes);
            $gatewayPayment = $this->api->payment->fetch($paymentId);
            if (($gatewayPayment['order_id'] ?? null) !== $payment->gateway_order_id
                || (int) ($gatewayPayment['amount'] ?? 0) !== (int) round($payment->amount * 100)
                || ($gatewayPayment['currency'] ?? null) !== 'INR') {
                throw new \RuntimeException('Gateway payment details do not match the order');
            }
        } catch (\Exception $e) {
            Log::error("Razorpay signature verification failed: " . $e->getMessage());
            $message = config('app.debug')
                ? 'Signature verification failed: ' . $e->getMessage()
                : 'Signature verification failed';

            return response()->json(['status' => 'fail', 'message' => $message], 400);
        }

        \DB::beginTransaction();
        try {
            // Update payment record — order ID must match the pending record
            $payment = Payment::where('id', $dbPaymentId)
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status === 'completed') {
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Payment already verified']);
            }
            if ($payment->status !== 'pending') {
                throw new \RuntimeException('Payment is not pending');
            }

            $payment->update([
                'transaction_id' => $paymentId,
            ]);

            $payment = app(PaymentFulfillmentService::class)->fulfill($payment);
            DB::commit();
        
        // Prepare conversion tracking data for Google Ads
        $conversionData = [
            'payment_type' => $paymentType,
            'amount' => $payment->amount,
            'payment_id' => $payment->id
        ];
        
        // Handle Response format (JSON for AJAX/Desktop, Redirect for Mobile/POST)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success', 
                'message' => 'Payment verified successfully',
                'conversion_data' => $conversionData
            ]);
        } else {
            // Redirect Logic for Mobile Flow (with conversion tracking in session)
            session(['google_ads_conversion' => $conversionData]);
            
            if ($paymentType === 'listing' || $paymentType === 'featured') {
                 // For listing/featured, owner dashboard is typically best
                 // If user is admin (unlikely for this flow but possible), maybe admin dashboard
                 if (Auth::user()->role === 'owner') {
                     return redirect()->route('owner.dashboard')->with('success', 'Payment successful! Action completed.');
                 } else {
                     return redirect()->route('rooms.index')->with('success', 'Payment successful! Room updated.');
                 }
            } elseif ($paymentType === 'unlock') {
                return redirect()->route('rooms.show', $referenceId)->with('success', 'Payment successful! Contact unlocked.');
            } elseif ($paymentType === 'subscription') {
                return redirect()->route('plans')->with('success', 'Subscription activated successfully!');
            } else {
                return redirect()->route('home')->with('success', 'Payment verified successfully!');
            }
        }

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Payment handling failed: " . $e->getMessage());
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'fail', 'message' => 'Payment recorded but updating status failed'], 500);
        } else {
            return redirect()->route('home')->with('error', 'Payment recorded but updating status failed. Please contact support.');
        }
    }
    }

    // Webhook: Razorpay will post event payloads here
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        $webhook_secret = trim(Setting::get('razorpay_webhook_secret', ''))
            ?: trim((string) config('payment.webhook_secret'));

        if ($webhook_secret === '') {
            Log::error('Razorpay webhook secret is not configured');
            return response('webhook not configured', 503);
        }

        if (!$signature) {
            Log::warning('Razorpay webhook missing signature header');
            return response('missing signature', 400);
        }

        // verify signature
        $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
        if (!hash_equals($expected_signature, $signature)) {
            Log::warning('Invalid webhook signature');
            return response('invalid signature', 400);
        }

        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['event'])) {
            Log::warning('Razorpay webhook contained invalid JSON');
            return response('invalid payload', 400);
        }

        $paymentEntity = $data['payload']['payment']['entity'] ?? [];
        Log::info('Razorpay webhook received', [
            'event' => $data['event'],
            'payment_id' => $paymentEntity['id'] ?? null,
            'order_id' => $paymentEntity['order_id'] ?? null,
            'status' => $paymentEntity['status'] ?? null,
        ]);

        $eventId = (string) ($data['id'] ?? 'payload-'.hash('sha256', $payload));
        $eventId = strlen($eventId) > 100 ? hash('sha256', $eventId) : $eventId;

        // Handle payment.captured event
        if ($data['event'] === 'payment.captured') {
            $paymentId = $paymentEntity['id'] ?? null;
            $orderId = $paymentEntity['order_id'] ?? null;

            if (!$paymentId) {
                Log::warning('Razorpay payment.captured webhook missing payment ID');
                return response('invalid payload', 400);
            }

            DB::beginTransaction();
            if (!DB::table('razorpay_webhook_events')->insertOrIgnore([
                'event_id' => $eventId,
                'event' => $data['event'],
                'created_at' => now(),
                'updated_at' => now(),
            ])) {
                DB::rollBack();
                return response('ok', 200);
            }
            // Find existing payment record with lock to prevent duplicate processing
            $payment = Payment::where(function ($query) use ($paymentId, $orderId) {
                    $query->where('transaction_id', $paymentId);
                    if ($orderId) {
                        $query->orWhere('gateway_order_id', $orderId);
                    }
                })
                ->lockForUpdate()
                ->first();

            if ($payment && $payment->status === 'pending') {
                try {
                    // Double-check status after acquiring lock
                    if ($payment->status !== 'pending') {
                        DB::rollBack();
                        return response('ok', 200);
                    }

                    if (($paymentEntity['order_id'] ?? null) !== $payment->gateway_order_id
                        || (int) ($paymentEntity['amount'] ?? 0) !== (int) round($payment->amount * 100)
                        || ($paymentEntity['currency'] ?? null) !== 'INR') {
                        DB::rollBack();
                        Log::warning('Webhook payment details did not match local payment', ['payment_id' => $payment->id]);
                        return response('payment mismatch', 400);
                    }

                    $payment->update([
                        'transaction_id' => $paymentId,
                    ]);

                    app(PaymentFulfillmentService::class)->fulfill($payment);
                    DB::commit();
                } catch (\Exception $e) {
                    \DB::rollBack();
                    Log::error('Webhook error: '.$e->getMessage());
                }
            } else {
                DB::commit();
            }
        }

        // Handle payment.failed event
        if ($data['event'] === 'payment.failed') {
            $paymentId = $paymentEntity['id'] ?? null;
            $orderId = $paymentEntity['order_id'] ?? null;

            if ($paymentId) {
                DB::transaction(function () use ($paymentId, $orderId, $paymentEntity, $eventId, $data): void {
                    if (!DB::table('razorpay_webhook_events')->insertOrIgnore([
                        'event_id' => $eventId,
                        'event' => $data['event'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])) {
                        return;
                    }
                    $payment = Payment::where(function ($query) use ($paymentId, $orderId) {
                            $query->where('transaction_id', $paymentId);
                            if ($orderId) {
                                $query->orWhere('gateway_order_id', $orderId);
                            }
                        })->lockForUpdate()->first();

                    if ($payment && $payment->status === 'pending') {
                        $payment->update(['status' => 'failed']);

                        Log::info('Payment marked as failed via webhook', [
                            'payment_id' => $payment->id,
                            'reason' => $paymentEntity['error_description'] ?? 'Unknown',
                        ]);
                    }
                });
            }
        }

        // Handle refund.created event
        if ($data['event'] === 'refund.created') {
            $paymentId = $paymentEntity['payment_id'] ?? $paymentEntity['id'] ?? null;

            if ($paymentId) {
                DB::transaction(function () use ($paymentId, $paymentEntity, $eventId, $data): void {
                    if (!DB::table('razorpay_webhook_events')->insertOrIgnore([
                        'event_id' => $eventId,
                        'event' => $data['event'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])) {
                        return;
                    }
                    $payment = Payment::where('transaction_id', $paymentId)->lockForUpdate()->first();

                    if ($payment && $payment->status === 'completed') {
                        $payment->update(['status' => 'refunded']);

                        Log::info('Payment marked as refunded via webhook', [
                            'payment_id' => $payment->id,
                            'refund_id' => $paymentEntity['id'] ?? null,
                        ]);
                    }
                });
            }
        }

        return response('ok', 200);
    }
}
