<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // One shared limit for the whole demonstration, so no visitor
        // IP address is used as a rate limiting key.
        RateLimiter::for('safety-demo', function () {
            return Limit::perMinute(60)->by('safety-demo');
        });
    }
}
