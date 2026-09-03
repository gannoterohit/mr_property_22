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
        // Drop if exists to ensure clean slate with correct columns
        Schema::dropIfExists('search_logs');

        Schema::create('search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('city')->nullable(); // Specifically requested
            $table->string('search_term')->nullable(); // General search
            $table->json('filters')->nullable(); // Other filters like rent range
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('city');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_logs');
    }
};
