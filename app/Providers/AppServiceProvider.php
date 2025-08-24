<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Services\FakeStoreApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(FakeStoreApiService::class);
    }

    public function boot()
    {
        config([
        'ratelimiter.api' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ]
    ]);
    }
}