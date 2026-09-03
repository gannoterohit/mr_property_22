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
            $table->enum('furnishing_type', ['furnished', 'semi-furnished', 'unfurnished'])->nullable()->after('listing_fee_paid');
            $table->enum('tenant_type', ['family', 'bachelors', 'girls', 'boys', 'any'])->default('any')->after('furnishing_type');
            $table->enum('room_type', ['single_room', 'shared_room', '1bhk', '2bhk', '3bhk', 'flat'])->nullable()->after('tenant_type');
            $table->json('amenities')->nullable()->after('room_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['furnishing_type', 'tenant_type', 'room_type', 'amenities']);
        });
    }
};
