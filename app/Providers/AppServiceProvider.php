<?php

namespace App\Providers;

use App\Services\Scraper\FakeProfileScraper;
use App\Services\Scraper\ProfileScraperInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProfileScraperInterface::class, FakeProfileScraper::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
