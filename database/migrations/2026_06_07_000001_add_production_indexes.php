<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['city', 'status', 'listing_status'], 'rooms_city_status_listing_idx');
            $table->index(['is_featured', 'created_at'], 'rooms_featured_created_idx');
            $table->index('rent', 'rooms_rent_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'payments_user_status_idx');
            $table->index('transaction_id', 'payments_transaction_id_idx');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->index(['user_id', 'room_id'], 'enquiries_user_room_idx');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('rooms_city_status_listing_idx');
            $table->dropIndex('rooms_featured_created_idx');
            $table->dropIndex('rooms_rent_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_user_status_idx');
            $table->dropIndex('payments_transaction_id_idx');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropIndex('enquiries_user_room_idx');
        });
    }
};
