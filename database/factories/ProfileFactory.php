<?php

namespace Database\Factories;

use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'name' => $this->faker->name(),
            'bio' => $this->faker->sentence(10),
            'avatar_url' => $this->faker->imageUrl(200, 200, 'people'),
            'cover_url' => $this->faker->imageUrl(800, 400, 'abstract'),
            'likes_count' => $this->faker->numberBetween(1000, 1000000),
            'posts_count' => $this->faker->numberBetween(10, 5000),
            'followers_count' => $this->faker->numberBetween(100, 500000),
            'following_count' => $this->faker->numberBetween(50, 3000),
            'is_verified' => $this->faker->boolean(20), // 20% chance of being verified
            'is_online' => $this->faker->boolean(30), // 30% chance of being online
            'location' => $this->faker->optional(0.7)->city(), // 70% chance of having location
            'joined_date' => $this->faker->dateTimeBetween('-5 years', '-1 month')->format('Y-m-d'),
            'last_scraped_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the profile has high likes count (daily scraping).
     */
    public function highLikes(): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween(100001, 5000000),
        ]);
    }

    /**
     * Indicate that the profile has low likes count (72h scraping).
     */
    public function lowLikes(): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween(1000, 100000),
        ]);
    }

    /**
     * Indicate that the profile is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
        ]);
    }

    /**
     * Indicate that the profile has never been scraped.
     */
    public function neverScraped(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_scraped_at' => null,
        ]);
    }
}