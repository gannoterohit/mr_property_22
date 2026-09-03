<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ApiWalletController extends BaseApiController
{
    /**
     * Get wallet balance and points
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $transactions = \App\Models\Payment::where('user_id', $user->id)
            ->latest()
            ->paginate($request->get('limit', 15));

        return $this->sendSuccess([
            'points' => (float) ($user->wallet ?? 0),
            'balance' => (float) ($user->wallet_balance ?? 0),
            'transactions' => $transactions
        ]);
    }

    /**
     * Convert points to balance
     */
    public function convertPoints(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:100' // Lowered from 1000 for better user experience
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        $user = Auth::user();

        if ($user->wallet < $request->points) {
            return $this->sendError('Insufficient points', [], 422);
        }

        // Conversion Rate: 100 Points = ₹1
        $amount = ($request->points / 100) * 1;

        DB::beginTransaction();
        try {
            $user->decrement('wallet', $request->points);
            $user->increment('wallet_balance', $amount);

            DB::commit();

            return $this->sendSuccess([
                'points' => (float) $user->wallet,
                'balance' => (float) $user->wallet_balance
            ], "Converted {$request->points} Points to ₹{$amount} successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($this->safeErrorMessage($e, 'Unable to convert points. Please try again.'), [], 500);
        }
    }
}
