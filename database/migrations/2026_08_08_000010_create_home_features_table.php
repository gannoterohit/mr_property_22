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
        Schema::create('home_features', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('icon')->default('fa-circle-check');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

<<<<<<< HEAD
        // Home features are managed from the admin panel.
=======
        $now = now();
        DB::table('home_features')->insert([
            ['title' => 'Verified listings', 'description' => 'Every live room is reviewed before it appears on the website.', 'icon' => 'fa-shield-halved', 'sort_order' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Direct owner contact', 'description' => 'Tenants can connect with owners without unnecessary middle steps.', 'icon' => 'fa-phone-volume', 'sort_order' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Local search first', 'description' => 'City, area and property filters help users find relevant rooms faster.', 'icon' => 'fa-location-dot', 'sort_order' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Clear property details', 'description' => 'Rent, sqft, furnishing, tenant preference and location stay visible upfront.', 'icon' => 'fa-list-check', 'sort_order' => 4, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Owner-friendly posting', 'description' => 'Owners can add rooms, photos and practical details from one dashboard.', 'icon' => 'fa-house-user', 'sort_order' => 5, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Safer decisions', 'description' => 'Helpful support, complaint flow and verified data reduce confusion.', 'icon' => 'fa-handshake-angle', 'sort_order' => 6, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
        Schema::dropIfExists('home_features');
    }
};
