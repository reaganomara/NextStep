<?php

namespace App\Providers;

use App\Ai\GroqTextGenerator;
use App\Ai\TextGenerator;
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
        $this->app->bind(TextGenerator::class, fn () => new GroqTextGenerator(
            apiKey: (string) config('services.groq.key'),
            model: config('ai_demo.model'),
            maxTokens: config('ai_demo.max_tokens'),
            timeoutSeconds: config('ai_demo.timeout_seconds'),
            temperature: config('ai_demo.temperature'),
        ));
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

        // The guided examples get their own shared limit, so heavy use of
        // one demonstration never blocks the other.
        RateLimiter::for('guided-demo', function () {
            return Limit::perMinute(60)->by('guided-demo');
        });

        // A lower shared limit for the AI explanation. The daily limit in
        // config/ai_demo.php is the main cost guard.
        RateLimiter::for('ai-demo', function () {
            return Limit::perMinute(20)->by('ai-demo');
        });
    }
}
