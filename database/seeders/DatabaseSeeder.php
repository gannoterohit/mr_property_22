<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Settings
        $this->call(SettingsSeeder::class);

        // Seed operational cities
        $this->call(CitySeeder::class);

        // Seed Room Options
        $this->call(RoomOptionSeeder::class);

        // Seed property catalog defaults
        $this->call(PropertyCatalogSeeder::class);

        // Seed Dummy Data
        $this->call(DummyDataSeeder::class);
        $this->call([
            DummyActivitySeeder::class,
            BlogSeeder::class,
        ]);
    }
}
