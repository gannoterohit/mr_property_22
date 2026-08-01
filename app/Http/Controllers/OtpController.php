<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Otp;
use App\Models\User;
use App\Models\Setting;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpController extends Controller
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
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        
        // Check if user exists and is blocked (optional, but good for security)
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $existingUser->is_blocked) {
             return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked.'
            ], 403);
        }
        if ($existingUser && $existingUser->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin and staff accounts must use the admin login page.'
            ], 403);
        }

        $code = Otp::generate($email);
        
        // Apply dynamic SMTP configuration from settings
        Setting::setMailConfig();
        
        // Force refresh the mailer configuration
        \Illuminate\Support\Facades\Mail::purge();
        
        // Send OTP via email using the OtpMail class
        try {
            Mail::to($email)->send(new OtpMail($code));
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("Failed to send OTP to {$email}: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP right now. Please try again shortly.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully'
        ]);
    }

    /**
     * Verify OTP for login
     */
    public function verifyLoginOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $otp = $request->otp;

        $user = User::where('email', $email)->first();

        if ($user && $user->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Admin and staff accounts must use the admin login page.'
            ], 403);
        }

        if (!Otp::verify($email, $otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email'
            ], 404);
        }

        // Check if user is blocked
        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been blocked. Please contact support.'
            ], 403);
        }

        // Log in the user
        auth()->login($user);
        
        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => route('dashboard')
        ]);
    }

    /**
     * Verify OTP for registration
     */
    public function verifyRegistrationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'nullable|in:user,owner',
            'otp' => 'required|string|min:6|max:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $otp = $request->otp;

        if (!Otp::verify($email, $otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP'
            ], 401);
        }

        // Handle Referral
        $referredBy = null;
        $initialFreeUnlocks = 0;
        
        // Prioritize referral code from request, fallback to session
        $referralCode = $request->referral_code ?? session('referral_code');

        if ($referralCode && \App\Models\Setting::get('referral_enabled', '1') === '1') {
            $referrer = User::where('referral_code', $referralCode)->first();
            if ($referrer) {
                $referredBy = $referrer->id;
                
                // Reward Referrer: +1 Free Unlock
                $referrer->increment('free_unlocks', 1);
                
                // Joining bonus for new user: +1 Free Unlock
                $initialFreeUnlocks = 1;
            }
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role ?? 'user',
            'password' => Hash::make(Str::random(64)),
            'email_verified_at' => now(),
            'referred_by_id' => $referredBy,
            'wallet' => 0, // Points system ignored now
            'free_unlocks' => $initialFreeUnlocks,
        ]);

        // Clear referral session
        session()->forget('referral_code');

        // Log in the user
        auth()->login($user);
        
        $msg = 'Registration successful!';
        if ($initialFreeUnlocks > 0) {
            $msg .= " You have received {$initialFreeUnlocks} Free Contact Unlock as a joining bonus!";
        }
        
        // Flash signup success for Google Ads tracking
        session(['signup_success' => true]);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'redirect' => route('home') // Users go to home page directly per new layout rules
        ]);
    }
}
