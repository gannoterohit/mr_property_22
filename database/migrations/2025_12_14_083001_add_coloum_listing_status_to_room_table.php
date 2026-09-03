<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'listing_status')) {
                $table->enum('listing_status', ['pending', 'approved', 'rejected'])
                      ->default('pending')
                      ->after('status');
            }

            if (!Schema::hasColumn('rooms', 'listing_reason')) {
                $table->text('listing_reason')
                      ->nullable()
                      ->after('listing_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'listing_reason')) {
                $table->dropColumn('listing_reason');
            }

            if (Schema::hasColumn('rooms', 'listing_status')) {
                $table->dropColumn('listing_status');
            }
        });
    }
};
