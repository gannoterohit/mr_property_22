<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'listing_payment_id')) {
                $table->unsignedBigInteger('listing_payment_id')->nullable()->after('listing_fee_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'listing_payment_id')) {
                $table->dropColumn('listing_payment_id');
            }
        });
    }
};
