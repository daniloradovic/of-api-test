<?php

namespace App\Services\Scraper;

interface ProfileScraperInterface
{
    /**
     * Scrape profile data for the given username.
     *
     * @param string $username
     * @return array
     * @throws \Exception
     */
    public function scrapeProfile(string $username): array;

    /**
     * Check if the scraper service is available.
     *
     * @return bool
     */
    public function isAvailable(): bool;
}

