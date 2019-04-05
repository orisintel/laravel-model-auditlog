<?php

namespace OrisIntel\AuditLog\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use OrisIntel\AuditLog\AuditLogServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * SetUp.
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/Fakes/migrations/');
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            AuditLogServiceProvider::class,
        ];
    }
}
