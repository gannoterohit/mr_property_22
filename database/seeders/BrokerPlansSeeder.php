<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BrokerPlan;

class BrokerPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'type' => 'monthly',
                'price' => 999,
                'max_listings' => 5,
                'duration_days' => 30,
                'is_featured_included' => false,
                'features' => ['Basic listing support', 'Standard visibility in search'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'type' => 'monthly',
                'price' => 2499,
                'max_listings' => 20,
                'duration_days' => 30,
                'is_featured_included' => false,
                'features' => ['Priority listing placement', 'Featured badge available', 'Basic lead insights'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'type' => 'yearly',
                'price' => 19999,
                'max_listings' => 100,
                'duration_days' => 365,
                'is_featured_included' => true,
                'features' => ['Featured listings included', 'Top search ranking', 'Dedicated support', 'Bulk property upload'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'type' => 'yearly',
                'price' => 49999,
                'max_listings' => 500,
                'duration_days' => 365,
                'is_featured_included' => true,
                'features' => ['Unlimited featured listings', 'Branded dashboard', 'API access', 'Dedicated account manager'],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pay Per Listing',
                'slug' => 'pay-per-listing',
                'type' => 'per_listing',
                'price' => 299,
                'max_listings' => null,
                'duration_days' => null,
                'is_featured_included' => false,
                'features' => ['One-time listing boost', 'Valid for 45 days', 'No commitment'],
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($plans as $plan) {
            BrokerPlan::create($plan);
        }
    }
}
