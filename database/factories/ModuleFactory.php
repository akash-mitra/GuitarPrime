<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Module>
 */
class ModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'difficulty' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'video_url' => $this->faker->optional(0.7)->url(),
            'cover_image' => $this->faker->imageUrl(800, 600, 'music', true, 'guitar lesson'),
        ];
    }

    public function easy()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'easy',
        ]);
    }

    public function medium()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'medium',
        ]);
    }

    public function hard()
    {
        return $this->state(fn (array $attributes) => [
            'difficulty' => 'hard',
        ]);
    }

    public function withVideo()
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => 'https://vimeo.com/' . $this->faker->numberBetween(100000000, 999999999),
        ]);
    }

    public function withoutVideo()
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => null,
        ]);
    }
}
