<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuitarPrimeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating Guitar Prime themes and courses...');

        // Load JSON data from the example.json file
        $jsonPath = __DIR__.'/example.json';
        $data = json_decode(file_get_contents($jsonPath), true);

        // Create admin user to assign as coach for these courses
        $adminCoach = User::firstOrCreate(
            ['email' => 'admin@guitarprime.com'],
            [
                'name' => 'Guitar Prime Admin',
                'role' => 'admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        foreach ($data['themes'] as $themeData) {
            $this->command->info("Creating theme: {$themeData['name']}");

            $theme = Theme::create([
                'name' => $themeData['name'],
                'description' => $themeData['description'] ?? "Collection of courses focused on {$themeData['name']} guitar techniques and styles.",
                'cover_image' => $themeData['cover_image'] ?? 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=800&q=80',
            ]);

            foreach ($themeData['courses'] as $courseData) {
                $this->command->info("  Creating course: {$courseData['name']}");

                $course = Course::create([
                    'theme_id' => $theme->id,
                    'coach_id' => $adminCoach->id,
                    'title' => $courseData['name'],
                    'description' => $courseData['description'],
                    'is_approved' => true,
                    'cover_image' => $courseData['cover_image'] ?? 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
                ]);

                // Create modules for this course if they exist
                if (isset($courseData['modules']) && is_array($courseData['modules'])) {
                    foreach ($courseData['modules'] as $index => $moduleData) {
                        $this->command->info("    Creating module: {$moduleData['name']}");

                        $module = Module::create([
                            'coach_id' => $adminCoach->id,
                            'title' => $moduleData['name'],
                            'description' => $moduleData['description'] ?? '',
                            'video_url' => $moduleData['video_url'] ?? null,
                            'difficulty' => 'easy', // Default difficulty - must be 'easy', 'medium', or 'hard'
                            'is_free' => true, // Default to free
                            'cover_image' => $moduleData['cover_image'] ?? 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80',
                        ]);

                        // Attach the module to the course
                        $course->modules()->attach($module->id, ['order' => $index + 1]);
                    }
                }
            }
        }

        $this->command->info('Guitar Prime themes, courses, and modules created successfully!');
    }
}
