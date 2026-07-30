<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after:from_date',
        ]);

        $room = Room::findOrFail($request->room_id);
        $from = \Carbon\Carbon::parse($request->from_date);
        $to = \Carbon\Carbon::parse($request->to_date);
        $months = $from->diffInMonths($to) + 1;
        $total = $room->rent * $months;

        // Legacy booking support only. ApnaNest does not charge commission or
        // a booking service fee in the current service-based product.
        $adminCommission = 0;
        $serviceCharge = 0;
        $ownerPayout = $total;
        $userPayAmount = $total;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'room_id' => $room->id,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'total_amount' => $total,
                'admin_commission' => $adminCommission,
                'service_charge' => $serviceCharge,
                'owner_payout' => $ownerPayout,
                'status' => 'pending',
            ]);

            $payment = Payment::create([
                'user_id' => Auth::id(),
                'type' => 'booking',
                'amount' => $userPayAmount,
                'gateway' => 'razorpay',
                'reference_id' => $booking->id,
                'status' => 'pending',
            ]);

            $booking->update(['payment_id' => $payment->id]);

            Payout::create([
                'owner_id' => $room->user_id,
                'booking_id' => $booking->id,
                'amount' => $ownerPayout,
                'status' => 'pending',
                'release_date' => now()->addDays(7),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'amount' => $userPayAmount,
                'message' => 'Booking created. Please complete payment.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Booking failed: '.$e->getMessage());
        }
    }
}
