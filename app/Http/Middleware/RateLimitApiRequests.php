<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitApiRequests
{
    /**
     * Rate limiting configuration.
     */
    protected array $limits = [
        'scrape' => ['requests' => 10, 'window' => 60], // 10 requests per minute
        'search' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'general' => ['requests' => 200, 'window' => 60], // 200 requests per minute
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        $key = $this->generateRateLimitKey($request, $type);
        $limit = $this->limits[$type] ?? $this->limits['general'];
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limit['requests']) {
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'endpoint' => $request->path(),
                'attempts' => $attempts,
                'limit' => $limit['requests'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
                'retry_after' => $limit['window'],
                'limit' => $limit['requests'],
                'window' => $limit['window'],
            ], 429);
        }

        // Increment counter
        Cache::put($key, $attempts + 1, $limit['window']);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limit['requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit['requests'] - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds($limit['window'])->timestamp);

        return $response;
    }

    /**
     * Generate rate limit cache key.
     */
    protected function generateRateLimitKey(Request $request, string $type): string
    {
        // Use IP and User-Agent for identification
        $identifier = hash('sha256', $request->ip() . '|' . $request->userAgent());
        return "rate_limit:{$type}:{$identifier}";
    }
}
