<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Log;

class FakeProfileScraper implements ProfileScraperInterface
{
    /**
     * Scrape profile data for the given username.
     * This is a fake implementation that returns mock data.
     *
     * @param string $username
     * @return array
     * @throws \Exception
     */
    public function scrapeProfile(string $username): array
    {
        Log::info("Fake scraping profile: {$username}");

        // Simulate API delay
        sleep(rand(1, 3));

        // Simulate occasional failures (5% chance)
        if (rand(1, 100) <= 5) {
            throw new \Exception("Simulated scraping failure for username: {$username}");
        }

        // Generate realistic fake data
        $baseData = [
            'username' => $username,
            'name' => $this->generateFakeName(),
            'bio' => $this->generateFakeBio(),
            'avatar_url' => "https://example.com/avatars/{$username}.jpg",
            'cover_url' => "https://example.com/covers/{$username}.jpg",
            'likes_count' => rand(1000, 5000000),
            'posts_count' => rand(10, 10000),
            'followers_count' => rand(100, 2000000),
            'following_count' => rand(50, 5000),
            'is_verified' => rand(1, 100) <= 15, // 15% chance of being verified
            'is_online' => rand(1, 100) <= 30, // 30% chance of being online
            'location' => $this->generateFakeLocation(),
            'joined_date' => now()->subDays(rand(30, 1825))->format('Y-m-d'), // Joined 30 days to 5 years ago
        ];

        Log::info("Successfully scraped profile data for: {$username}", [
            'likes_count' => $baseData['likes_count'],
            'followers_count' => $baseData['followers_count'],
        ]);

        return $baseData;
    }

    /**
     * Check if the scraper service is available.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        // Simulate 95% uptime
        return rand(1, 100) <= 95;
    }

    /**
     * Generate a fake name.
     *
     * @return string
     */
    private function generateFakeName(): string
    {
        $firstNames = [
            'Alex', 'Jordan', 'Taylor', 'Morgan', 'Casey', 'Riley', 'Avery', 'Quinn',
            'Sage', 'River', 'Phoenix', 'Skylar', 'Cameron', 'Dakota', 'Blake'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller',
            'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez'
        ];

        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    /**
     * Generate a fake bio.
     *
     * @return string
     */
    private function generateFakeBio(): string
    {
        $bios = [
            "✨ Living my best life ✨ DM for collabs 💕",
            "🌟 Content creator | Fitness enthusiast | Coffee lover ☕",
            "💋 Your favorite girl next door 💋 Link in bio 👇",
            "🔥 New content daily 🔥 Subscribe for exclusive content",
            "💎 VIP treatment for my subscribers 💎 Always online",
            "🌸 Sweet dreams are made of me 🌸 Custom content available",
            "👑 Queen of hearts 👑 Making dreams come true",
            "🦋 Free spirit | Adventure seeker | Your fantasy 🦋",
            "💫 Spreading positivity one post at a time 💫",
            "🌺 Tropical vibes | Beach lover | Sun-kissed skin 🌺"
        ];

        return $bios[array_rand($bios)];
    }

    /**
     * Generate a fake location.
     *
     * @return string|null
     */
    private function generateFakeLocation(): ?string
    {
        // 30% chance of no location
        if (rand(1, 100) <= 30) {
            return null;
        }

        $locations = [
            'Los Angeles, CA',
            'Miami, FL',
            'New York, NY',
            'Las Vegas, NV',
            'Austin, TX',
            'Chicago, IL',
            'San Francisco, CA',
            'Atlanta, GA',
            'Phoenix, AZ',
            'Denver, CO',
            'Seattle, WA',
            'Nashville, TN'
        ];

        return $locations[array_rand($locations)];
    }
}

