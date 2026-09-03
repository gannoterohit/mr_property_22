<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Indore', 'state' => 'Madhya Pradesh', 'is_active' => true, 'is_default' => true, 'latitude' => 22.7196, 'longitude' => 75.8577, 'sort_order' => 1],
            ['name' => 'Bhopal', 'state' => 'Madhya Pradesh', 'is_active' => false, 'is_default' => false, 'latitude' => 23.2599, 'longitude' => 77.4126, 'sort_order' => 2],
            ['name' => 'Mumbai', 'state' => 'Maharashtra', 'is_active' => false, 'is_default' => false, 'latitude' => 19.0760, 'longitude' => 72.8777, 'sort_order' => 3],
            ['name' => 'Pune', 'state' => 'Maharashtra', 'is_active' => false, 'is_default' => false, 'latitude' => 18.5204, 'longitude' => 73.8567, 'sort_order' => 4],
            ['name' => 'Delhi', 'state' => 'Delhi', 'is_active' => false, 'is_default' => false, 'latitude' => 28.7041, 'longitude' => 77.1025, 'sort_order' => 5],
            ['name' => 'Bangalore', 'state' => 'Karnataka', 'is_active' => false, 'is_default' => false, 'latitude' => 12.9716, 'longitude' => 77.5946, 'sort_order' => 6],
            ['name' => 'Hyderabad', 'state' => 'Telangana', 'is_active' => false, 'is_default' => false, 'latitude' => 17.3850, 'longitude' => 78.4867, 'sort_order' => 7],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['slug' => Str::slug($city['name'])],
                $city + ['slug' => Str::slug($city['name'])]
            );
        }
    }
}
