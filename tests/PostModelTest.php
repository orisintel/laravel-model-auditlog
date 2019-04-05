<?php

namespace OrisIntel\AuditLog\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use OrisIntel\AuditLog\EventType;
use OrisIntel\AuditLog\Tests\Fakes\Models\Post;
use OrisIntel\AuditLog\Tests\Fakes\Models\PostAuditLog;

class PostModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_get_classname_of_auditlog_model()
    {
        $post = new Post();
        $this->assertEquals(PostAuditLog::class, $post->getAuditLogModelName());
    }

    /** @test */
    public function can_get_instance_of_auditlog_model()
    {
        $post = new Post();
        $this->assertInstanceOf(PostAuditLog::class, $post->getAuditLogModelInstance());
    }

    /** @test */
    public function creating_a_post_triggers_a_revision()
    {
        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        /** @var Collection $logs */
        $logs = $post->auditLogs()->where('event_type', EventType::CREATED)->get();
        $this->assertEquals(2, $logs->count());

        $title = $logs->where('field_name', 'title')->first();
        $this->assertEquals('title', $title->field_name);
        $this->assertNull($title->field_value_old);
        $this->assertEquals('Test', $title->field_value_new);

        $posted = $logs->where('field_name', 'posted_at')->first();
        $this->assertEquals('posted_at', $posted->field_name);
        $this->assertNull($posted->field_value_old);
        $this->assertEquals('2019-04-05 12:00:00', $posted->field_value_new);
    }
}
