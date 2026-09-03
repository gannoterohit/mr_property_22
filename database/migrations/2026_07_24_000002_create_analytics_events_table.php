<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name', 60)->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('city', 120)->nullable()->index();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->nullable()->default('INR');
            $table->string('url', 1000)->nullable();
            $table->string('referrer', 1000)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
            $table->index(['city', 'created_at']);
            $table->index(['room_id', 'event_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
