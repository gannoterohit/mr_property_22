<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        Log::info('Hit verifyPayment: ' . $request->razorpay_payment_id);
        
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
        $payment = Payment::whereKey($dbPaymentId)->firstOrFail();
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
            Log::error("Data: " . json_encode($attributes ?? []));

            $message = config('app.debug')
                ? 'Signature verification failed: ' . $e->getMessage()
                : 'Signature verification failed';

            return response()->json(['status' => 'fail', 'message' => $message], 400);
        }

        // Session restoration for mobile callback flow (only after signature is valid)
        if (!Auth::check() && $dbPaymentId) {
            $payment = Payment::find($dbPaymentId);
            if ($payment) {
                Log::info("Restoring session for user ID: " . $payment->user_id);
                Auth::loginUsingId($payment->user_id);
            } else {
                Log::warning("Could not find payment for session restoration: $dbPaymentId");
            }
        }

        \DB::beginTransaction();
        try {
            // Update payment record — order ID must match the pending record
            $payment = Payment::where('id', $dbPaymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'completed') {
                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Payment already verified']);
            }
            if ($payment->status !== 'pending') {
                throw new \RuntimeException('Payment is not pending');
            }

            if (Auth::check() && $payment->user_id !== Auth::id()) {
                throw new \RuntimeException('Payment does not belong to this user');
            }

            $payment->update([
                'transaction_id' => $paymentId,
                'status' => 'completed'
            ]);

            // Handle based on payment type
            if ($paymentType === 'listing' && $referenceId) {
            // Activate room after listing fee payment
            $room = \App\Models\Room::whereKey($referenceId)->where('user_id', $payment->user_id)->first();
            if ($room) {
                $room->update([
                    'listing_fee_paid' => true,
                    'status' => 'active'
                ]);
            }
        } elseif ($paymentType === 'featured' && $referenceId) {
            // Make room featured
            $room = \App\Models\Room::whereKey($referenceId)->where('user_id', $payment->user_id)->first();
            if ($room) {
                $room->update(['is_featured' => true]);
            }
        } elseif ($paymentType === 'unlock' && $referenceId) {
            $unlockRoom = \App\Models\Room::whereKey($referenceId)->where('status', 'active')->where('listing_status', 'approved')->where('listing_fee_paid', true)->firstOrFail();
            // Unlock contact details
            $enquiry = \App\Models\Enquiry::where('room_id', $referenceId)
                ->where('user_id', $payment->user_id)
                ->where('payment_id', $payment->id)
                ->first();
            
            if ($enquiry) {
                $enquiry->update([
                    'unlocked' => true,
                    'unlocked_at' => now()
                ]);
            } else {
                // If enquiry not found, try to find by room_id and user_id only
                $enquiry = \App\Models\Enquiry::where('room_id', $referenceId)
                    ->where('user_id', $payment->user_id)
                    ->first();
                
                if ($enquiry) {
                    $enquiry->update([
                        'payment_id' => $payment->id,
                        'unlocked' => true,
                        'unlocked_at' => now()
                    ]);
                } else {
                    // Create enquiry if it doesn't exist
                    \App\Models\Enquiry::create([
                        'user_id' => $payment->user_id,
                        'room_id' => $referenceId,
                        'payment_id' => $payment->id,
                        'unlocked' => true,
                        'unlocked_at' => now()
                    ]);
                }
            }
        } elseif ($paymentType === 'booking' && $referenceId) {
            $booking = \App\Models\Booking::find($referenceId);
            if ($booking) {
                $booking->update(['status' => 'confirmed']);
                // Set room status to inactive when booked
                $room = $booking->room;
                if ($room) {
                    $room->update(['status' => 'inactive']);
                }
            }
        } elseif ($paymentType === 'subscription' && $referenceId) {
            $subscription = \App\Models\Subscription::whereKey($referenceId)->where('user_id', $payment->user_id)->first();
            if ($subscription) {
                $subscription->update([
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addDays($subscription->plan->duration_days),
                    'payment_id' => $payment->id
                ]);
            }
        }

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
            } elseif ($paymentType === 'booking') {
                return redirect()->route('home')->with('success', 'Booking confirmed successfully!');
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

        // Handle payment.captured event
        if ($data['event'] === 'payment.captured') {
            $paymentId = $paymentEntity['id'] ?? null;
            $orderId = $paymentEntity['order_id'] ?? null;

            if (!$paymentId) {
                Log::warning('Razorpay payment.captured webhook missing payment ID');
                return response('invalid payload', 400);
            }

            // Find existing payment record
            $payment = Payment::where('transaction_id', $paymentId)
                ->when($orderId, fn ($query) => $query->orWhere('gateway_order_id', $orderId))
                ->first();

            if ($payment && $payment->status === 'pending') {
                \DB::beginTransaction();
                try {
                    $payment->update([
                        'transaction_id' => $paymentId,
                        'status' => 'completed'
                    ]);

                    // Handle unlock
                    if ($payment->type === 'unlock' && $payment->reference_id) {
                        $enquiry = \App\Models\Enquiry::where('room_id', $payment->reference_id)
                            ->where('user_id', $payment->user_id)
                            ->where('payment_id', $payment->id)
                            ->first();
                        
                        if ($enquiry) {
                            $enquiry->update([
                                'unlocked' => true,
                                'unlocked_at' => now()
                            ]);
                        } else {
                            // If enquiry not found, try to find by room_id and user_id only
                            $enquiry = \App\Models\Enquiry::where('room_id', $payment->reference_id)
                                ->where('user_id', $payment->user_id)
                                ->first();
                            
                            if ($enquiry) {
                                $enquiry->update([
                                    'payment_id' => $payment->id,
                                    'unlocked' => true,
                                    'unlocked_at' => now()
                                ]);
                            } else {
                                // Create enquiry if it doesn't exist
                                \App\Models\Enquiry::create([
                                    'user_id' => $payment->user_id,
                                    'room_id' => $payment->reference_id,
                                    'payment_id' => $payment->id,
                                    'unlocked' => true,
                                    'unlocked_at' => now()
                                ]);
                            }
                        }
                    }
                    // Handle booking
                    elseif ($payment->type === 'booking' && $payment->reference_id) {
                        $booking = \App\Models\Booking::find($payment->reference_id);
                        if ($booking) {
                            $booking->update(['status' => 'confirmed']);
                            // Set room status to inactive when booked
                            $room = $booking->room;
                            if ($room) {
                                $room->update(['status' => 'inactive']);
                            }
                        }
                    }

                    // Handle subscription
                    if ($payment->type === 'subscription' && $payment->reference_id) {
                        $subscription = \App\Models\Subscription::find($payment->reference_id);
                        if ($subscription && $subscription->status === 'pending') {
                            $subscription->update(['status' => 'active']);
                        }
                    }

                    \DB::commit();
                } catch (\Exception $e) {
                    \DB::rollBack();
                    Log::error('Webhook error: '.$e->getMessage());
                }
            }
        }

        return response('ok', 200);
    }
}
