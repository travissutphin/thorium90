<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $content = $this->faker->paragraphs(5, true);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $content,
            'excerpt' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['draft', 'published', 'private']),
            'is_featured' => $this->faker->boolean(20), // 20% chance of being featured
            'meta_title' => $title,
            'meta_description' => Str::limit(strip_tags($content), 160),
            'meta_keywords' => implode(', ', $this->faker->words(5)),
            'schema_type' => $this->faker->randomElement(['WebPage', 'Article', 'BlogPosting', 'NewsArticle']),
            'user_id' => User::factory(),
            'published_at' => function (array $attributes) {
                return $attributes['status'] === 'published' ? $this->faker->dateTimeBetween('-1 year', 'now') : null;
            },
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $this->faker->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Indicate that the page is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the page is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the page is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'private',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the page is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the page is not featured.
     */
    public function notFeatured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => false,
        ]);
    }

    /**
     * Indicate that the page has SEO data.
     */
    public function withSeo(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta_title' => $this->faker->sentence(6),
            'meta_description' => $this->faker->paragraph(),
            'meta_keywords' => implode(', ', $this->faker->words(8)),
            'og_title' => $this->faker->sentence(6),
            'og_description' => $this->faker->paragraph(),
            'og_image' => $this->faker->imageUrl(1200, 630),
            'twitter_title' => $this->faker->sentence(6),
            'twitter_description' => $this->faker->paragraph(),
            'twitter_image' => $this->faker->imageUrl(1200, 600),
        ]);
    }

    /**
     * Indicate that the page has schema data.
     */
    public function withSchema(): static
    {
        return $this->state(fn (array $attributes) => [
            'schema_type' => 'Article',
            'schema_data' => [
                'author' => [
                    '@type' => 'Person',
                    'name' => $this->faker->name(),
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => $this->faker->imageUrl(600, 60),
                    ],
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => $this->faker->url(),
                ],
            ],
        ]);
    }
}
