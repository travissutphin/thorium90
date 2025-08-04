<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with social login provider data.
     */
    public function socialUser(string $provider = 'google'): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => $provider,
            'provider_id' => fake()->unique()->numerify('##########'),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'email_verified_at' => now(), // Social users are considered verified
        ]);
    }

    /**
     * Create a Google social login user.
     */
    public function google(): static
    {
        return $this->socialUser('google');
    }

    /**
     * Create a GitHub social login user.
     */
    public function github(): static
    {
        return $this->socialUser('github');
    }

    /**
     * Create a Facebook social login user.
     */
    public function facebook(): static
    {
        return $this->socialUser('facebook');
    }

    /**
     * Create a LinkedIn social login user.
     */
    public function linkedin(): static
    {
        return $this->socialUser('linkedin');
    }

    /**
     * Create a Twitter/X social login user.
     */
    public function twitter(): static
    {
        return $this->socialUser('twitter');
    }

    /**
     * Create a GitLab social login user.
     */
    public function gitlab(): static
    {
        return $this->socialUser('gitlab');
    }
}
