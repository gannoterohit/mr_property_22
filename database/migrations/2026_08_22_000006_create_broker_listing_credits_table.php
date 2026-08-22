<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_listing_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
            $table->integer('credits_remaining')->default(0);
            $table->integer('credits_purchased')->default(0);
            $table->string('source')->default('purchase'); // subscription, purchase, gift, referral
            $table->string('type')->default('listing'); // listing, featured
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_listing_credits');
    }
};
