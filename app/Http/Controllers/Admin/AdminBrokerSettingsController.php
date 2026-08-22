<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokerSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminBrokerSettingsController extends Controller
{
    public function index(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.settings'), 403);

        $settings = BrokerSetting::all()->keyBy('key');

        return view('admin.broker-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.settings'), 403);

        $request->validate([
            'broker_module_enabled' => 'nullable|boolean',
            'broker_verification_enabled' => 'nullable|boolean',
            'broker_listing_charges_enabled' => 'nullable|boolean',
            'broker_subscription_enabled' => 'nullable|boolean',
            'broker_featured_enabled' => 'nullable|boolean',
            'broker_future_brokerage_enabled' => 'nullable|boolean',
            'broker_per_listing_charge' => 'nullable|numeric|min:0',
            'broker_featured_charge' => 'nullable|numeric|min:0',
            'broker_listing_expiry_days' => 'nullable|integer|min:1',
            'broker_subscription_expiry_days' => 'nullable|integer|min:1',
            'broker_free_listing_limit' => 'nullable|integer|min:0',
            'broker_lead_charge' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'broker_module_enabled',
            'broker_verification_enabled',
            'broker_listing_charges_enabled',
            'broker_subscription_enabled',
            'broker_featured_enabled',
            'broker_future_brokerage_enabled',
            'broker_per_listing_charge',
            'broker_featured_charge',
            'broker_listing_expiry_days',
            'broker_subscription_expiry_days',
            'broker_free_listing_limit',
            'broker_lead_charge',
        ]);

        foreach ($data as $key => $value) {
            if ($value === null) continue;
            BrokerSetting::set($key, $value);
        }

        return back()->with('success', 'Broker settings updated successfully.');
    }
}
