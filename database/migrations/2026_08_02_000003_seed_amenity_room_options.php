<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
<<<<<<< HEAD
        // Room options are managed from the admin panel.
=======
        $amenities = [
            'wifi' => 'Wifi', 'ac' => 'AC', 'tv' => 'TV', 'geyser' => 'Geyser',
            'cooler' => 'Cooler', 'parking' => 'Parking', 'kitchen' => 'Kitchen',
            'cleaning' => 'Cleaning', 'laundry' => 'Laundry', 'power_backup' => 'Power Backup',
            'cctv' => 'CCTV', 'lift' => 'Lift', 'security' => 'Security',
            'water_supply' => 'Water Supply', 'gym' => 'Gym',
            'swimming_pool' => 'Swimming Pool', 'clubhouse' => 'Clubhouse',
        ];

        foreach ($amenities as $key => $label) {
            DB::table('room_options')->insertOrIgnore(
                ['group' => 'amenity', 'key' => $key, 'label' => $label, 'sort_order' => array_search($key, array_keys($amenities), true) + 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public function down(): void
    {
<<<<<<< HEAD
        // No records are created by this migration.
=======
        DB::table('room_options')->where('group', 'amenity')->delete();
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
};
