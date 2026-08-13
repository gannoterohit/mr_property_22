<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('account.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            
            $path = \App\Services\ImageOptimizer::optimize($request->file('avatar'), 'avatar');
            $user->avatar = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function sendDeleteOtp(Request $request)
    {
        $user = $request->user();
        $otp = rand(100000, 999999);
        
        // Cache OTP for 10 minutes
        \Illuminate\Support\Facades\Cache::put('delete_otp_' . $user->id, $otp, now()->addMinutes(10));
        
        // Apply dynamic SMTP configuration from settings
        \App\Models\Setting::setMailConfig();
        
        // Send Email
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\AccountDeletionOtpMail($otp));
        
        return response()->json(['message' => 'OTP sent successfully']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'numeric'],
        ]);

        $user = $request->user();
        $cachedOtp = \Illuminate\Support\Facades\Cache::get('delete_otp_' . $user->id);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return back()->with('error', 'Invalid or expired verification code.');
        }

        Auth::logout();

        $user->delete();
        \Illuminate\Support\Facades\Cache::forget('delete_otp_' . $user->id);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
