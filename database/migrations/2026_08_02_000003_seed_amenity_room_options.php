<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        DB::table('room_options')->where('group', 'amenity')->delete();
    }
};
