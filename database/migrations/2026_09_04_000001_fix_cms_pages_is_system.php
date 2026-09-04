<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set all CMS pages to is_system = false so they can be deleted
        DB::table('cms_pages')->update(['is_system' => false]);
    }

    public function down(): void
    {
        // Rollback if needed
    }
};
