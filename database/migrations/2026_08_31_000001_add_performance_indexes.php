<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add composite indexes for rooms table for better query performance
        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->index(['status', 'listing_status', 'listing_fee_paid', 'city'], 'idx_rooms_public_listing');
                $table->index(['city', 'status', 'listing_status'], 'idx_rooms_city_status');
                $table->index(['property_type_id', 'status', 'listing_status'], 'idx_rooms_property_type');
                $table->index(['property_category_id', 'status', 'listing_status'], 'idx_rooms_property_category');
                $table->index(['tenant_option_id', 'status', 'listing_status'], 'idx_rooms_tenant_type');
                $table->index(['furnishing_option_id', 'status', 'listing_status'], 'idx_rooms_furnishing');
                $table->index(['user_id', 'status'], 'idx_rooms_user_status');
                $table->index(['created_at'], 'idx_rooms_created_at');
            });
        }

        // Add composite indexes for payments table
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['status', 'type', 'created_at'], 'idx_payments_status_type');
                $table->index(['user_id', 'status'], 'idx_payments_user_status');
                $table->index(['reference_id', 'type'], 'idx_payments_reference');
            });
        }

        // Add composite indexes for subscriptions table
        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index(['user_id', 'status', 'end_date'], 'idx_subscriptions_user_status');
            });
        }

        // Add composite indexes for broker_listing_credits table
        if (Schema::hasTable('broker_listing_credits')) {
            Schema::table('broker_listing_credits', function (Blueprint $table) {
                $table->index(['broker_id', 'credits_remaining', 'expires_at'], 'idx_credits_broker');
            });
        }

        // Add index for enquiries table
        if (Schema::hasTable('enquiries')) {
            Schema::table('enquiries', function (Blueprint $table) {
                $table->index(['user_id', 'room_id'], 'idx_enquiries_user_room');
                $table->index(['room_id', 'unlocked'], 'idx_enquiries_room_unlocked');
            });
        }

        // Add index for search_logs table
        if (Schema::hasTable('search_logs')) {
            Schema::table('search_logs', function (Blueprint $table) {
                $table->index(['ip_address', 'created_at'], 'idx_search_logs_ip_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rooms')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropIndex('idx_rooms_public_listing');
                $table->dropIndex('idx_rooms_city_status');
                $table->dropIndex('idx_rooms_property_type');
                $table->dropIndex('idx_rooms_property_category');
                $table->dropIndex('idx_rooms_tenant_type');
                $table->dropIndex('idx_rooms_furnishing');
                $table->dropIndex('idx_rooms_user_status');
                $table->dropIndex('idx_rooms_created_at');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex('idx_payments_status_type');
                $table->dropIndex('idx_payments_user_status');
                $table->dropIndex('idx_payments_reference');
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex('idx_subscriptions_user_status');
            });
        }

        if (Schema::hasTable('broker_listing_credits')) {
            Schema::table('broker_listing_credits', function (Blueprint $table) {
                $table->dropIndex('idx_credits_broker');
            });
        }

        if (Schema::hasTable('enquiries')) {
            Schema::table('enquiries', function (Blueprint $table) {
                $table->dropIndex('idx_enquiries_user_room');
                $table->dropIndex('idx_enquiries_room_unlocked');
            });
        }

        if (Schema::hasTable('search_logs')) {
            Schema::table('search_logs', function (Blueprint $table) {
                $table->dropIndex('idx_search_logs_ip_time');
            });
        }
    }
};
