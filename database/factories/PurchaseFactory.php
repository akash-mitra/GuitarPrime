<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $purchasableType = fake()->randomElement([
            \App\Models\Course::class,
            \App\Models\Module::class,
        ]);

        $purchasable = $purchasableType::factory()->create();

        return [
            'user_id' => \App\Models\User::factory(),
            'purchasable_type' => $purchasableType,
            'purchasable_id' => $purchasable->id,
            'amount' => fake()->randomFloat(2, 9.99, 299.99),
            'currency' => fake()->randomElement(['USD', 'EUR', 'GBP', 'INR']),
            'payment_provider' => fake()->randomElement(['stripe', 'razorpay']),
            'payment_id' => fake()->optional()->uuid(),
            'checkout_session_id' => fake()->optional()->uuid(),
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'cancelled']),
            'metadata' => fake()->optional()->randomElements([
                'order_id' => fake()->uuid(),
                'session_url' => fake()->url(),
                'timestamp' => fake()->unixTime(),
            ], fake()->numberBetween(0, 3)),
        ];
    }

    /**
     * Indicate that the purchase is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the purchase is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the purchase is for a course.
     */
    public function forCourse(): static
    {
        return $this->state(function (array $attributes) {
            $course = \App\Models\Course::factory()->create();

            return [
                'purchasable_type' => \App\Models\Course::class,
                'purchasable_id' => $course->id,
                'amount' => $course->price ?? fake()->randomFloat(2, 9.99, 299.99),
            ];
        });
    }

    /**
     * Indicate that the purchase is for a module.
     */
    public function forModule(): static
    {
        return $this->state(function (array $attributes) {
            $module = \App\Models\Module::factory()->create();

            return [
                'purchasable_type' => \App\Models\Module::class,
                'purchasable_id' => $module->id,
                'amount' => $module->price ?? fake()->randomFloat(2, 9.99, 299.99),
            ];
        });
    }
}
