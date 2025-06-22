<?php

namespace Database\Factories;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'module_id' => $this->faker->word(),
            'filename' => $this->faker->word(),
            'disk' => $this->faker->word(),
            'path' => $this->faker->word(),
            'type' => $this->faker->word(),
            'size' => $this->faker->randomNumber(),
            'mime_type' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
