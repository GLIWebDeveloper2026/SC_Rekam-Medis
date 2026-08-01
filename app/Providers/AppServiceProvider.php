<?php

namespace App\Providers;

use App\Contracts\Ai\OpenAiClient;
use App\Services\Ai\OpenAiResponsesClient;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OpenAiClient::class, OpenAiResponsesClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        RateLimiter::for('clinic-chat', function (Request $request): array {
            $user = $request->user();

            return [
                Limit::perMinute($user?->hasRole('patient') ? 10 : 20)
                    ->by('chat:user:'.($user?->id ?? 'guest')),
                Limit::perMinute(30)->by('chat:ip:'.$request->ip()),
            ];
        });
    }
}
