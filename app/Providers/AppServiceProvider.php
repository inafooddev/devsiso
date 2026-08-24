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

        // Custom Blade Directives for Menu Action Permissions
        \Illuminate\Support\Facades\Blade::if('canEdit', function ($routeName) {
            return auth()->check() && auth()->user()->hasMenuAccess($routeName, 'can_edit');
        });

        \Illuminate\Support\Facades\Blade::if('canImport', function ($routeName) {
            return auth()->check() && auth()->user()->hasMenuAccess($routeName, 'can_import');
        });

        \Illuminate\Support\Facades\Blade::if('canExport', function ($routeName) {
            return auth()->check() && auth()->user()->hasMenuAccess($routeName, 'can_export');
        });

        \Illuminate\Support\Facades\Blade::if('canAdd', function ($routeName) {
            return auth()->check() && auth()->user()->hasMenuAccess($routeName, 'can_add');
        });

        \Illuminate\Support\Facades\Blade::if('canDelete', function ($routeName) {
            return auth()->check() && auth()->user()->hasMenuAccess($routeName, 'can_delete');
        });

        \Illuminate\Support\Facades\Blade::if('canFinalize', function ($routeName) {
            return auth()->check() && (auth()->user()->hasRole('finance') || auth()->user()->hasRole('admin'));
        });
    }
}
