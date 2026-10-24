<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        $isProduction = $this->app->environment('production')
            || config('app.env') === 'production'
            || ($this->app->has('request') && request()->header('X-Forwarded-Proto') === 'https');

        if ($isProduction) {
            URL::forceScheme('https');

            // On Render / cloud containers without Redis, avoid per-click remote DB queries for cache & sessions
            // or non-persistent 'array' cache that recalculates heavy KPIs on every request.
            $cacheDir = storage_path('framework/cache/data');
            if (in_array(config('cache.default'), ['array', 'database'], true) && is_dir($cacheDir) && is_writable($cacheDir)) {
                config(['cache.default' => 'file']);
            }

            $sessionDir = storage_path('framework/sessions');
            if (config('session.driver') === 'database' && is_dir($sessionDir) && is_writable($sessionDir)) {
                config(['session.driver' => 'file']);
            }
        }
    }
}
