<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiFcmTokenController extends BaseApiController
{
    /**
     * Save or update the FCM token for the authenticated user.
     * Called by the Flutter app after login or when the token refreshes.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'nullable|string',
            'type'      => 'nullable|in:mobile,web',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid token.', $validator->errors(), 422);
        }

        $type  = $request->input('type', 'mobile');
        $user  = $request->user();
        $token = $request->fcm_token;

        if ($type === 'web') {
            $user->update(['web_push_token' => $token]);
        } else {
            $user->update(['fcm_token' => $token]);
        }

        return $this->sendSuccess([], 'FCM token saved successfully.');
    }

    /**
     * Remove FCM token on logout or permission denied.
     */
    public function destroy(Request $request)
    {
        $type = $request->input('type', 'mobile');
        $user = $request->user();

        if ($type === 'web') {
            $user->update(['web_push_token' => null]);
        } else {
            $user->update(['fcm_token' => null]);
        }

        return $this->sendSuccess([], 'FCM token removed.');
    }
}
