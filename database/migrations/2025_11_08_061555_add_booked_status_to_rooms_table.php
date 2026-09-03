<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        // Modify enum to include 'booked'
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('pending','active','inactive','booked') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        // Revert back to original enum
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('pending','active','inactive') DEFAULT 'pending'");
    }
};
