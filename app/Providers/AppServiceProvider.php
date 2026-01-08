<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        // Register middleware aliases
        $this->app['router']->aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
        $this->app['router']->aliasMiddleware('permission', \App\Http\Middleware\PermissionMiddleware::class);
        $this->app['router']->aliasMiddleware('admin.access', \App\Http\Middleware\AdminAccessMiddleware::class);
        
        // Use Bootstrap Five for pagination
        Paginator::useBootstrapFive();
        Paginator::defaultView('components.pagination');
        Paginator::defaultSimpleView('components.pagination');
        
        // Pagination configuration is handled by PaginationServiceProvider
    }
}
