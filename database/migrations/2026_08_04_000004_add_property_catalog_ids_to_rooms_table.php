<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('property_type_id')->nullable()->after('status')->constrained('property_types')->nullOnDelete();
            $table->foreignId('property_category_id')->nullable()->after('property_type_id')->constrained('property_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_category_id');
            $table->dropConstrainedForeignId('property_type_id');
        });
    }
};
