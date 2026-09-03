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
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'room_type')) {
                $table->dropColumn('room_type');
            }
            if (Schema::hasColumn('rooms', 'furnishing_type')) {
                $table->dropColumn('furnishing_type');
            }
            if (Schema::hasColumn('rooms', 'tenant_type')) {
                $table->dropColumn('tenant_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rooms')) {
            return;
        }

        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'furnishing_type')) {
                $table->enum('furnishing_type', ['furnished', 'semi-furnished', 'unfurnished'])->nullable();
            }
            if (!Schema::hasColumn('rooms', 'tenant_type')) {
                $table->enum('tenant_type', ['family', 'bachelors', 'girls', 'boys', 'any'])->default('any');
            }
            if (!Schema::hasColumn('rooms', 'room_type')) {
                $table->enum('room_type', ['single_room', 'shared_room', '1bhk', '2bhk', '3bhk', 'flat'])->nullable();
            }
        });
    }
};
