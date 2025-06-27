<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssignUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign first user as admin (if exists)
        $firstUser = User::first();
        if ($firstUser && $firstUser->role === 'student') {
            $firstUser->update(['role' => 'admin']);
            $this->command->info("Assigned admin role to: {$firstUser->email}");
        }

        // Update any existing users without roles to 'student' (default)
        $usersWithoutRoles = User::whereNull('role')->get();
        foreach ($usersWithoutRoles as $user) {
            $user->update(['role' => 'student']);
        }

        if ($usersWithoutRoles->count() > 0) {
            $this->command->info("Assigned student role to {$usersWithoutRoles->count()} users");
        }

        // Create sample roles if no users exist
        if (User::count() === 0) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@guitarprime.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            User::create([
                'name' => 'Coach User',
                'email' => 'coach@guitarprime.com',
                'password' => bcrypt('password'),
                'role' => 'coach',
                'email_verified_at' => now(),
            ]);

            User::create([
                'name' => 'Student User',
                'email' => 'student@guitarprime.com',
                'password' => bcrypt('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);

            $this->command->info('Created sample users for all roles');
        }
    }
}
