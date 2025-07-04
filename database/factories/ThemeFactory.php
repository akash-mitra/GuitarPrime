<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Theme>
 */
class ThemeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'cover_image' => $this->faker->imageUrl(800, 600, 'music', true, 'guitar'),
        ];
    }

    public function withData(string $name, ?string $description = null, ?string $coverImage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'description' => $description ?? $this->faker->paragraph(),
            'cover_image' => $coverImage ?? $this->faker->imageUrl(800, 600, 'music', true, 'guitar'),
        ]);
    }
}
