<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebPushTokenController extends Controller
{
    /**
     * Save the web push FCM token for the authenticated user.
     * Called by the browser-side Firebase JS SDK after permission is granted.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:10',
        ]);

        Auth::user()->update(['web_push_token' => $request->token]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove web push token (e.g. when user denies permission later).
     */
    public function destroy(Request $request)
    {
        Auth::user()->update(['web_push_token' => null]);
        return response()->json(['success' => true]);
    }
}
