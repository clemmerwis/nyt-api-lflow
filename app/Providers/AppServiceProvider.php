<?php

namespace App\Providers;

use App\Services\NYTBooksService;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NYTBooksService::class, function ($app) {
            return new NYTBooksService(
                config('services.nyt.api_key'),
                $app->make(HttpFactory::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
