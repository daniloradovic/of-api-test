<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ScrapeProfilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:scrape {--limit=100 : Maximum number of profiles to queue for scraping}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue profiles for scraping based on their scraping schedule';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("Starting scheduled profile scraping (limit: {$limit})...");

        // Find profiles that need scraping
        $profilesToScrape = Profile::query()
            ->where(function ($query) {
                // Profiles that have never been scraped
                $query->whereNull('last_scraped_at')
                      // OR profiles with >100k likes that haven't been scraped in 24 hours
                      ->orWhere(function ($q) {
                          $q->where('likes_count', '>', 100000)
                            ->where('last_scraped_at', '<', now()->subHours(24));
                      })
                      // OR other profiles that haven't been scraped in 72 hours
                      ->orWhere(function ($q) {
                          $q->where('likes_count', '<=', 100000)
                            ->where('last_scraped_at', '<', now()->subHours(72));
                      });
            })
            ->orderBy('last_scraped_at', 'asc') // Prioritize profiles that haven't been scraped for longest
            ->limit($limit)
            ->get();

        if ($profilesToScrape->isEmpty()) {
            $this->info('No profiles need scraping at this time.');
            return Command::SUCCESS;
        }

        $queuedCount = 0;
        $highPriorityCount = 0;
        $regularCount = 0;

        foreach ($profilesToScrape as $profile) {
            // High priority profiles (>100k likes) get queued immediately
            if ($profile->shouldScrapeDaily()) {
                ScrapeProfileJob::dispatch($profile->username, $profile->id);
                $highPriorityCount++;
            } else {
                // Regular profiles get queued with a delay to spread the load
                ScrapeProfileJob::dispatch($profile->username, $profile->id)
                    ->delay(now()->addMinutes(rand(1, 30)));
                $regularCount++;
            }

            $queuedCount++;
        }

        $this->info("Successfully queued {$queuedCount} profiles for scraping:");
        $this->line("  • High priority (>100k likes): {$highPriorityCount}");
        $this->line("  • Regular priority: {$regularCount}");

        Log::info('Scheduled profile scraping completed', [
            'total_queued' => $queuedCount,
            'high_priority' => $highPriorityCount,
            'regular_priority' => $regularCount,
        ]);

        return Command::SUCCESS;
    }
}
