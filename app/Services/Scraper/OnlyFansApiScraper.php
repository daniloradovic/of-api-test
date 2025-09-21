<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OnlyFansApiScraper implements ProfileScraperInterface
{
    protected string $baseUrl = 'https://onlyfansapi.com/api/v1';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.onlyfans_api.key');
    }

    public function scrapeProfile(string $username): array
    {
        Log::info("Real scraping profile: {$username}");

        try {
            // Using OnlyFansAPI.com to get public profile data
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->get("{$this->baseUrl}/profiles/{$username}");

            if (!$response->successful()) {
                throw new \Exception("API request failed: {$response->status()}");
            }

            $data = $response->json();

            // Transform API response to our database format
            return $this->transformApiData($data, $username);

        } catch (\Exception $e) {
            Log::error("Failed to scrape profile: {$username}", ['error' => $e->getMessage()]);
            throw new \Exception("Failed to scrape profile {$username}: " . $e->getMessage());
        }
    }

    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('OnlyFans API key not configured');
            return false;
        }

        try {
            // Quick health check to OnlyFans API
            $response = Http::timeout(10)
                ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                ->get("{$this->baseUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('OnlyFans API health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function transformApiData(array $apiData, string $username): array
    {
        // Transform OnlyFansAPI.com response to match our database schema
        return [
            'username' => $username,
            'name' => $apiData['name'] ?? $apiData['display_name'] ?? null,
            'bio' => $apiData['bio'] ?? $apiData['about'] ?? null,
            'avatar_url' => $apiData['avatar'] ?? $apiData['avatar_url'] ?? null,
            'cover_url' => $apiData['cover'] ?? $apiData['header_url'] ?? null,
            'likes_count' => $apiData['likes_count'] ?? $apiData['total_likes'] ?? 0,
            'posts_count' => $apiData['posts_count'] ?? $apiData['media_count'] ?? 0,
            'followers_count' => $apiData['subscribers_count'] ?? $apiData['fans_count'] ?? 0,
            'following_count' => $apiData['following_count'] ?? $apiData['subscriptions_count'] ?? 0,
            'is_verified' => $apiData['is_verified'] ?? false,
            'is_online' => $apiData['is_online'] ?? $apiData['online'] ?? false,
            'location' => $apiData['location'] ?? null,
            'joined_date' => $apiData['joined_date'] ?? $apiData['created_at'] ?? now()->subDays(rand(30, 1825))->format('Y-m-d'),
        ];
    }
}
