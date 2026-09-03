<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // credit, debit, refund, commission
            $table->string('category'); // listing, subscription, featured, lead, withdrawal
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->string('reference_type')->nullable(); // Payment, Subscription, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_transactions');
    }
};
