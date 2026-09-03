<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_options', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('key')->unique();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

<<<<<<< HEAD
        // Room options are managed from the admin panel.
=======
        $defaults = [
            ['group' => 'room_type', 'key' => 'single_room', 'label' => 'Single Room', 'sort_order' => 1],
            ['group' => 'room_type', 'key' => 'shared_room', 'label' => 'Shared Room', 'sort_order' => 2],
            ['group' => 'room_type', 'key' => '1bhk', 'label' => '1 BHK', 'sort_order' => 3],
            ['group' => 'room_type', 'key' => '2bhk', 'label' => '2 BHK', 'sort_order' => 4],
            ['group' => 'room_type', 'key' => '3bhk', 'label' => '3 BHK', 'sort_order' => 5],
            ['group' => 'room_type', 'key' => 'flat', 'label' => 'Full Flat', 'sort_order' => 6],
            ['group' => 'furnishing_type', 'key' => 'furnished', 'label' => 'Fully Furnished', 'sort_order' => 1],
            ['group' => 'furnishing_type', 'key' => 'semi-furnished', 'label' => 'Semi Furnished', 'sort_order' => 2],
            ['group' => 'furnishing_type', 'key' => 'unfurnished', 'label' => 'Unfurnished', 'sort_order' => 3],
            ['group' => 'tenant_type', 'key' => 'any', 'label' => 'Any / All', 'sort_order' => 1],
            ['group' => 'tenant_type', 'key' => 'family', 'label' => 'Family', 'sort_order' => 2],
            ['group' => 'tenant_type', 'key' => 'bachelors', 'label' => 'Bachelors', 'sort_order' => 3],
            ['group' => 'tenant_type', 'key' => 'girls', 'label' => 'Girls Only', 'sort_order' => 4],
            ['group' => 'tenant_type', 'key' => 'boys', 'label' => 'Boys Only', 'sort_order' => 5],
        ];

        foreach ($defaults as $option) {
            DB::table('room_options')->insertOrIgnore(array_merge($option, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]));
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
        Schema::dropIfExists('room_options');
    }
};
