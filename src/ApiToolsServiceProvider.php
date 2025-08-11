<?php

namespace Scrapify\ApiTools;

use Illuminate\Support\ServiceProvider;

class ApiToolsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->app->singleton(ApiService::class, function () {
            return new ApiService();
        });
    }
}
