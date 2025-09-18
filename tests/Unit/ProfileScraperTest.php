<?php

namespace Tests\Unit;

use App\Services\Scraper\FakeProfileScraper;
use App\Services\Scraper\ProfileScraperInterface;
use Tests\TestCase;

class ProfileScraperTest extends TestCase
{
    protected FakeProfileScraper $scraper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scraper = new FakeProfileScraper();
    }

    /** @test */
    public function it_implements_profile_scraper_interface()
    {
        $this->assertInstanceOf(ProfileScraperInterface::class, $this->scraper);
    }

    /** @test */
    public function it_reports_as_available()
    {
        $this->assertTrue($this->scraper->isAvailable());
    }

    /** @test */
    public function it_returns_complete_profile_data_structure()
    {
        $username = 'test_user';
        $profileData = $this->scraper->scrapeProfile($username);

        // Check that all required fields are present
        $requiredFields = [
            'username',
            'name',
            'bio',
            'avatar_url',
            'cover_url',
            'likes_count',
            'posts_count',
            'followers_count',
            'following_count',
            'is_verified',
            'is_online',
            'location',
            'joined_date',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $profileData, "Missing required field: {$field}");
        }
    }

    /** @test */
    public function it_returns_correct_username()
    {
        $username = 'specific_test_user';
        $profileData = $this->scraper->scrapeProfile($username);

        $this->assertEquals($username, $profileData['username']);
    }

    /** @test */
    public function it_returns_valid_data_types()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // String fields
        $this->assertIsString($profileData['username']);
        $this->assertIsString($profileData['name']);
        $this->assertIsString($profileData['bio']);
        $this->assertIsString($profileData['avatar_url']);
        $this->assertIsString($profileData['cover_url']);
        $this->assertIsString($profileData['joined_date']);

        // Integer fields
        $this->assertIsInt($profileData['likes_count']);
        $this->assertIsInt($profileData['posts_count']);
        $this->assertIsInt($profileData['followers_count']);
        $this->assertIsInt($profileData['following_count']);

        // Boolean fields
        $this->assertIsBool($profileData['is_verified']);
        $this->assertIsBool($profileData['is_online']);

        // Location can be string or null
        $this->assertTrue(
            is_string($profileData['location']) || is_null($profileData['location'])
        );
    }

    /** @test */
    public function it_returns_realistic_data_ranges()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // Check realistic ranges
        $this->assertGreaterThanOrEqual(1000, $profileData['likes_count']);
        $this->assertLessThanOrEqual(5000000, $profileData['likes_count']);

        $this->assertGreaterThanOrEqual(10, $profileData['posts_count']);
        $this->assertLessThanOrEqual(10000, $profileData['posts_count']);

        $this->assertGreaterThanOrEqual(100, $profileData['followers_count']);
        $this->assertLessThanOrEqual(2000000, $profileData['followers_count']);

        $this->assertGreaterThanOrEqual(50, $profileData['following_count']);
        $this->assertLessThanOrEqual(5000, $profileData['following_count']);
    }

    /** @test */
    public function it_generates_valid_urls()
    {
        $username = 'test_user';
        $profileData = $this->scraper->scrapeProfile($username);

        $this->assertStringStartsWith('https://example.com/avatars/', $profileData['avatar_url']);
        $this->assertStringEndsWith('.jpg', $profileData['avatar_url']);
        $this->assertStringContains($username, $profileData['avatar_url']);

        $this->assertStringStartsWith('https://example.com/covers/', $profileData['cover_url']);
        $this->assertStringEndsWith('.jpg', $profileData['cover_url']);
        $this->assertStringContains($username, $profileData['cover_url']);
    }

    /** @test */
    public function it_generates_valid_date_format()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // Should be in Y-m-d format
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}$/',
            $profileData['joined_date']
        );

        // Should be a valid date
        $this->assertNotFalse(
            \DateTime::createFromFormat('Y-m-d', $profileData['joined_date'])
        );
    }

    /** @test */
    public function it_generates_different_data_for_different_users()
    {
        $user1Data = $this->scraper->scrapeProfile('user1');
        $user2Data = $this->scraper->scrapeProfile('user2');

        // Usernames should be different
        $this->assertNotEquals($user1Data['username'], $user2Data['username']);

        // At least some other fields should be different (due to randomization)
        // We'll check that they're not identical
        $this->assertNotEquals($user1Data, $user2Data);
    }

    /** @test */
    public function it_occasionally_throws_exceptions_for_failure_simulation()
    {
        // This test runs multiple times to try to hit the 5% failure rate
        $exceptionCount = 0;
        $totalRuns = 100;

        for ($i = 0; $i < $totalRuns; $i++) {
            try {
                $this->scraper->scrapeProfile("test_user_{$i}");
            } catch (\Exception $e) {
                $exceptionCount++;
                $this->assertStringContainsString('Simulated scraping failure', $e->getMessage());
            }
        }

        // We expect roughly 5% failures, but allow for variance in random number generation
        // At least 1 failure in 100 runs should occur
        $this->assertGreaterThan(0, $exceptionCount, 'Expected at least some simulated failures');
        $this->assertLessThan(15, $exceptionCount, 'Too many failures occurred'); // Max ~15% for variance
    }

    /** @test */
    public function it_generates_realistic_profile_names()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // Name should not be empty
        $this->assertNotEmpty($profileData['name']);

        // Should contain first and last name (space separated)
        $this->assertStringContains(' ', $profileData['name']);

        // Should be reasonable length
        $this->assertGreaterThan(3, strlen($profileData['name']));
        $this->assertLessThan(50, strlen($profileData['name']));
    }

    /** @test */
    public function it_generates_realistic_bios()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // Bio should not be empty
        $this->assertNotEmpty($profileData['bio']);

        // Should be reasonable length
        $this->assertGreaterThan(10, strlen($profileData['bio']));
        $this->assertLessThan(500, strlen($profileData['bio']));
    }

    /** @test */
    public function it_maintains_data_consistency_within_profile()
    {
        $profileData = $this->scraper->scrapeProfile('test_user');

        // Avatar and cover URLs should reference the same username
        $this->assertStringContains('test_user', $profileData['avatar_url']);
        $this->assertStringContains('test_user', $profileData['cover_url']);

        // Joined date should be in the past
        $joinedDate = \DateTime::createFromFormat('Y-m-d', $profileData['joined_date']);
        $this->assertLessThanOrEqual(new \DateTime(), $joinedDate);

        // Likes count should be reasonable compared to followers
        // (Not enforcing strict business logic, but checking for obvious inconsistencies)
        $this->assertIsInt($profileData['likes_count']);
        $this->assertIsInt($profileData['followers_count']);
    }
}