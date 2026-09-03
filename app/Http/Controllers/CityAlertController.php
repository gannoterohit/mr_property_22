<?php

namespace App\Http\Controllers;

use App\Models\CityAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityAlertController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:100',
        ]);

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to subscribe to alerts.'
            ], 401);
        }

        try {
            CityAlert::firstOrCreate([
                'user_id' => Auth::id(),
                'city' => $request->city
            ]);

            return response()->json([
                'success' => true,
                'message' => 'You will be notified when new rooms are listed in ' . $request->city
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function destroy(CityAlert $alert)
    {
        if ($alert->user_id !== Auth::id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $alert->delete();
        return back()->with('success', 'Alert removed successfully.');
    }
}
