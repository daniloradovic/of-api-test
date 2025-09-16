<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileScrape extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'profile_id',
        'status',
        'scraped_data',
        'error_message',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scraped_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the profile that owns the scrape.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Mark the scrape as started.
     */
    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'pending',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the scrape as completed.
     */
    public function markAsCompleted(array $scrapedData): void
    {
        $this->update([
            'status' => 'completed',
            'scraped_data' => $scrapedData,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the scrape as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include completed scrapes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed scrapes.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include pending scrapes.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
