<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsController extends Controller
{
    private const TAB_FIELDS = [
        'general' => [
            'listing_fee', 'featured_fee', 'unlock_fee', 'listing_fee_enabled', 'unlock_fee_enabled',
        ],
        'broker' => [
            'broker_module_enabled', 'broker_verification_enabled', 'broker_listing_charges_enabled',
            'broker_featured_enabled', 'broker_lead_charge_enabled', 'broker_future_brokerage_enabled',
            'broker_per_listing_charge', 'broker_featured_charge', 'broker_listing_expiry_days',
            'broker_free_listing_limit', 'broker_lead_charge',
        ],
        'appearance' => [
            'website_name', 'primary_color', 'secondary_color', 'admin_access_key', 'facebook_url',
            'twitter_url', 'instagram_url', 'linkedin_url', 'navbar_logo', 'footer_logo', 'website_logo',
            'website_favicon', 'owner_cta_image', 'default_hero_image', 'auth_modal_image',
            'registration_image', 'contact_phone', 'contact_email', 'company_address', 'business_hours',
        ],
        'payment' => [
            'razorpay_key', 'razorpay_secret', 'razorpay_webhook_secret', 'wallet_enabled',
        ],
        'integrations' => [
            'google_maps_api_key', 'play_store_url', 'app_store_url', 'google_login_enabled',
            'google_client_id', 'google_client_secret', 'google_redirect_url', 'facebook_login_enabled',
            'facebook_client_id', 'facebook_client_secret', 'facebook_redirect_url',
        ],
        'firebase' => [
            'firebase_push_enabled', 'firebase_project_id', 'firebase_web_api_key', 'firebase_app_id',
            'firebase_messaging_sender_id', 'firebase_vapid_key', 'firebase_server_key', 'firebase_service_account_json',
        ],
        'sms' => ['otp_delivery', 'sms_gateway', 'sms_api_key', 'sms_sender_id', 'sms_dlt_te_id'],
        'seo' => [
            'website_url', 'seo_meta_description', 'seo_meta_keywords', 'google_search_console_code',
            'ga4_measurement_id', 'google_ads_enabled', 'google_ads_tag_id', 'google_ads_conversion_label',
            'google_ads_signup_label', 'google_ads_room_view_label', 'meta_pixel_enabled', 'meta_pixel_id',
            'adsense_enabled', 'adsense_client_id', 'adsense_home_top_id', 'adsense_home_bottom_id',
            'adsense_room_content_id', 'adsense_room_sidebar_id',
        ],
        'mail' => ['mail_host', 'mail_port', 'mail_username', 'mail_password'],
        'referral' => ['referral_enabled'],
        'modal' => [
            'promo_enabled', 'promo_modal_enabled', 'promo_modal_audience', 'promo_modal_type',
            'promo_modal_badge', 'promo_modal_title', 'promo_modal_description', 'promo_modal_btn_text',
            'promo_modal_btn_url', 'promo_modal_delay', 'promo_modal_cooldown_hours', 'promo_modal_image',
        ],
    ];

    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.business', compact('settings'));
    }

    public function maintenance()
    {
        return view('admin.settings.maintenance');
    }

    public function updateMaintenance(Request $request)
    {
        $data = $request->validate([
            'maintenance_title' => ['required', 'string', 'max:120'],
            'maintenance_message' => ['required', 'string', 'max:500'],
            'maintenance_reopening_at' => ['nullable', 'date'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        foreach (['maintenance_mode', 'registration_enabled', 'new_listings_enabled', 'payments_enabled', 'owner_panel_enabled', 'user_panel_enabled'] as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0');
        }

        foreach (['broker_module_enabled', 'broker_verification_enabled', 'broker_listing_charges_enabled', 'broker_featured_enabled', 'broker_future_brokerage_enabled'] as $key) {
            \App\Models\BrokerSetting::set($key, $request->boolean($key) ? '1' : '0');
        }

        return back()->with('success', 'Platform availability settings updated.');
    }

    public function update(Request $request)
    {
        $rules = [
            'listing_fee' => ['nullable', 'numeric', 'min:0'],
            'featured_fee' => ['nullable', 'numeric', 'min:0'],
            'unlock_fee' => ['nullable', 'numeric', 'min:0'],
            'website_name' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:1000'],
            'business_hours' => ['nullable', 'string', 'max:120'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'admin_access_key' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:500'],
            'twitter_url' => ['nullable', 'url', 'max:500'],
            'instagram_url' => ['nullable', 'url', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'referral_enabled' => ['nullable', 'boolean'],
            'wallet_enabled' => ['nullable', 'boolean'],
            'promo_enabled' => ['nullable', 'boolean'],
            'razorpay_key' => ['nullable', 'string', 'max:255'],
            'razorpay_secret' => ['nullable', 'string', 'max:500'],
            'razorpay_webhook_secret' => ['nullable', 'string', 'max:500'],
            'google_maps_api_key' => ['nullable', 'string', 'max:500'],
            'firebase_server_key' => ['nullable', 'string', 'max:1000'],
            'firebase_service_account_json' => ['nullable', 'string', 'max:20000'],
            'firebase_project_id' => ['nullable', 'string', 'max:255'],
            'firebase_web_api_key' => ['nullable', 'string', 'max:500'],
            'firebase_app_id' => ['nullable', 'string', 'max:255'],
            'firebase_messaging_sender_id' => ['nullable', 'string', 'max:255'],
            'firebase_vapid_key' => ['nullable', 'string', 'max:1000'],
            'play_store_url' => ['nullable', 'url', 'max:500'],
            'app_store_url' => ['nullable', 'url', 'max:500'],
            'ga4_measurement_id' => ['nullable', 'string', 'max:100'],
            'google_search_console_code' => ['nullable', 'string', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:500'],
            'seo_meta_description' => ['nullable', 'string', 'max:1000'],
            'seo_meta_keywords' => ['nullable', 'string', 'max:1000'],
            'google_ads_tag_id' => ['nullable', 'string', 'max:100'],
            'google_ads_conversion_label' => ['nullable', 'string', 'max:100'],
            'google_ads_signup_label' => ['nullable', 'string', 'max:100'],
            'google_ads_room_view_label' => ['nullable', 'string', 'max:100'],
            'meta_pixel_id' => ['nullable', 'string', 'max:100'],
            'adsense_client_id' => ['nullable', 'string', 'max:100'],
            'adsense_home_top_id' => ['nullable', 'string', 'max:100'],
            'adsense_home_bottom_id' => ['nullable', 'string', 'max:100'],
            'adsense_room_content_id' => ['nullable', 'string', 'max:100'],
            'adsense_room_sidebar_id' => ['nullable', 'string', 'max:100'],
            'navbar_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'website_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'website_favicon' => ['nullable', 'file', 'mimes:ico,png', 'max:1024'],
            'owner_cta_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'default_hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'auth_modal_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'registration_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            // SMS Gateway
            'otp_delivery'  => ['nullable', 'in:email,phone,both'],
            'sms_gateway'   => ['nullable', 'in:log,msg91,twilio,fast2sms'],
            'sms_api_key'   => ['nullable', 'string', 'max:500'],
            'sms_sender_id' => ['nullable', 'string', 'max:100'],
            'sms_dlt_te_id' => ['nullable', 'string', 'max:100'],
            // Social Login
            'google_login_enabled' => ['nullable', 'boolean'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:500'],
            'google_redirect_url' => ['nullable', 'url', 'max:500'],
            'facebook_login_enabled' => ['nullable', 'boolean'],
            'facebook_client_id' => ['nullable', 'string', 'max:255'],
            'facebook_client_secret' => ['nullable', 'string', 'max:500'],
            'facebook_redirect_url' => ['nullable', 'url', 'max:500'],
            // Promo Popup Modal Settings
            'promo_modal_enabled' => ['nullable', 'boolean'],
            'promo_modal_audience' => ['nullable', 'in:guests_only,logged_in,all'],
            'promo_modal_type' => ['nullable', 'in:text_card,banner_image,both'],
            'promo_modal_badge' => ['nullable', 'string', 'max:100'],
            'promo_modal_title' => ['nullable', 'string', 'max:255'],
            'promo_modal_description' => ['nullable', 'string', 'max:1000'],
            'promo_modal_btn_text' => ['nullable', 'string', 'max:100'],
            'promo_modal_btn_url' => ['nullable', 'string', 'max:500'],
            'promo_modal_delay' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'promo_modal_cooldown_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'promo_modal_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];

        $activeTab = array_key_exists($request->input('_active_tab'), self::TAB_FIELDS)
            ? $request->input('_active_tab')
            : 'general';
        $activeFields = array_flip(self::TAB_FIELDS[$activeTab]);
        $data = $request->validate(array_intersect_key($rules, $activeFields));

        $brokerFeeEnabled = $request->boolean('broker_listing_fee_enabled');
        $brokerListingFee = $request->input('broker_listing_fee');

        foreach (array_intersect([
            'google_ads_enabled',
            'adsense_enabled',
            'meta_pixel_enabled',
            'listing_fee_enabled',
            'unlock_fee_enabled',
            'firebase_push_enabled',
            'google_login_enabled',
            'facebook_login_enabled',
            'promo_modal_enabled',
        ], self::TAB_FIELDS[$activeTab]) as $booleanKey) {
            $data[$booleanKey] = $request->boolean($booleanKey) ? '1' : '0';
        }

        \App\Models\BrokerSetting::set('broker_listing_charges_enabled', $request->boolean('broker_listing_fee_enabled') ? '1' : '0');
        if ($request->filled('broker_listing_fee')) {
            \App\Models\BrokerSetting::set('broker_per_listing_charge', $request->input('broker_listing_fee'));
        }

        foreach (['mail_password', 'razorpay_secret', 'razorpay_webhook_secret', 'firebase_server_key', 'firebase_service_account_json', 'sms_api_key', 'admin_access_key'] as $secretKey) {
            if (($data[$secretKey] ?? '') === '' || ($data[$secretKey] ?? '') === '••••••••••••') {
                unset($data[$secretKey]);
            }
        }

        if (!empty($data['admin_access_key'])) {
            $data['admin_access_key'] = bcrypt($data['admin_access_key']);
        }

        $newFiles = [];
        $oldFiles = [];

        try {
            foreach (['navbar_logo', 'footer_logo', 'website_logo', 'website_favicon', 'owner_cta_image', 'promo_modal_image', 'default_hero_image', 'auth_modal_image', 'registration_image'] as $fileKey) {
                if (! $request->hasFile($fileKey)) {
                    continue;
                }

                $path = $this->optimizeImage($request->file($fileKey), $fileKey);
                $newFiles[] = $path;
                $data[$fileKey] = $path;
            }

            DB::transaction(function () use ($data, $request, &$oldFiles): void {
                foreach ($data as $key => $value) {
                    $setting = Setting::where('key', $key)->first();
                    if ($request->hasFile($key) && $setting?->value) {
                        $oldFiles[] = trim((string) $setting->value);
                    }

                    Setting::updateOrCreate(
                        ['key' => $key],
                        [
                            'value' => $value,
                            'type' => $request->hasFile($key) ? 'image' : ($setting?->type ?? 'text'),
                            'group' => $setting?->group ?? 'general',
                        ]
                    );
                }

            });

            if ($activeTab === 'broker') {
                $brokerToggles = [
                    'broker_module_enabled',
                    'broker_verification_enabled',
                    'broker_listing_charges_enabled',
                    'broker_featured_enabled',
                    'broker_lead_charge_enabled',
                    'broker_future_brokerage_enabled',
                ];
                foreach ($brokerToggles as $bToggle) {
                    \App\Models\BrokerSetting::set($bToggle, $request->boolean($bToggle) ? '1' : '0');
                }

                $brokerInputs = [
                    'broker_per_listing_charge',
                    'broker_featured_charge',
                    'broker_listing_expiry_days',
                    'broker_free_listing_limit',
                    'broker_lead_charge',
                ];
                foreach ($brokerInputs as $bInput) {
                    if ($request->has($bInput)) {
                        \App\Models\BrokerSetting::set($bInput, (string) $request->input($bInput, '0'));
                    }
                }
            }

            foreach (array_unique($oldFiles) as $oldFile) {
                Storage::disk('public')->delete($oldFile);
            }

            Setting::clearCache();
        } catch (\Throwable $e) {
            foreach ($newFiles as $newFile) {
                Storage::disk('public')->delete($newFile);
            }
            report($e);

            return back()->withInput()->with('error', 'Unable to update settings. Please try again.');
        }

        $tab = in_array($activeTab, [
            'general','broker','appearance','payment','integrations','firebase','sms','seo','mail','referral','modal'
        ]) ? $activeTab : 'general';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Settings updated successfully!']);
        }

        return redirect(route('admin.settings') . '#' . $tab)->with('success', 'Settings updated successfully!');

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|unique:settings,key',
            'value' => 'required',
            'type' => 'required|in:text,number,image,boolean',
            'group' => 'required|string|max:100',
        ]);

        Setting::create($data);

        return back()->with('success', 'Setting added successfully!');
    }

    /**
     * Ping Search Engines to index the dynamic sitemap
     */
    public function pingSearchEngines()
    {
        $configuredUrl = trim((string) \App\Models\Setting::get('website_url', ''));
        $baseUrl = rtrim($configuredUrl !== '' ? $configuredUrl : url('/'), '/');
        $sitemapUrl = $baseUrl . '/sitemap.xml';
        $success = false;

        try {
            // Bing/IndexNow ping is the supported public method for sitemap submissions.
            \Illuminate\Support\Facades\Http::timeout(10)->get('https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl));

            // Additional fallback for a generic sitemap pings if a crawler supports it.
            \Illuminate\Support\Facades\Http::timeout(10)->get('https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl));

            $success = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Sitemap Ping Failed: '.$e->getMessage());
        }

        if ($success) {
            return back()->with('success', 'Search engines were notified successfully. Submit your sitemap in Google Search Console for full indexing verification.');
        }

        return back()->with('error', 'Failed to notify search engines. Please verify your production URL and try again later.');
    }

    private function optimizeImage($file, string $type): string
    {
        $preset = match ($type) {
            'navbar_logo' => 'logo',
            'footer_logo' => 'logo',
            'website_logo' => 'logo',
            'website_favicon' => 'favicon',
            'promo_modal_image' => 'offer_image',
            'default_hero_image' => 'default_hero',
            'auth_modal_image' => 'auth_modal',
            'registration_image' => 'auth_modal',
            default => 'logo',
        };

        return \App\Services\ImageOptimizer::optimize($file, $preset);
    }
}
