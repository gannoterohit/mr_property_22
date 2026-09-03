<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class BrokerSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Module ON/OFF Controls
            [
                'key' => 'broker_module_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'broker',
                'description' => 'Enable/disable entire broker module',
            ],
            [
                'key' => 'broker_verification_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'broker',
                'description' => 'Enable/disable broker verification workflow',
            ],
            [
                'key' => 'broker_listing_charges_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'broker',
                'description' => 'Enable/disable per-listing charges for brokers',
            ],
            [
                'key' => 'broker_featured_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'broker',
                'description' => 'Enable/disable featured listing for brokers',
            ],
            [
                'key' => 'broker_future_brokerage_enabled',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'broker',
                'description' => 'Enable/disable future brokerage system',
            ],

            // Pricing
            [
                'key' => 'broker_per_listing_charge',
                'value' => '199',
                'type' => 'number',
                'group' => 'broker',
                'description' => 'Per listing charge for brokers (INR)',
            ],
            [
                'key' => 'broker_featured_charge',
                'value' => '99',
                'type' => 'number',
                'group' => 'broker',
                'description' => 'Featured listing charge for brokers (INR)',
            ],
            [
                'key' => 'broker_listing_expiry_days',
                'value' => '30',
                'type' => 'number',
                'group' => 'broker',
                'description' => 'Default listing expiry in days',
            ],
            [
                'key' => 'broker_free_listing_limit',
                'value' => '0',
                'type' => 'number',
                'group' => 'broker',
                'description' => 'Free listing limit per new broker (0 = none)',
            ],
            [
                'key' => 'broker_lead_charge',
                'value' => '0',
                'type' => 'number',
                'group' => 'broker',
                'description' => 'Charge per lead/enquiry (0 = free)',
            ],
        ];

        foreach ($settings as $setting) {
            \App\Models\BrokerSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
