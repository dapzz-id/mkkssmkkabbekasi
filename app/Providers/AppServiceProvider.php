<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();

        // Enforce HTTPS in production, but exclude local development servers (localhost / 127.0.0.1)
        if (config('app.env') === 'production' 
            && app()->bound('request') 
            && !in_array(optional(request())->getHost(), ['localhost', '127.0.0.1'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
