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
            if (!Schema::hasColumn('rooms', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('rooms', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'country')) {
                $table->dropColumn('country');
            }
            if (Schema::hasColumn('rooms', 'state')) {
                $table->dropColumn('state');
            }
        });
    }
};
