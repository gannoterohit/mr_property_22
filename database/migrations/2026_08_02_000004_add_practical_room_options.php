<?php

use Illuminate\Database\Migrations\Migration;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\DB;
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        // Room options are managed from the admin panel.
=======
        $options = [
            'room_type' => [
                'private_room' => 'Private Room', '1rk' => '1 RK',
                'studio_apartment' => 'Studio Apartment', '4bhk_plus' => '4 BHK or More',
                'pg' => 'PG', 'hostel' => 'Hostel', 'independent_house' => 'Independent House', 'villa' => 'Villa',
            ],
            'tenant_type' => [
                'working_professionals' => 'Working Professionals', 'students' => 'Students', 'couples' => 'Couples',
            ],
            'amenity' => [
                'attached_bathroom' => 'Attached Bathroom', 'balcony' => 'Balcony', 'bed' => 'Bed',
                'wardrobe' => 'Wardrobe', 'study_table' => 'Study Table', 'refrigerator' => 'Refrigerator',
                'washing_machine' => 'Washing Machine', 'ro_water' => 'RO Water',
                'gas_connection' => 'Gas Connection', 'microwave' => 'Microwave',
                'housekeeping' => 'Housekeeping', 'meals' => 'Meals Available',
                'common_area' => 'Common Area', 'garden' => 'Garden', 'fire_safety' => 'Fire Safety',
                'intercom' => 'Intercom', 'pet_friendly' => 'Pet Friendly',
                'two_wheeler_parking' => 'Two-Wheeler Parking', 'four_wheeler_parking' => 'Four-Wheeler Parking',
            ],
        ];

        foreach ($options as $group => $groupOptions) {
            $nextOrder = (int) DB::table('room_options')->where('group', $group)->max('sort_order');
            foreach ($groupOptions as $key => $label) {
                DB::table('room_options')->insertOrIgnore([
                    'group' => $group, 'key' => $key, 'label' => $label,
                    'sort_order' => ++$nextOrder, 'is_active' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
<<<<<<< HEAD
        // Room options are managed from the admin panel.
=======
        $addedKeys = [
            'room_type' => ['private_room', '1rk', 'studio_apartment', '4bhk_plus', 'pg', 'hostel', 'independent_house', 'villa'],
            'tenant_type' => ['working_professionals', 'students', 'couples'],
            'amenity' => ['attached_bathroom', 'balcony', 'bed', 'wardrobe', 'study_table', 'refrigerator', 'washing_machine', 'ro_water', 'gas_connection', 'microwave', 'housekeeping', 'meals', 'common_area', 'garden', 'fire_safety', 'intercom', 'pet_friendly', 'two_wheeler_parking', 'four_wheeler_parking'],
        ];

        foreach ($addedKeys as $group => $keys) {
            DB::table('room_options')
                ->where('group', $group)
                ->whereIn('key', $keys)
                ->delete();
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};
