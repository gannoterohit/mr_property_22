<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use App\Mail\AccountDeletionOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ApiProfileController extends BaseApiController
{
    /**
     * Get user profile
     */
    public function show()
    {
        return $this->sendSuccess(new UserResource(Auth::user()));
    }

    /**
     * Update profile details
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:15',
            'avatar' => 'nullable|image|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $user->name = $request->name;
        $user->phone = $request->phone;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return $this->sendSuccess(new UserResource($user), 'Profile updated successfully');
    }

    /**
     * Send OTP for account deletion
     */
    public function sendDeleteOtp()
    {
        $user = Auth::user();
        $otp = random_int(100000, 999999);
        Cache::put('delete_otp_' . $user->id, (string) $otp, now()->addMinutes(10));
        Setting::setMailConfig();

        try {
            Mail::to($user->email)->send(new AccountDeletionOtpMail($otp));
        } catch (\Exception $e) {
            Cache::forget('delete_otp_' . $user->id);
            \Illuminate\Support\Facades\Log::error('Delete OTP mail failed: ' . $e->getMessage());
            return $this->sendError(
                'Unable to send verification email. Please try again later.',
                config('app.debug') ? ['error' => $e->getMessage()] : [],
                500
            );
        }

        return $this->sendSuccess([], 'OTP sent to your email for account deletion verification');
    }

    /**
     * Delete account
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);
        $cachedOtp = Cache::get('delete_otp_' . $user->id);
        if (!$cachedOtp || !hash_equals((string) $cachedOtp, (string) $request->otp)) {
            return $this->sendError('Invalid or expired OTP', [], 422);
        }

        $user->tokens()->delete();
        $user->delete();
        Cache::forget('delete_otp_' . $user->id);

        return $this->sendSuccess([], 'Account deleted successfully');
    }
}
