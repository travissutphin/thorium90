<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['image', 'document', 'video', 'audio']);
        $extensions = [
            'image' => ['jpg', 'png', 'gif'],
            'document' => ['pdf', 'docx', 'xlsx'],
            'video' => ['mp4', 'avi', 'mov'],
            'audio' => ['mp3', 'wav', 'ogg'],
        ];
        
        $extension = $this->faker->randomElement($extensions[$type]);
        $filename = $this->faker->word() . '.' . $extension;
        $storedFilename = $this->faker->uuid() . '.' . $extension;
        $year = $this->faker->year();
        $month = str_pad($this->faker->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
        
        return [
            'filename' => $filename,
            'stored_filename' => $storedFilename,
            'path' => "media/{$type}s/{$year}/{$month}/{$storedFilename}",
            'disk' => 'public',
            'mime_type' => $this->getMimeType($type, $extension),
            'extension' => $extension,
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'type' => $type,
            'metadata' => $type === 'image' ? [
                'width' => $this->faker->numberBetween(100, 2000),
                'height' => $this->faker->numberBetween(100, 2000),
                'aspect_ratio' => $this->faker->randomFloat(2, 0.5, 3.0),
            ] : [],
            'thumbnail_path' => $type === 'image' ? 
                "media/{$type}s/{$year}/{$month}/thumbnails/{$storedFilename}" : null,
            'alt_text' => $type === 'image' ? $this->faker->sentence() : null,
            'description' => $this->faker->optional()->sentence(),
            'tags' => $this->faker->optional()->randomElements(['tag1', 'tag2', 'tag3'], rand(0, 3)),
            'is_public' => $this->faker->boolean(80), // 80% chance of being public
            'uploaded_by' => \App\Models\User::factory(),
            'scanned_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'scan_result' => $this->faker->randomElement(['clean', 'pending']),
        ];
    }

    /**
     * Get MIME type based on file type and extension
     */
    private function getMimeType(string $type, string $extension): string
    {
        $mimeTypes = [
            'image' => [
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ],
            'document' => [
                'pdf' => 'application/pdf',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'video' => [
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
            ],
            'audio' => [
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
            ],
        ];

        return $mimeTypes[$type][$extension] ?? 'application/octet-stream';
    }

    /**
     * Create an image media file
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'metadata' => [
                'width' => 1920,
                'height' => 1080,
                'aspect_ratio' => 1.78,
            ],
            'thumbnail_path' => str_replace('/images/', '/images/thumbnails/', $attributes['path']),
            'alt_text' => fake()->sentence(),
        ]);
    }

    /**
     * Create a document media file
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'metadata' => [],
            'thumbnail_path' => null,
            'alt_text' => null,
        ]);
    }

    /**
     * Create a video media file
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'mime_type' => 'video/mp4',
            'extension' => 'mp4',
            'metadata' => [
                'duration' => fake()->numberBetween(30, 3600),
                'width' => 1920,
                'height' => 1080,
            ],
            'thumbnail_path' => str_replace('/videos/', '/videos/thumbnails/', $attributes['path']),
        ]);
    }

    /**
     * Create an infected media file
     */
    public function infected(): static
    {
        return $this->state(fn (array $attributes) => [
            'scan_result' => 'infected',
            'scanned_at' => now(),
        ]);
    }

    /**
     * Create a pending scan media file
     */
    public function pendingScan(): static
    {
        return $this->state(fn (array $attributes) => [
            'scan_result' => 'pending',
            'scanned_at' => null,
        ]);
    }
}
