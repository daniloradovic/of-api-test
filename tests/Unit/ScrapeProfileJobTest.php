<?php

namespace Tests\Unit;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use App\Models\ProfileScrape;
use App\Services\Scraper\ProfileScraperInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ScrapeProfileJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'collection']);
    }

    /** @test */
    public function it_creates_new_profile_when_username_does_not_exist()
    {
        $scraperMock = Mockery::mock(ProfileScraperInterface::class);
        $scraperMock->shouldReceive('isAvailable')->andReturn(true);
        $scraperMock->shouldReceive('scrapeProfile')
            ->with('new_user')
            ->andReturn([
                'username' => 'new_user',
                'name' => 'New User',
                'bio' => 'Test bio',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'cover_url' => 'https://example.com/cover.jpg',
                'likes_count' => 50000,
                'posts_count' => 100,
                'followers_count' => 25000,
                'following_count' => 500,
                'is_verified' => false,
                'is_online' => true,
                'location' => 'Test City',
                'joined_date' => '2023-01-01',
            ]);

        $job = new ScrapeProfileJob('new_user');
        $job->handle($scraperMock);

        $this->assertDatabaseHas('profiles', [
            'username' => 'new_user',
            'name' => 'New User',
            'likes_count' => 50000,
        ]);

        $this->assertDatabaseHas('profile_scrapes', [
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function it_updates_existing_profile_data()
    {
        $profile = Profile::create([
            'username' => 'existing_user',
            'name' => 'Old Name',
            'likes_count' => 10000,
        ]);

        $scraperMock = Mockery::mock(ProfileScraperInterface::class);
        $scraperMock->shouldReceive('isAvailable')->andReturn(true);
        $scraperMock->shouldReceive('scrapeProfile')
            ->andReturn([
                'username' => 'existing_user',
                'name' => 'Updated Name',
                'bio' => 'Updated bio',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'cover_url' => 'https://example.com/cover.jpg',
                'likes_count' => 75000,
                'posts_count' => 200,
                'followers_count' => 50000,
                'following_count' => 800,
                'is_verified' => true,
                'is_online' => false,
                'location' => 'Updated City',
                'joined_date' => '2023-01-01',
            ]);

        $job = new ScrapeProfileJob('existing_user', $profile->id);
        $job->handle($scraperMock);

        $profile->refresh();
        $this->assertEquals('Updated Name', $profile->name);
        $this->assertEquals(75000, $profile->likes_count);
        $this->assertNotNull($profile->last_scraped_at);
    }

    /** @test */
    public function it_handles_scraper_service_unavailable()
    {
        $scraperMock = Mockery::mock(ProfileScraperInterface::class);
        $scraperMock->shouldReceive('isAvailable')->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Profile scraper service is currently unavailable');

        $job = new ScrapeProfileJob('test_user');
        $job->handle($scraperMock);
    }

    /** @test */
    public function it_handles_scraping_failures()
    {
        $scraperMock = Mockery::mock(ProfileScraperInterface::class);
        $scraperMock->shouldReceive('isAvailable')->andReturn(true);
        $scraperMock->shouldReceive('scrapeProfile')
            ->andThrow(new \Exception('Scraping failed'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Scraping failed');

        $job = new ScrapeProfileJob('failing_user');
        $job->handle($scraperMock);

        $this->assertDatabaseHas('profile_scrapes', [
            'status' => 'failed',
            'error_message' => 'Scraping failed',
        ]);
    }

    /** @test */
    public function it_queues_to_scraping_queue()
    {
        $job = new ScrapeProfileJob('test_user');
        
        $this->assertEquals(3, $job->tries);
        $this->assertEquals(120, $job->timeout);
        $this->assertEquals('scraping', $job->queue);
    }

    /** @test */
    public function it_has_proper_job_tags()
    {
        $job = new ScrapeProfileJob('tagged_user');
        $tags = $job->tags();

        $this->assertContains('scraping', $tags);
        $this->assertContains('username:tagged_user', $tags);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}