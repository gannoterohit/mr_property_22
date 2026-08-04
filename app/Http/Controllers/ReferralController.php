<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    public function track(Request $request, $code)
    {
        // Store the referral code in the session for 30 days
        session(['referral_code' => $code]);

        // Redirect to homepage or registration
        return redirect()->route('home')->with('info', 'Welcome! You have been referred by a friend. Register now to get 1 Free Contact Unlock!');
    }

    /**
     * Display the referral dashboard for the user.
     */
    public function index()
    {
        if (!\App\Models\Setting::isEnabled('referral_enabled', true)) {
            return redirect()->route('home')->with('error', 'Referral program is currently inactive.');
        }

        $user = Auth::user();
        
        // Ensure user has a referral code for link generation
        if (!$user->referral_code) {
            $user->referral_code = \App\Models\User::generateUniqueReferralCode();
            $user->save();
        }

        $referrals = User::where('referred_by_id', $user->id)->latest()->get();
        $referralLink = route('referral.track', ['code' => $user->referral_code]);

        return view('account.referral', compact('user', 'referrals', 'referralLink'));
    }
}
