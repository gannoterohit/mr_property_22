<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_featured_agency')) {
                $table->boolean('is_featured_agency')->default(false)->after('is_broker_active');
            }
            if (!Schema::hasColumn('users', 'featured_agency_expires_at')) {
                $table->timestamp('featured_agency_expires_at')->nullable()->after('is_featured_agency');
            }
            if (!Schema::hasColumn('users', 'broker_rating')) {
                $table->decimal('broker_rating', 3, 2)->default(5.00)->after('featured_agency_expires_at');
            }
            if (!Schema::hasColumn('users', 'broker_reviews_count')) {
                $table->unsignedInteger('broker_reviews_count')->default(0)->after('broker_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('users', 'is_featured_agency') ? 'is_featured_agency' : null,
                Schema::hasColumn('users', 'featured_agency_expires_at') ? 'featured_agency_expires_at' : null,
                Schema::hasColumn('users', 'broker_rating') ? 'broker_rating' : null,
                Schema::hasColumn('users', 'broker_reviews_count') ? 'broker_reviews_count' : null,
            ]);
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
