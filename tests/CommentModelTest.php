<?php

namespace OrisIntel\AuditLog\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use OrisIntel\AuditLog\EventType;
use OrisIntel\AuditLog\Tests\Fakes\Models\Comment;
use OrisIntel\AuditLog\Tests\Fakes\Models\CommentAuditLog;

class CommentModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_get_classname_of_auditlog_model()
    {
        $comment = new Comment();
        $this->assertEquals(CommentAuditLog::class, $comment->getAuditLogModelName());
    }

    /** @test */
    public function can_get_instance_of_auditlog_model()
    {
        $comment = new Comment();
        $this->assertInstanceOf(CommentAuditLog::class, $comment->getAuditLogModelInstance());
    }

    /** @test */
    public function creating_a_comment_triggers_a_revision()
    {
        /** @var Comment $comment */
        $comment = Comment::create([
            'comment'   => 'Here is a comment!',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        /** @var Collection $logs */
        $logs = $comment->auditLogs()->where('event_type', EventType::CREATED)->get();
        $this->assertEquals(2, $logs->count());

        $title = $logs->where('field_name', 'comment')->first();
        $this->assertEquals('comment', $title->field_name);
        $this->assertNull($title->field_value_old);
        $this->assertEquals('Here is a comment!', $title->field_value_new);

        $commented = $logs->where('field_name', 'posted_at')->first();
        $this->assertEquals('posted_at', $commented->field_name);
        $this->assertNull($commented->field_value_old);
        $this->assertEquals('2019-04-05 12:00:00', $commented->field_value_new);
    }

    /** @test */
    public function updating_a_comment_triggers_a_revision()
    {
        /** @var Comment $comment */
        $comment = Comment::create([
            'comment'   => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $comment->auditLogs()->count());

        // Modify the comment
        $comment->update(['comment' => 'My New Title']);
        $this->assertEquals(3, $comment->auditLogs()->count());

        $title = $comment->auditLogs()->where('event_type', EventType::UPDATED)->first();
        $this->assertEquals('comment', $title->field_name);
        $this->assertEquals('Test', $title->field_value_old);
        $this->assertEquals('My New Title', $title->field_value_new);
    }

    /** @test */
    public function deleting_a_comment_triggers_a_revision()
    {
        /** @var Comment $comment */
        $comment = Comment::create([
            'comment'   => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $comment->auditLogs()->count());

        $comment->delete();

        $this->assertEquals(3, $comment->auditLogs()->count());

        $last = $comment->auditLogs()->where('event_type', EventType::DELETED)->first();
        $this->assertEquals('deleted_at', $last->field_name);
        $this->assertNull($last->field_value_old);
        $this->assertNotEmpty($last->field_value_new);
    }

    /** @test */
    public function force_deleting_a_comment_does_not_trigger_a_revision()
    {
        /** @var Comment $comment */
        $comment = Comment::create([
            'comment'   => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $comment->auditLogs()->count());

        $comment->forceDelete();

        $this->assertEquals(2, $comment->auditLogs()->count());
    }
}
