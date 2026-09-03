<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\DB;
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('how_it_works_items', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-circle-check');
            $table->string('badge')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
<<<<<<< HEAD
=======

        $now = now();
        DB::table('how_it_works_items')->insert([
            ['group' => 'hero_feature', 'title' => 'Room, PG and commercial listings', 'description' => null, 'icon' => 'fa-house', 'badge' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'hero_feature', 'title' => 'Admin reviewed details', 'description' => null, 'icon' => 'fa-circle-check', 'badge' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'hero_feature', 'title' => 'Contact unlock flow', 'description' => null, 'icon' => 'fa-address-card', 'badge' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'hero_feature', 'title' => 'Direct owner conversation', 'description' => null, 'icon' => 'fa-comments', 'badge' => null, 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'seeker_step', 'title' => 'Search and compare', 'description' => 'Filter by city, rent, property type, category, area, furnishing, tenant preference and facilities.', 'icon' => 'fa-magnifying-glass', 'badge' => 'Step 01', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'seeker_step', 'title' => 'Unlock owner contact', 'description' => 'Use a contact-plan credit, wallet balance or single online payment to reveal the complete owner number.', 'icon' => 'fa-lock-open', 'badge' => 'Step 02', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'seeker_step', 'title' => 'Call, visit and finalize', 'description' => 'Contact the owner directly, schedule a visit and independently confirm rent, deposit and rental terms.', 'icon' => 'fa-phone', 'badge' => 'Step 03', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'owner_step', 'title' => 'Create listing', 'description' => 'Add pricing, property type, category, square feet, facilities, location, photos and preferred tenant.', 'icon' => 'fa-file-pen', 'badge' => null, 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'owner_step', 'title' => 'Activate listing', 'description' => 'Use an owner listing credit, wallet balance or online payment to submit the property for approval.', 'icon' => 'fa-credit-card', 'badge' => null, 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['group' => 'owner_step', 'title' => 'Manage availability', 'description' => 'Mark a property booked to hide it, then reactivate it when it becomes available again.', 'icon' => 'fa-chart-line', 'badge' => null, 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $settings = [
            'hiw_hero_eyebrow' => 'Simple and transparent process',
            'hiw_hero_title' => 'Find the right property.',
            'hiw_hero_highlight' => 'Connect directly.',
            'hiw_hero_description' => 'ApnaNest helps users discover room, PG, shop, showroom and rental listings, then unlock verified owner contact details. Owners can publish and manage listings from one dashboard.',
            'hiw_primary_button_label' => 'Browse Properties',
            'hiw_secondary_button_label' => 'List a Property',
            'hiw_seeker_eyebrow' => 'For property seekers',
            'hiw_seeker_title' => 'From search to owner contact',
            'hiw_seeker_description' => 'No booking confusion. Choose a property, unlock the contact and speak directly with the owner.',
            'hiw_owner_eyebrow' => 'For property owners',
            'hiw_owner_title' => 'List and manage your properties',
            'hiw_owner_description' => 'Use a listing plan or single listing payment. Your property becomes public after entitlement validation and admin approval.',
            'hiw_owner_button_label' => 'View listing plans',
            'hiw_safety_title' => 'Visit and verify before finalizing',
            'hiw_safety_description' => 'ApnaNest provides listing discovery and contact access. Always visit the property, verify owner identity/documents and agree on rent, deposit and terms before paying the owner.',
            'hiw_safety_button_label' => 'Safety Tips',
        ];

        foreach ($settings as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'type' => 'text', 'group' => 'how_it_works', 'created_at' => $now, 'updated_at' => $now]
            );
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
        Schema::dropIfExists('how_it_works_items');
<<<<<<< HEAD
=======
        DB::table('settings')->where('group', 'how_it_works')->delete();
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};
