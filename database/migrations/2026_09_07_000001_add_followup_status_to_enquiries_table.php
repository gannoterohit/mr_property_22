<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiries', 'status')) {
                $table->string('status', 30)->default('new')->after('unlocked');
            }
            if (!Schema::hasColumn('enquiries', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('enquiries', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('enquiries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
