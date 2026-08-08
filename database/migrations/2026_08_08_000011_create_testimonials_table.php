<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('city')->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar')->nullable();
            $table->string('status')->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('testimonials')->insert([
            ['name' => 'Rohit Sharma', 'role' => 'Tenant', 'city' => 'Indore', 'message' => 'I found a clean room near my office and contacted the owner directly. The filters saved a lot of time.', 'rating' => 5, 'status' => 'active', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Priya Verma', 'role' => 'Owner', 'city' => 'Vijay Nagar', 'message' => 'Posting my property was simple. The dashboard keeps enquiries and listing details easy to manage.', 'rating' => 5, 'status' => 'active', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Amit Jain', 'role' => 'Tenant', 'city' => 'Rajwada', 'message' => 'The listing details were clear, especially rent, location and room type. It helped me shortlist faster.', 'rating' => 4, 'status' => 'active', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
