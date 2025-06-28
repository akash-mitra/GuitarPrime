<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'theme_id' => Theme::factory(),
            'coach_id' => User::factory()->create(['role' => 'coach'])->id,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraphs(3, true),
            'is_approved' => $this->faker->boolean(30), // 30% chance of being approved
            'cover_image' => $this->faker->imageUrl(800, 600, 'music', true, 'guitar course'),
        ];
    }

    public function approved()
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    public function pending()
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    public function withData(string $title, string $description, $themeId, $coachId, bool $isApproved = true, ?string $coverImage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'theme_id' => $themeId,
            'coach_id' => $coachId,
            'title' => $title,
            'description' => $description,
            'is_approved' => $isApproved,
            'cover_image' => $coverImage ?? $this->faker->imageUrl(800, 600, 'music', true, 'guitar course'),
        ]);
    }
}
