<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Response;

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
        $data = $request->except(['_token', '_method']);

        foreach ([
            'google_ads_enabled',
            'adsense_enabled',
            'meta_pixel_enabled',
            'listing_fee_enabled',
            'unlock_fee_enabled',
        ] as $booleanKey) {
            $data[$booleanKey] = $request->boolean($booleanKey) ? '1' : '0';
        }
        
        // Remove helper text inputs (suffix _text) to avoid duplicate settings
        $data = array_filter($data, function($key) {
            return !str_ends_with($key, '_text');
        }, ARRAY_FILTER_USE_KEY);

        foreach ($data as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            
            // Handle file uploads (logo)
            if ($request->hasFile($key)) {
                // Delete old file if exists
                if ($setting && $setting->value) {
                    Storage::disk('public')->delete($setting->value);
                    // Also remove from public/storage folder
                    @unlink(public_path('storage/' . $setting->value));
                }
                
                // Store new file
                $path = $request->file($key)->store('settings', 'public');
                $value = $path;

                // XAMPP Windows fix: Copy file to public/storage (symlink may not work)
                $destDir = public_path('storage/' . dirname($path));
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            }
            
            if ($setting) {
                $setting->update(['value' => $value]);
            } else {
                // Create new setting if doesn't exist
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => $request->hasFile($key) ? 'image' : 'text',
                    'group' => 'general'
                ]);
            }
        }

        return back()->with('success', 'Settings updated successfully!');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|unique:settings,key',
            'value' => 'required',
            'type' => 'required|in:text,number,image,boolean',
            'group' => 'required',
        ]);

        Setting::create($request->all());

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
            \Illuminate\Support\Facades\Http::get('https://www.bing.com/ping?sitemap=' . urlencode($sitemapUrl));
            
            // Note: Google deprecated their public ping endpoint in late 2023.
            // IndexNow is the current recommended way for Bing/Yandex.
            // For Google, having the sitemap in robots.txt (already done) is the most reliable way.
            
            $success = true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Sitemap Ping Failed: ' . $e->getMessage());
        }

        if ($success) {
            return back()->with('success', 'Search engines notified successfully! Your new rooms will be indexed faster.');
        }

        return back()->with('error', 'Failed to notify search engines. Please try again later.');
    }
}
