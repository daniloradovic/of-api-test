<?php

namespace App\Providers;

use App\Services\Scraper\FakeProfileScraper;
use App\Services\Scraper\OnlyFansApiScraper;
use App\Services\Scraper\ProfileScraperInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Switch between fake and real scraper based on environment
        if (config('services.onlyfans_api.key')) {
            $this->app->bind(ProfileScraperInterface::class, OnlyFansApiScraper::class);
        } else {
            $this->app->bind(ProfileScraperInterface::class, FakeProfileScraper::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
