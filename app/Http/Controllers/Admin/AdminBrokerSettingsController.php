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
        abort_if(!$admin->hasAdminPermission('brokers.settings') && !$admin->hasAdminPermission('settings.manage'), 403);

        return redirect(route('admin.settings') . '#broker');
    }

    public function update(Request $request)
    {
        $admin = $request->user();
        abort_if(!$admin->hasAdminPermission('brokers.settings') && !$admin->hasAdminPermission('settings.manage'), 403);

        $data = $request->only([
            'broker_module_enabled',
            'broker_verification_enabled',
            'broker_listing_charges_enabled',
            'broker_featured_enabled',
            'broker_future_brokerage_enabled',
            'broker_per_listing_charge',
            'broker_featured_charge',
            'broker_listing_expiry_days',
            'broker_free_listing_limit',
            'broker_lead_charge',
        ]);

        foreach ($data as $key => $value) {
            if ($value === null) continue;
            BrokerSetting::set($key, $value);
        }

        return redirect(route('admin.settings') . '#broker')->with('success', 'Broker settings updated successfully.');
    }
}
