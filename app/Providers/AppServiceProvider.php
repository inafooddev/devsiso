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
        if ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || 
            (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'master.my.id') ||
            (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] === 'master.my.id')) {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer(
            'components.sidebar', 
            \App\Http\View\Composers\SidebarComposer::class
        );
    }
}
