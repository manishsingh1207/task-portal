<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        // Create Admin
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );

        $user->assignRole($admin);
    }
}
