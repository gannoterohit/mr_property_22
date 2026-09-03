<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('description');
            $table->string('link_url')->nullable()->after('image_path');
            $table->string('placement')->default('dashboard')->after('link_url'); // top_nav, home_hero, dashboard, sidebar
            $table->string('type')->default('text_only')->after('placement'); // text_only, image_only, both
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'link_url', 'placement', 'type']);
        });
    }
};
