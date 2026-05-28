<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Hashing\Md5Hasher;
use App\Models\Sell;
use App\Observers\SellObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->afterResolving('hash', function ($hashManager) {
            $hashManager->extend('md5', fn () => new Md5Hasher());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Sell Observer for FCM notifications
        Sell::observe(SellObserver::class);
    }
}
