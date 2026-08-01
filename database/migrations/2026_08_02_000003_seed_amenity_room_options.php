<?php

use App\Models\RoomOption;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (RoomOption::fallbackOptionsFor('amenity') as $key => $label) {
            RoomOption::query()->firstOrCreate(
                ['group' => 'amenity', 'key' => $key],
                ['label' => $label, 'sort_order' => array_search($key, array_keys(RoomOption::fallbackOptionsFor('amenity')), true) + 1, 'is_active' => true]
            );
        }
    }

    public function down(): void
    {
        RoomOption::query()->where('group', 'amenity')->delete();
    }
};
