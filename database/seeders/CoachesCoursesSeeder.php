<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoachesCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Mapping existing coaches to existing courses and modules...');

        // Get existing courses and modules
        $courses = Course::all();
        $modules = Module::all();

        if ($courses->isEmpty()) {
            $this->command->error('No courses found. Please run GuitarPrimeDataSeeder first.');

            return;
        }

        if ($modules->isEmpty()) {
            $this->command->error('No modules found. Please run GuitarPrimeDataSeeder first.');

            return;
        }

        // Find existing coaches with role 'coach'
        $existingCoaches = User::where('role', 'coach')->get();

        if ($existingCoaches->isEmpty()) {
            $this->command->error('No coaches found in the database. Please create coaches first.');

            return;
        }

        $this->command->info("Found {$existingCoaches->count()} existing coaches");
        $this->command->info("Found {$courses->count()} existing courses");
        $this->command->info("Found {$modules->count()} existing modules");

        // Map each coach to all courses and modules
        foreach ($existingCoaches as $coach) {
            $this->command->info("Mapping coach: {$coach->name} to all courses and modules");

            // Update all courses to be associated with this coach
            foreach ($courses as $course) {
                $course->update(['coach_id' => $coach->id]);
            }

            // Update all modules to be associated with this coach
            foreach ($modules as $module) {
                $module->update(['coach_id' => $coach->id]);
            }
        }

        // Create some additional students if needed
        $existingStudents = User::where('role', 'student')->count();
        if ($existingStudents < 10) {
            $studentsToCreate = 10 - $existingStudents;
            User::factory()->count($studentsToCreate)->create([
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
            $this->command->info("Created {$studentsToCreate} additional students");
        }

        $this->command->info('Coach mapping completed successfully!');
        $this->command->info('Summary:');
        $this->command->info("- {$existingCoaches->count()} coaches mapped to all courses and modules");
        $this->command->info("- {$courses->count()} courses now associated with coaches");
        $this->command->info("- {$modules->count()} modules now associated with coaches");
    }
}
