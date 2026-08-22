<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('broker_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
            $table->enum('listed_by', ['owner', 'broker'])->default('owner')->after('broker_id');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
            $table->dropColumn(['broker_id', 'listed_by']);
        });
    }
};
