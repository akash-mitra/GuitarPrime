<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AssignUserRolesSeeder::class,     // Create basic users with roles
            GuitarPrimeDataSeeder::class,     // Create themes and courses from JSON data
            CoachesCoursesSeeder::class,      // Create coaches with additional courses, modules, and attachments
        ]);
    }
}
