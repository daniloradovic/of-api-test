<?php

namespace App\Jobs;

use App\Models\Profile;
use App\Models\ProfileScrape;
use App\Services\Scraper\ProfileScraperInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ScrapeProfileJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $username,
        protected ?int $profileId = null
    ) {
        $this->onQueue('scraping');
    }

    /**
     * Execute the job.
     */
    public function handle(ProfileScraperInterface $scraper): void
    {
        Log::info("Starting profile scrape job for username: {$this->username}");

        // Find or create profile
        $profile = Profile::firstOrCreate(
            ['username' => $this->username],
            ['username' => $this->username]
        );

        // Create scrape record
        $profileScrape = ProfileScrape::create([
            'profile_id' => $profile->id,
            'status' => 'pending',
        ]);

        $profileScrape->markAsStarted();

        try {
            // Check if scraper is available
            if (!$scraper->isAvailable()) {
                throw new \Exception('Profile scraper service is currently unavailable');
            }

            // Scrape the profile data
            $scrapedData = $scraper->scrapeProfile($this->username);

            // Update profile with scraped data
            $profile->update([
                'name' => $scrapedData['name'] ?? $profile->name,
                'bio' => $scrapedData['bio'] ?? $profile->bio,
                'avatar_url' => $scrapedData['avatar_url'] ?? $profile->avatar_url,
                'cover_url' => $scrapedData['cover_url'] ?? $profile->cover_url,
                'likes_count' => $scrapedData['likes_count'] ?? $profile->likes_count,
                'posts_count' => $scrapedData['posts_count'] ?? $profile->posts_count,
                'followers_count' => $scrapedData['followers_count'] ?? $profile->followers_count,
                'following_count' => $scrapedData['following_count'] ?? $profile->following_count,
                'is_verified' => $scrapedData['is_verified'] ?? $profile->is_verified,
                'is_online' => $scrapedData['is_online'] ?? $profile->is_online,
                'location' => $scrapedData['location'] ?? $profile->location,
                'joined_date' => $scrapedData['joined_date'] ?? $profile->joined_date,
                'last_scraped_at' => now(),
            ]);

            // Update search index
            $profile->searchable();

            // Mark scrape as completed
            $profileScrape->markAsCompleted($scrapedData);

            Log::info("Successfully completed profile scrape for username: {$this->username}", [
                'profile_id' => $profile->id,
                'likes_count' => $profile->likes_count,
                'followers_count' => $profile->followers_count,
            ]);

        } catch (Throwable $e) {
            // Mark scrape as failed
            $profileScrape->markAsFailed($e->getMessage());

            Log::error("Failed to scrape profile for username: {$this->username}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("Profile scrape job failed permanently for username: {$this->username}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Find the profile and mark the latest scrape as failed
        $profile = Profile::where('username', $this->username)->first();
        if ($profile) {
            $latestScrape = $profile->scrapes()->latest()->first();
            if ($latestScrape && $latestScrape->status === 'pending') {
                $latestScrape->markAsFailed(
                    "Job failed permanently after {$this->attempts()} attempts: " . $exception->getMessage()
                );
            }
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array<int, string>
     */
    public function tags(): array
    {
        return ['scraping', "username:{$this->username}"];
    }
}
