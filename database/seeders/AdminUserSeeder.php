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

<<<<<<< HEAD
        $adminPassword = env('ADMIN_PASSWORD');
        if (! is_string($adminPassword) || strlen($adminPassword) < 12) {
            throw new \RuntimeException('ADMIN_PASSWORD must be set in .env and contain at least 12 characters.');
        }

=======
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
        $superAdminRole = AdminRole::where('slug', 'super_admin')->first();

        User::updateOrCreate(
            ['email' => 'rohitgannote9009@gmail.com'],
            [
                'name' => 'Rohit',
<<<<<<< HEAD
                'password' => Hash::make($adminPassword),
=======
                'password' => Hash::make('Rohit9009@'),
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
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
