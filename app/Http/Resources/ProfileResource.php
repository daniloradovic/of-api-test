<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'name' => $this->name,
            'bio' => $this->bio,
            'avatar_url' => $this->avatar_url,
            'cover_url' => $this->cover_url,
            'likes_count' => $this->likes_count,
            'posts_count' => $this->posts_count,
            'followers_count' => $this->followers_count,
            'following_count' => $this->following_count,
            'is_verified' => $this->is_verified,
            'is_online' => $this->is_online,
            'location' => $this->location,
            'joined_date' => $this->joined_date?->format('Y-m-d'),
            'last_scraped_at' => $this->last_scraped_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
