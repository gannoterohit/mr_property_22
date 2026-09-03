<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY type ENUM('listing','broker_listing','subscription','booking','unlock','featured') NOT NULL DEFAULT 'booking'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY type ENUM('listing','subscription','booking','unlock','featured') NOT NULL DEFAULT 'booking'");
    }
};