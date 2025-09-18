<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScrapeProfileRequest;
use App\Http\Requests\SearchProfilesRequest;
use App\Http\Requests\IndexProfilesRequest;
use App\Http\Resources\ProfileResource;
use App\Jobs\ScrapeProfileJob;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Queue a profile for scraping.
     */
    public function scrape(ScrapeProfileRequest $request): JsonResponse
    {
        $username = $request->validated()['username'];

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
    public function search(SearchProfilesRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $query = $validated['q'];
        $limit = $validated['limit'];

        try {
            // Search using Scout
            $profiles = Profile::search($query)
                ->take($limit)
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Found {$profiles->count()} profiles matching '{$query}'",
                'data' => [
                    'query' => $query,
                    'total' => $profiles->count(),
                    'limit' => $limit,
                    'profiles' => ProfileResource::collection($profiles),
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
    public function index(IndexProfilesRequest $request): JsonResponse
    {
        $data = $request->getValidatedData();
        $limit = $data['limit'];
        $sort = $data['sort'];
        $order = $data['order'];

        try {
            $profiles = Profile::orderBy($sort, $order)
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => "Retrieved {$profiles->count()} profiles",
                'data' => [
                    'profiles' => ProfileResource::collection($profiles),
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
