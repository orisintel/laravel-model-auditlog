<?php

namespace OrisIntel\AuditLog;

use Illuminate\Support\ServiceProvider;
use OrisIntel\AuditLog\Console\Commands\MakeModelAuditLogTable;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/model-auditlog.php' => config_path('model-auditlog.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model-auditlog.php', 'model-auditlog');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModelAuditLogTable::class
            ]);
        }
    }
}
