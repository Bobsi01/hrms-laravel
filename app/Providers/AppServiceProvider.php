<?php

namespace App\Providers;

use App\Services\AuditService;
use App\Services\PermissionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(PermissionService::class);
        $this->app->singleton(AuditService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set timezone from config
        date_default_timezone_set(config('hrms.timezone', 'Asia/Manila'));

        // Custom Blade directives for permissions
        Blade::if('can_access', function (string $domain, string $resource, string $level = 'read') {
            $permissionService = app(PermissionService::class);
            return $permissionService->userCan($domain, $resource, $level);
        });

        Blade::if('sysadmin', function () {
            return auth()->check() && auth()->user()->isSystemAdmin();
        });
    }
}
