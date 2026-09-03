<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('admin_permissions.roles') as $slug => [$name, $description, $permissions]) {
            AdminRole::updateOrCreate(['slug' => $slug], compact('name', 'description', 'permissions') + ['is_system' => true]);
        }

        $superAdmin = AdminRole::where('slug', 'super_admin')->firstOrFail();
        User::where('role', 'admin')->whereNull('admin_role_id')->update([
            'admin_role_id' => $superAdmin->id,
            'is_staff_active' => true,
        ]);
    }
}
