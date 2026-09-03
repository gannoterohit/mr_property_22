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
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'photos')) {
                $table->json('photos')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('rooms', 'video')) {
                $table->string('video')->nullable()->after('video_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'photos')) {
                $table->dropColumn('photos');
            }
            if (Schema::hasColumn('rooms', 'video')) {
                $table->dropColumn('video');
            }
        });
    }
};
