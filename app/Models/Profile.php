<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Profile extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
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
        'last_scraped_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'likes_count' => 'integer',
        'posts_count' => 'integer',
        'followers_count' => 'integer',
        'following_count' => 'integer',
        'is_verified' => 'boolean',
        'is_online' => 'boolean',
        'joined_date' => 'date',
        'last_scraped_at' => 'datetime',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'bio' => $this->bio,
            'location' => $this->location,
            'likes_count' => $this->likes_count,
            'is_verified' => $this->is_verified,
        ];
    }

    /**
     * Get the profile scrapes for the profile.
     */
    public function scrapes(): HasMany
    {
        return $this->hasMany(ProfileScrape::class);
    }

    /**
     * Get the latest scrape for the profile.
     */
    public function latestScrape(): HasOne
    {
        return $this->hasOne(ProfileScrape::class)->latestOfMany();
    }

    /**
     * Determine if the profile should be scraped every 24 hours (>100k likes).
     */
    public function shouldScrapeDaily(): bool
    {
        return $this->likes_count > 100000;
    }

    /**
     * Get the scraping interval in hours.
     */
    public function getScrapingIntervalHours(): int
    {
        return $this->shouldScrapeDaily() ? 24 : 72;
    }

    /**
     * Determine if the profile needs to be scraped.
     */
    public function needsScraping(): bool
    {
        if (is_null($this->last_scraped_at)) {
            return true;
        }

        $intervalHours = $this->getScrapingIntervalHours();
        return $this->last_scraped_at->addHours($intervalHours)->isPast();
    }
}
