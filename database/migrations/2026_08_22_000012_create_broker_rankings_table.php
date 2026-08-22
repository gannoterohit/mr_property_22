<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->decimal('score', 5, 2)->default(0); // 0-100
            $table->integer('total_deals')->default(0);
            $table->decimal('total_deal_value', 12, 2)->default(0);
            $table->integer('total_reviews')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->integer('response_time_minutes')->nullable();
            $table->integer('rank')->nullable();
            $table->json('badges')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('broker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_rankings');
    }
};
