<?php

use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
    // Profile management routes
    Route::prefix('profiles')->group(function () {
        Route::post('scrape', [ProfileController::class, 'scrape'])
            ->middleware('rate_limit:scrape')
            ->name('profiles.scrape');
        
        Route::get('/', [ProfileController::class, 'index'])
            ->middleware('rate_limit:general')
            ->name('profiles.index');
    });

    // Search routes
    Route::get('search', [ProfileController::class, 'search'])
        ->middleware('rate_limit:search')
        ->name('profiles.search');

    // Health check route
    Route::get('health', function () {
        return response()->json([
            'success' => true,
            'message' => 'OnlyFans Profile Scraper API is running',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
        ]);
    })->name('api.health');
});

