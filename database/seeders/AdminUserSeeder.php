<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AdminRoleSeeder::class);

        $superAdminRole = AdminRole::where('slug', 'super_admin')->first();

        User::updateOrCreate(
            ['email' => 'rohitgannote9009@gmail.com'],
            [
                'name' => 'Rohit',
                'password' => Hash::make('Rohit9009@'),
                'role' => 'admin',
                'is_staff_active' => true,
                'email_verified_at' => now(),
                'is_verified' => true,
                'verification_status' => 'verified',
                'verified_at' => now(),
                'admin_role_id' => $superAdminRole?->id,
            ]
        );
    }
}
