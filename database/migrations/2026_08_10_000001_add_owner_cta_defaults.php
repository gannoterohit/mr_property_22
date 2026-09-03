<?php

use Illuminate\Database\Migrations\Migration;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\DB;
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        // Homepage content is managed from the admin panel.
=======
        $now = now();

        DB::table('how_it_works_items')->insert([
            [
                'group' => 'owner_cta',
                'title' => 'Easy listing in under 5 minutes',
                'description' => 'Add pricing, photos, location and property details in just a few clicks.',
                'icon' => 'fa-bolt',
                'badge' => null,
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'owner_cta',
                'title' => 'Verified enquiries from real seekers',
                'description' => 'Get genuine contacts from users who are actually looking for a property.',
                'icon' => 'fa-user-check',
                'badge' => null,
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'owner_cta',
                'title' => 'Direct tenant contact — no broker needed',
                'description' => 'Connect with tenants directly and avoid middleman delays.',
                'icon' => 'fa-phone',
                'badge' => null,
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'group' => 'owner_cta',
                'title' => 'Simple plans starting at affordable rates',
                'description' => 'Choose a listing plan that fits your budget and start receiving enquiries.',
                'icon' => 'fa-tag',
                'badge' => null,
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $settings = [
            'owner_cta_eyebrow' => 'For property owners',
            'owner_cta_title' => 'Have a property to rent?',
            'owner_cta_description' => 'List your room, flat, PG, shop or office on ApnaNest and connect with genuine tenants or buyers directly. No middlemen, no delays.',
            'owner_cta_button_label' => 'List Your Property',
            'owner_cta_image' => '',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'type' => 'text', 'group' => 'homepage', 'created_at' => $now, 'updated_at' => $now]
            );
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
<<<<<<< HEAD
        // Homepage content is managed from the admin panel.
=======
        DB::table('how_it_works_items')->where('group', 'owner_cta')->delete();
        DB::table('settings')->where('key', 'like', 'owner_cta_%')->delete();
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};
