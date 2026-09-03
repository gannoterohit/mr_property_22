<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('agency_name')->nullable()->after('name');
            $table->string('agency_address')->nullable()->after('agency_name');
            $table->string('agency_gst')->nullable()->after('agency_address');
            $table->string('broker_license')->nullable()->after('agency_gst');
            $table->enum('broker_verification_status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending')->after('verification_status');
            $table->timestamp('broker_verified_at')->nullable()->after('broker_verification_status');
            $table->boolean('is_broker_active')->default(false)->after('verified_at');
            $table->timestamp('broker_approved_at')->nullable()->after('is_broker_active');
            $table->text('broker_rejected_reason')->nullable()->after('broker_approved_at');
            $table->integer('broker_total_listings')->default(0)->after('broker_rejected_reason');
            $table->integer('broker_active_listings')->default(0)->after('broker_total_listings');
            $table->integer('broker_featured_listings')->default(0)->after('broker_active_listings');
            $table->timestamp('broker_subscription_expires_at')->nullable()->after('broker_featured_listings');
            $table->integer('broker_subscription_listings_limit')->default(0)->after('broker_subscription_expires_at');
            $table->integer('broker_subscription_listings_used')->default(0)->after('broker_subscription_listings_limit');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'agency_name',
                'agency_address',
                'agency_gst',
                'broker_license',
                'broker_verification_status',
                'broker_verified_at',
                'is_broker_active',
                'broker_approved_at',
                'broker_rejected_reason',
                'broker_total_listings',
                'broker_active_listings',
                'broker_featured_listings',
                'broker_subscription_expires_at',
                'broker_subscription_listings_limit',
                'broker_subscription_listings_used',
            ]);
        });
    }
};
