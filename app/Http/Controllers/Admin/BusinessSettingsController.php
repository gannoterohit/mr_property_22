<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsController extends Controller
{
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
        $data = $request->validate([
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
        ]);

        $brokerFeeEnabled = $request->boolean('broker_listing_fee_enabled');
        $brokerListingFee = $request->input('broker_listing_fee');

        foreach ([
            'google_ads_enabled',
            'adsense_enabled',
            'meta_pixel_enabled',
            'listing_fee_enabled',
            'unlock_fee_enabled',
            'firebase_push_enabled',
            'google_login_enabled',
            'facebook_login_enabled',
            'promo_modal_enabled',
        ] as $booleanKey) {
            $data[$booleanKey] = $request->boolean($booleanKey) ? '1' : '0';
        }

        \App\Models\BrokerSetting::set('broker_listing_charges_enabled', $request->boolean('broker_listing_fee_enabled') ? '1' : '0');
        if ($request->filled('broker_listing_fee')) {
            \App\Models\BrokerSetting::set('broker_per_listing_charge', $request->input('broker_listing_fee'));
        }

        foreach (['mail_password', 'razorpay_secret', 'razorpay_webhook_secret', 'firebase_server_key', 'sms_api_key'] as $secretKey) {
            if (($data[$secretKey] ?? '') === '') {
                unset($data[$secretKey]);
            }
        }

        $newFiles = [];
        $oldFiles = [];

        try {
            foreach (['navbar_logo', 'footer_logo', 'website_logo', 'website_favicon', 'owner_cta_image', 'promo_modal_image'] as $fileKey) {
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
                        $oldFiles[] = $setting->value;
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

            if ($request->input('_active_tab') === 'broker' || $request->has('broker_module_enabled') || $request->has('broker_per_listing_charge')) {
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
            } elseif ($request->has('broker_listing_fee_enabled') || $request->has('broker_listing_fee')) {
                \App\Models\BrokerSetting::set('broker_listing_charges_enabled', $request->boolean('broker_listing_fee_enabled') ? '1' : '0');
                if ($request->filled('broker_listing_fee')) {
                    \App\Models\BrokerSetting::set('broker_per_listing_charge', (string) $request->input('broker_listing_fee'));
                }
            }

            foreach (array_unique($oldFiles) as $oldFile) {
                Storage::disk('public')->delete($oldFile);
            }
        } catch (\Throwable $e) {
            foreach ($newFiles as $newFile) {
                Storage::disk('public')->delete($newFile);
            }
            report($e);

            return back()->withInput()->with('error', 'Unable to update settings. Please try again.');
        }

        $tab = in_array($request->input('_active_tab'), [
            'general','broker','appearance','payment','integrations','firebase','sms','seo','mail','referral','modal'
        ]) ? $request->input('_active_tab') : 'general';

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
            default => 'logo',
        };

        return \App\Services\ImageOptimizer::optimize($file, $preset);
    }
}
