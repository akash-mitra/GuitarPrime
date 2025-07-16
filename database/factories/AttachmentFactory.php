<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    /**
     * Get the appropriate disk for storing attachments based on FILESYSTEM_DISK.
     * Maps local -> private (secure local storage), s3 -> s3 (secure cloud storage).
     */
    private function getAttachmentDisk(): string
    {
        return config('filesystems.default') === 'local' ? 'private' : 's3';
    }

    public function definition(): array
    {
        $fileTypes = [
            ['extension' => 'pdf', 'mime' => 'application/pdf'],
            ['extension' => 'doc', 'mime' => 'application/msword'],
            ['extension' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['extension' => 'txt', 'mime' => 'text/plain'],
            ['extension' => 'jpg', 'mime' => 'image/jpeg'],
            ['extension' => 'png', 'mime' => 'image/png'],
        ];

        $fileType = $this->faker->randomElement($fileTypes);
        $filename = $this->faker->words(3, true).'.'.$fileType['extension'];

        return [
            'module_id' => Module::factory(),
            'name' => $this->faker->sentence(3, true),
            'filename' => $filename,
            'disk' => $this->getAttachmentDisk(),
            'path' => 'attachments/'.$this->faker->uuid().'/'.$filename,
            'type' => 'file',
            'size' => $this->faker->numberBetween(1024, 5242880), // 1KB to 5MB
            'mime_type' => $fileType['mime'],
        ];
    }

    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->sentence(3, true),
            'filename' => $this->faker->words(3, true).'.pdf',
            'mime_type' => 'application/pdf',
            'path' => 'attachments/'.$this->faker->uuid().'/'.$this->faker->words(3, true).'.pdf',
        ]);
    }

    public function image(): static
    {
        $extension = $this->faker->randomElement(['jpg', 'png']);
        $mime = $extension === 'jpg' ? 'image/jpeg' : 'image/png';

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->sentence(3, true),
            'filename' => $this->faker->words(3, true).'.'.$extension,
            'mime_type' => $mime,
            'path' => 'attachments/'.$this->faker->uuid().'/'.$this->faker->words(3, true).'.'.$extension,
        ]);
    }

    public function document(): static
    {
        $types = [
            ['extension' => 'doc', 'mime' => 'application/msword'],
            ['extension' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['extension' => 'txt', 'mime' => 'text/plain'],
        ];

        $type = $this->faker->randomElement($types);

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->sentence(3, true),
            'filename' => $this->faker->words(3, true).'.'.$type['extension'],
            'mime_type' => $type['mime'],
            'path' => 'attachments/'.$this->faker->uuid().'/'.$this->faker->words(3, true).'.'.$type['extension'],
        ]);
    }
}
