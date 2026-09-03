<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Plan;

class BrokerPlansForAdminSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'price' => 999,
                'duration_days' => 30,
                'listing_limit' => 5,
                'contacts_limit' => 0,
                'type' => 'broker',
                'benefits' => ['Basic listing support', 'Standard visibility in search'],
                'is_active' => true,
            ],
            [
                'name' => 'Growth',
                'price' => 2499,
                'duration_days' => 30,
                'listing_limit' => 20,
                'contacts_limit' => 0,
                'type' => 'broker',
                'benefits' => ['Priority listing placement', 'Featured badge available', 'Basic lead insights'],
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'price' => 19999,
                'duration_days' => 365,
                'listing_limit' => 100,
                'contacts_limit' => 0,
                'type' => 'broker',
                'benefits' => ['Featured listings included', 'Top search ranking', 'Dedicated support', 'Bulk property upload'],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 49999,
                'duration_days' => 365,
                'listing_limit' => 500,
                'contacts_limit' => 0,
                'type' => 'broker',
                'benefits' => ['Unlimited featured listings', 'Branded dashboard', 'API access', 'Dedicated account manager'],
                'is_active' => true,
            ],
            [
                'name' => 'Pay Per Listing',
                'price' => 299,
                'duration_days' => 45,
                'listing_limit' => 1,
                'contacts_limit' => 0,
                'type' => 'broker',
                'benefits' => ['One-time listing boost', 'Valid for 45 days', 'No commitment'],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
