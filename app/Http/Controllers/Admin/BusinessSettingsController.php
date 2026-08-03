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
        ]);

        foreach ([
            'google_ads_enabled',
            'adsense_enabled',
            'meta_pixel_enabled',
            'listing_fee_enabled',
            'unlock_fee_enabled',
            'firebase_push_enabled',
        ] as $booleanKey) {
            $data[$booleanKey] = $request->boolean($booleanKey) ? '1' : '0';
        }

        foreach (['mail_password', 'razorpay_secret', 'razorpay_webhook_secret', 'firebase_server_key'] as $secretKey) {
            if (($data[$secretKey] ?? '') === '') {
                unset($data[$secretKey]);
            }
        }

        $newFiles = [];
        $oldFiles = [];

        try {
            foreach (['navbar_logo', 'footer_logo', 'website_logo', 'website_favicon'] as $fileKey) {
                if (! $request->hasFile($fileKey)) {
                    continue;
                }

                $path = $request->file($fileKey)->store('settings', 'public');
                $newFiles[] = $path;
                $data[$fileKey] = $path;

                $destDir = public_path('storage/'.dirname($path));
                if (! is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                if (! copy(storage_path('app/public/'.$path), public_path('storage/'.$path))) {
                    throw new \RuntimeException('Unable to publish the uploaded settings image.');
                }
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

        return back()->with('success', 'Settings updated successfully!');
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
        $sitemapUrl = url('/sitemap.xml');
        $success = false;

        try {
            // Ping Bing / IndexNow (Modern Standard)
            \Illuminate\Support\Facades\Http::get('https://www.bing.com/ping?sitemap='.urlencode($sitemapUrl));

            // Note: Google deprecated their public ping endpoint in late 2023.
            // IndexNow is the current recommended way for Bing/Yandex.
            // For Google, having the sitemap in robots.txt (already done) is the most reliable way.

            $success = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Sitemap Ping Failed: '.$e->getMessage());
        }

        if ($success) {
            return back()->with('success', 'Search engines notified successfully! Your new rooms will be indexed faster.');
        }

        return back()->with('error', 'Failed to notify search engines. Please try again later.');
    }
}
