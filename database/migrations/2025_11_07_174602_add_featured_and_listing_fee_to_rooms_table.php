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
            if (!Schema::hasColumn('rooms', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('photo');
            }
            if (!Schema::hasColumn('rooms', 'listing_fee_paid')) {
                $table->boolean('listing_fee_paid')->default(false)->after('is_featured');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('rooms', 'listing_fee_paid')) {
                $table->dropColumn('listing_fee_paid');
            }
        });
    }
};
