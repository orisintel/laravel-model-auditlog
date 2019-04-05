<?php

namespace OrisIntel\AuditLog;

use Illuminate\Support\ServiceProvider;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/model-audit-log.php' => config_path('model-audit-log.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model-audit-log.php', 'model-audit-log');
    }
}
