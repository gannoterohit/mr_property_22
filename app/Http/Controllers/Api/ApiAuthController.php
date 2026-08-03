<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Mail\OtpMail;
use Illuminate\Support\Str;

class ApiAuthController extends BaseApiController
{
    /**
     * Send OTP to the provided email
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid email address', $validator->errors(), 422);
        }

        $email = $request->email;
        
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->is_blocked) {
             return $this->sendError('Your account has been blocked.', [], 403);
        }

        $code = Otp::generate($email);

        Setting::setMailConfig();

        try {
            Mail::to($email)->send(new OtpMail($code));
        } catch (\Exception $e) {
            Log::error("API OTP mail failed for {$email}: " . $e->getMessage());

            return $this->sendError(
                'Failed to send OTP. Please try again later or contact support.',
                config('app.debug') ? ['error' => $e->getMessage()] : [],
                500
            );
        }

        return $this->sendSuccess([], 'OTP sent successfully');
    }

    /**
     * Login using OTP
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        if (!Otp::verify($request->email, $request->otp)) {
            return $this->sendError('Invalid or expired OTP', [], 401);
        }

        $user = User::where('email', $request->email)->first();
        
        // Auto-Register if user does not exist
        if (!$user) {
            $user = User::create([
                'name' => 'User', 
                'email' => $request->email,
                'role' => 'user', 
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
            ]);
        }

        if ($user->is_blocked) {
            return $this->sendError('Your account is blocked.', [], 403);
        }
        if ($user->role === 'admin') {
            return $this->sendError('Administrators must use the admin email and password login.', [], 403);
        }

        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = $user->createToken('flutter_app')->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'user' => new UserResource($user)
        ], 'Login successful');
    }

    /**
     * Register a new user with OTP
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'nullable|in:user,owner',
            'otp' => 'required|string|min:6|max:6',
            'referral_code' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Please check your input and try again.', $validator->errors(), 422);
        }

        if (!Otp::verify($request->email, $request->otp)) {
            return $this->sendError('Invalid or expired OTP', [], 401);
        }

        // Handle Referral
        $referredBy = null;
        $initialFreeUnlocks = 0;
        
        if ($request->referral_code && \App\Models\Setting::get('referral_enabled', '1') === '1') {
            $referrer = User::where('referral_code', $request->referral_code)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                // Reward Referrer: +1 Free Unlock
                $referrer->increment('free_unlocks', 1);
                // Joining bonus for new user: +1 Free Unlock
                $initialFreeUnlocks = 1;
            }
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->input('role', 'user'),
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'referred_by_id' => $referredBy,
            'wallet' => 0,
            'free_unlocks' => $initialFreeUnlocks,
            'fcm_token' => $request->fcm_token ?? null,
        ]);

        $token = $user->createToken('flutter_app')->plainTextToken;

        return $this->sendSuccess([
            'token' => $token,
            'user' => new UserResource($user)
        ], 'Registration successful');
    }

    /**
     * Get authenticated user profile
     */
    public function user(Request $request)
    {
        return $this->sendSuccess(new UserResource($request->user()));
    }

    /**
     * Logout and revoke tokens
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['fcm_token' => null]);
            $user->tokens()->delete();
        }

        return $this->sendSuccess([], 'Logged out successfully');
    }
}
