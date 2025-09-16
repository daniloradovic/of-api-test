<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Run profile scraping every hour
        $schedule->command('profiles:scrape')
            ->hourly()
            ->withoutOverlapping()
            ->onOneServer()
            ->runInBackground()
            ->description('Scrape OnlyFans profiles based on their schedule');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
