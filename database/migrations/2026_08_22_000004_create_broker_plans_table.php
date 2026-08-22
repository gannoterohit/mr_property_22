<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broker_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('monthly'); // monthly, yearly, per_listing
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('max_listings')->nullable();
            $table->integer('duration_days')->nullable(); // validity in days
            $table->boolean('is_featured_included')->default(false);
            $table->text('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broker_plans');
    }
};
