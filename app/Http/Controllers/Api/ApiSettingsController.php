<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Setting;
use Illuminate\Http\Request;

class ApiSettingsController extends BaseApiController
{
    /**
     * Get global app settings
     */
    public function index()
    {
        $settings = [
            'website_name' => Setting::get('website_name', 'RoomRental'),
            'website_logo' => asset('storage/' . Setting::get('website_logo', 'logo.png')),
            'contact_email' => Setting::get('contact_email', ''),
            'contact_phone' => Setting::get('contact_phone', ''),
            'currency_symbol' => '₹',
            'razorpay_key' => Setting::get('razorpay_key', ''),
            'app_download' => [
                'play_store_url' => Setting::get('play_store_url', ''),
                'app_store_url' => Setting::get('app_store_url', ''),
                'is_available' => filled(Setting::get('play_store_url')) || filled(Setting::get('app_store_url')),
            ],
            'social_links' => [
                'facebook' => Setting::get('facebook_url', ''),
                'instagram' => Setting::get('instagram_url', ''),
                'twitter' => Setting::get('twitter_url', ''),
                'youtube' => Setting::get('youtube_url', ''),
            ],
            'fees' => [
                'unlock_fee_enabled' => filter_var(Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
                'listing_fee_enabled' => filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
                'unlock_fee' => filter_var(Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN)
                    ? (float) Setting::get('unlock_fee', 49) : 0,
                'listing_fee' => filter_var(Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN)
                    ? (float) Setting::get('listing_fee', 199) : 0,
                'configured_unlock_fee' => (float) Setting::get('unlock_fee', 49),
                'configured_listing_fee' => (float) Setting::get('listing_fee', 199),
                'featured_fee' => (float) Setting::get('featured_fee', 99),
            ],
            'module_availability' => [
                'maintenance_mode' => filter_var(Setting::get('maintenance_mode', '0'), FILTER_VALIDATE_BOOLEAN),
                'registration_enabled' => filter_var(Setting::get('registration_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'new_listings_enabled' => filter_var(Setting::get('new_listings_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'payments_enabled' => filter_var(Setting::get('payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'owner_panel_enabled' => filter_var(Setting::get('owner_panel_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'user_panel_enabled' => filter_var(Setting::get('user_panel_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'wallet_enabled' => filter_var(Setting::get('wallet_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'referral_enabled' => filter_var(Setting::get('referral_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'promo_enabled' => filter_var(Setting::get('promo_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'subscriptions_enabled' => filter_var(Setting::get('payments_enabled', '1'), FILTER_VALIDATE_BOOLEAN),
                'google_login_enabled' => filter_var(Setting::get('google_login_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
                'facebook_login_enabled' => filter_var(Setting::get('facebook_login_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            ],
            'maintenance' => [
                'title' => Setting::get('maintenance_title', 'Website is currently under maintenance'),
                'message' => Setting::get('maintenance_message', 'We are improving your experience and will be back soon.'),
                'reopening_at' => Setting::get('maintenance_reopening_at'),
            ],
        ];

        return $this->sendSuccess($settings);
    }
}
