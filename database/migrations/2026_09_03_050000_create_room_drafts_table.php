<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->unsignedTinyInteger('step')->default(1);
            $table->json('data')->nullable();
            $table->json('photos')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_published']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_drafts');
    }
};
