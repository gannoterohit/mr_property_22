<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Upgrade offers table to full coupon engine
        Schema::table('offers', function (Blueprint $table) {
            // Remove old columns that are no longer needed
            $dropColumns = [];
            foreach (['image_path', 'link_url', 'placement', 'type', 'banner_color', 'discount_text'] as $col) {
                if (Schema::hasColumn('offers', $col)) $dropColumns[] = $col;
            }
            if (!empty($dropColumns)) $table->dropColumn($dropColumns);

            // Add new professional coupon fields only if they don't exist yet
            if (!Schema::hasColumn('offers', 'code'))
                $table->string('code', 30)->unique()->nullable()->after('title');
            if (!Schema::hasColumn('offers', 'discount_type'))
                $table->enum('discount_type', ['percentage', 'flat'])->default('percentage')->after('code');
            if (!Schema::hasColumn('offers', 'discount_value'))
                $table->decimal('discount_value', 10, 2)->default(0)->after('discount_type');
            if (!Schema::hasColumn('offers', 'max_discount_cap'))
                $table->decimal('max_discount_cap', 10, 2)->nullable()->after('discount_value')->comment('Max discount for percentage type');
            if (!Schema::hasColumn('offers', 'min_order_value'))
                $table->decimal('min_order_value', 10, 2)->default(0)->after('max_discount_cap');
            if (!Schema::hasColumn('offers', 'max_uses'))
                $table->unsignedInteger('max_uses')->nullable()->after('min_order_value')->comment('Null = unlimited');
            if (!Schema::hasColumn('offers', 'uses_count'))
                $table->unsignedInteger('uses_count')->default(0)->after('max_uses');
            if (!Schema::hasColumn('offers', 'per_user_limit'))
                $table->unsignedTinyInteger('per_user_limit')->default(1)->after('uses_count');
            if (!Schema::hasColumn('offers', 'applicable_for'))
                $table->enum('applicable_for', ['all', 'owner_plans', 'user_plans', 'broker_plans', 'unlocks'])->default('all')->after('per_user_limit');
            if (!Schema::hasColumn('offers', 'show_as_banner'))
                $table->boolean('show_as_banner')->default(false)->after('applicable_for')->comment('Show promo code as a top announcement banner');
        });

        // New coupon usage tracking table
        if (!Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('used_in_type')->nullable()->comment('payment, unlock, broker_payment');
                $table->unsignedBigInteger('used_in_id')->nullable()->comment('ID of the payment/unlock record');
                $table->decimal('original_amount', 10, 2);
                $table->decimal('discount_amount', 10, 2);
                $table->decimal('final_amount', 10, 2);
                $table->timestamps();
                $table->index(['offer_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'discount_type', 'discount_value', 'max_discount_cap',
                'min_order_value', 'max_uses', 'uses_count', 'per_user_limit',
                'applicable_for', 'show_as_banner',
            ]);

            // Restore old columns
            $table->string('image_path')->nullable();
            $table->string('link_url')->nullable();
            $table->string('placement')->default('dashboard');
            $table->string('type')->default('text_only');
            $table->string('banner_color')->default('#4F46E5');
            $table->string('discount_text', 50)->nullable();
        });
    }
};
