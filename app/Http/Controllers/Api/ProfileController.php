<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Queue a profile for scraping.
     */
    public function scrape(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|regex:/^[a-zA-Z0-9_-]+$/|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username provided',
                'errors' => $validator->errors(),
            ], 422);
        }

        $username = $request->input('username');

        try {
            // Check if profile already exists
            $profile = Profile::where('username', $username)->first();

            // Queue the scraping job
            ScrapeProfileJob::dispatch($username, $profile?->id);

            Log::info("Queued profile scraping for username: {$username}");

            return response()->json([
                'success' => true,
                'message' => "Profile scraping queued for username: {$username}",
                'data' => [
                    'username' => $username,
                    'profile_exists' => !is_null($profile),
                    'queued_at' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to queue profile scraping for username: {$username}", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to queue profile scraping',
                'error' => app()->environment('local') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Search profiles using Scout.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid search parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = $request->input('q');
        $limit = $request->input('limit', 20);

        try {
            // Search using Scout
            $profiles = Profile::search($query)
                ->take($limit)
                ->get();

            // Transform the results
            $results = $profiles->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'username' => $profile->username,
                    'name' => $profile->name,
                    'bio' => $profile->bio,
                    'avatar_url' => $profile->avatar_url,
                    'likes_count' => $profile->likes_count,
                    'followers_count' => $profile->followers_count,
                    'is_verified' => $profile->is_verified,
                    'is_online' => $profile->is_online,
                    'location' => $profile->location,
                    'last_scraped_at' => $profile->last_scraped_at?->toISOString(),
                    'created_at' => $profile->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Found {$profiles->count()} profiles matching '{$query}'",
                'data' => [
                    'query' => $query,
                    'total' => $profiles->count(),
                    'limit' => $limit,
                    'profiles' => $results,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to search profiles with query: {$query}", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => app()->environment('local') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Get all profiles with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'limit' => 'sometimes|integer|min:1|max:100',
            'sort' => 'sometimes|string|in:username,name,likes_count,followers_count,last_scraped_at,created_at',
            'order' => 'sometimes|string|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $limit = $request->input('limit', 20);
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');

        try {
            $profiles = Profile::orderBy($sort, $order)
                ->paginate($limit);

            $results = $profiles->getCollection()->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'username' => $profile->username,
                    'name' => $profile->name,
                    'bio' => $profile->bio,
                    'avatar_url' => $profile->avatar_url,
                    'likes_count' => $profile->likes_count,
                    'followers_count' => $profile->followers_count,
                    'posts_count' => $profile->posts_count,
                    'is_verified' => $profile->is_verified,
                    'is_online' => $profile->is_online,
                    'location' => $profile->location,
                    'last_scraped_at' => $profile->last_scraped_at?->toISOString(),
                    'created_at' => $profile->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Retrieved {$profiles->count()} profiles",
                'data' => [
                    'profiles' => $results,
                    'pagination' => [
                        'current_page' => $profiles->currentPage(),
                        'last_page' => $profiles->lastPage(),
                        'per_page' => $profiles->perPage(),
                        'total' => $profiles->total(),
                        'from' => $profiles->firstItem(),
                        'to' => $profiles->lastItem(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to retrieve profiles", [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profiles',
                'error' => app()->environment('local') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }
}
