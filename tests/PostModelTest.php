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

    /** @test */
    public function updating_a_post_triggers_a_revision()
    {
        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $post->auditLogs()->count());

        // Modify the post
        $post->update(['title' => 'My New Title']);
        $this->assertEquals(3, $post->auditLogs()->count());

        $title = $post->auditLogs()->where('event_type', EventType::UPDATED)->first();
        $this->assertEquals('title', $title->field_name);
        $this->assertEquals('Test', $title->field_value_old);
        $this->assertEquals('My New Title', $title->field_value_new);
    }

    /** @test */
    public function deleting_a_post_triggers_a_revision()
    {
        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $post->auditLogs()->count());

        $post->delete();

        $this->assertEquals(3, $post->auditLogs()->count());

        $last = $post->auditLogs()->where('event_type', EventType::DELETED)->first();
        $this->assertEquals('deleted_at', $last->field_name);
        $this->assertNull($last->field_value_old);
        $this->assertNotEmpty($last->field_value_new);
    }

    /** @test */
    public function restoring_a_post_triggers_a_revision()
    {
        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $post->auditLogs()->count());

        $post->delete();

        $this->assertEquals(3, $post->auditLogs()->count());

        $post->restore();

        $this->assertEquals(5, $post->auditLogs()->count());

        $last = $post->auditLogs()->where('event_type', EventType::RESTORED)->first();
        $this->assertEquals('deleted_at', $last->field_name);
        $this->assertNull($last->field_value_new);
    }
}
