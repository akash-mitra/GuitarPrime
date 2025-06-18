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
        ];
    }
}
