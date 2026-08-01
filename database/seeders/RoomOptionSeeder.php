<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('room_options')) {
            return;
        }

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

        $amenityOrder = 0;
        foreach (\App\Models\RoomOption::fallbackOptionsFor('amenity') as $key => $label) {
            $defaults[] = ['group' => 'amenity', 'key' => $key, 'label' => $label, 'sort_order' => ++$amenityOrder];
        }

        foreach ($defaults as $option) {
            DB::table('room_options')->updateOrInsert(
                ['group' => $option['group'], 'key' => $option['key']],
                array_merge($option, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
