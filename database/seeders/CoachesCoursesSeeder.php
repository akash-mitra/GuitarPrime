<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoachesCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating coaches with courses, modules, and attachments...');

        // Get existing themes
        $themes = Theme::all();

        if ($themes->isEmpty()) {
            $this->command->error('No themes found. Please run GuitarPrimeDataSeeder first.');
            return;
        }

        // Create 3 coaches
        $coaches = User::factory()->count(3)->create([
            'role' => 'coach',
            'email_verified_at' => now(),
        ]);

        foreach ($coaches as $index => $coach) {
            $this->command->info("Creating courses for coach: {$coach->name}");

            // Each coach gets 5 courses
            for ($courseIndex = 1; $courseIndex <= 5; $courseIndex++) {
                $theme = $themes->random();

                $course = Course::factory()->create([
                    'theme_id' => $theme->id,
                    'coach_id' => $coach->id,
                    'title' => "Advanced {$theme->name} Course {$courseIndex}",
                    'description' => "An in-depth exploration of {$theme->name} techniques and concepts, designed for intermediate to advanced students.",
                    'is_approved' => $courseIndex <= 3, // First 3 courses approved, last 2 pending
                ]);

                $this->command->info("  Creating course: {$course->title}");

                // Each course gets 4 modules
                for ($moduleIndex = 1; $moduleIndex <= 4; $moduleIndex++) {
                    $difficulties = ['easy', 'medium', 'hard'];
                    $difficulty = $difficulties[($moduleIndex - 1) % 3]; // Cycle through difficulties

                    $module = Module::factory()->create([
                        'title' => "Module {$moduleIndex}: " . fake()->words(3, true),
                        'description' => fake()->paragraphs(2, true),
                        'difficulty' => $difficulty,
                        'video_url' => $moduleIndex <= 2 ? 'https://vimeo.com/' . fake()->numberBetween(100000000, 999999999) : null,
                    ]);

                    // Attach module to course with order
                    $course->modules()->attach($module->id, ['order' => $moduleIndex]);

                    $this->command->info("    Creating module: {$module->title}");

                    // Each module gets 2-3 attachments
                    $attachmentCount = fake()->numberBetween(2, 3);

                    for ($attachmentIndex = 1; $attachmentIndex <= $attachmentCount; $attachmentIndex++) {
                        $attachmentTypes = ['pdf', 'document', 'image'];
                        $attachmentType = fake()->randomElement($attachmentTypes);

                        Attachment::factory()->$attachmentType()->create([
                            'module_id' => $module->id,
                        ]);
                    }

                    $this->command->info("      Created {$attachmentCount} attachments");
                }
            }
        }

        // Create some additional students
        User::factory()->count(10)->create([
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Coaches, courses, modules, and attachments created successfully!');
        $this->command->info('Summary:');
        $this->command->info('- 3 Coaches created');
        $this->command->info('- 15 Additional courses created (5 per coach)');
        $this->command->info('- 60 Modules created (4 per course)');
        $this->command->info('- ~150 Attachments created (2-3 per module)');
        $this->command->info('- 10 Students created');
    }
}
