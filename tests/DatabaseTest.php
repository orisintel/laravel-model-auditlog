<?php

namespace OrisIntel\AuditLog\Tests;

use Illuminate\Support\Facades\Schema;

class DatabaseTest extends TestCase
{
    /** @test */
    public function posts_table_exists()
    {
        $this->assertTrue(Schema::hasTable('posts'));
    }

    /** @test */
    public function posts_auditlog_table_exists()
    {
        $this->assertTrue(Schema::hasTable('posts_auditlog'));
    }
}
