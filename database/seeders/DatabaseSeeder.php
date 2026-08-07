<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * All seeders except RoomOptionSeeder run automatically.
     */
    public function run(): void
    {
        $this->call(SettingsSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(PropertyCatalogSeeder::class);
        $this->call(DummyDataSeeder::class);
        $this->call(AdminUserSeeder::class);
        $this->call(DummyActivitySeeder::class);
        $this->call(BlogSeeder::class);
    }
}
