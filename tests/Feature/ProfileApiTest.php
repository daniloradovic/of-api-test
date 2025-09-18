<?php

namespace Tests\Feature;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we're using the testing environment
        config(['scout.driver' => 'collection']);
    }

    /** @test */
    public function it_can_queue_profile_scraping_for_new_username()
    {
        Queue::fake();

        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'test_user'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile scraping queued for username: test_user',
                'data' => [
                    'username' => 'test_user',
                    'profile_exists' => false,
                ]
            ]);

        Queue::assertPushed(ScrapeProfileJob::class);
    }

    /** @test */
    public function it_can_queue_profile_scraping_for_existing_username()
    {
        Queue::fake();

        // Create existing profile
        $profile = Profile::create([
            'username' => 'existing_user',
            'name' => 'Existing User',
            'likes_count' => 50000,
        ]);

        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'existing_user'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'existing_user',
                    'profile_exists' => true,
                ]
            ]);

        Queue::assertPushed(ScrapeProfileJob::class);
    }

    /** @test */
    public function it_validates_scrape_request_parameters()
    {
        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'a' // Too short
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'username' => ['Username must be at least 3 characters long.']
                ]
            ]);
    }

    /** @test */
    public function it_rejects_invalid_usernames()
    {
        $invalidUsernames = [
            'user with spaces',
            'user@invalid',
            'admin', // Reserved
            'api',   // Reserved
            'test@#$%', // Invalid characters
        ];

        foreach ($invalidUsernames as $username) {
            $response = $this->postJson('/api/profiles/scrape', [
                'username' => $username
            ]);

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function it_can_search_profiles()
    {
        // Create test profiles
        $profiles = [
            Profile::create([
                'username' => 'fitness_girl',
                'name' => 'Fitness Girl',
                'bio' => 'I love fitness and healthy living',
                'likes_count' => 100000,
            ]),
            Profile::create([
                'username' => 'travel_blogger',
                'name' => 'Travel Blogger',
                'bio' => 'Exploring the world one city at a time',
                'likes_count' => 50000,
            ]),
            Profile::create([
                'username' => 'art_creator',
                'name' => 'Art Creator',
                'bio' => 'Digital artist creating amazing content',
                'likes_count' => 75000,
            ]),
        ];

        // Make profiles searchable
        foreach ($profiles as $profile) {
            $profile->searchable();
        }

        $response = $this->getJson('/api/search?q=fitness');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'query' => 'fitness',
                    'total' => 1,
                ]
            ]);

        $this->assertCount(1, $response->json('data.profiles'));
        $this->assertEquals('fitness_girl', $response->json('data.profiles.0.username'));
    }

    /** @test */
    public function it_validates_search_parameters()
    {
        $response = $this->getJson('/api/search?q=a'); // Too short

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid search parameters',
                'errors' => [
                    'q' => ['Search query must be at least 2 characters long.']
                ]
            ]);
    }

    /** @test */
    public function it_can_list_profiles_with_pagination()
    {
        // Create test profiles
        Profile::factory()->count(25)->create();

        $response = $this->getJson('/api/profiles?limit=10&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 10,
                        'total' => 25,
                    ]
                ]
            ]);

        $this->assertCount(10, $response->json('data.profiles'));
    }

    /** @test */
    public function it_can_sort_profiles()
    {
        Profile::create([
            'username' => 'user_a',
            'name' => 'User A',
            'likes_count' => 100,
            'created_at' => now()->subDays(2),
        ]);

        Profile::create([
            'username' => 'user_b',
            'name' => 'User B',
            'likes_count' => 200,
            'created_at' => now()->subDays(1),
        ]);

        // Sort by likes_count descending
        $response = $this->getJson('/api/profiles?sort=likes_count&order=desc');

        $response->assertStatus(200);
        $profiles = $response->json('data.profiles');
        $this->assertEquals('user_b', $profiles[0]['username']);
        $this->assertEquals('user_a', $profiles[1]['username']);
    }

    /** @test */
    public function it_validates_index_parameters()
    {
        $response = $this->getJson('/api/profiles?sort=invalid_field');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid parameters',
            ]);
    }

    /** @test */
    public function health_endpoint_returns_success()
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OnlyFans Profile Scraper API is running',
                'version' => '1.0.0',
            ]);
    }

    /** @test */
    public function it_handles_rate_limiting()
    {
        // This test would require more setup to actually test rate limiting
        // For now, we'll just verify the middleware is applied
        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'test_user'
        ]);

        // Check that rate limit headers are present
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
    }

    /** @test */
    public function it_returns_consistent_json_structure()
    {
        Queue::fake();

        $response = $this->postJson('/api/profiles/scrape', [
            'username' => 'test_user'
        ]);

        $json = $response->json();

        // All API responses should have these fields
        $this->assertArrayHasKey('success', $json);
        $this->assertArrayHasKey('message', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertTrue($json['success']);
    }
}