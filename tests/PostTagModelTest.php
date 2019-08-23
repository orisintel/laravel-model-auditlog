<?php

namespace OrisIntel\AuditLog\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OrisIntel\AuditLog\EventType;
use OrisIntel\AuditLog\Tests\Fakes\Models\Post;
use OrisIntel\AuditLog\Tests\Fakes\Models\PostTag;
use OrisIntel\AuditLog\Tests\Fakes\Models\PostTagAuditLog;
use OrisIntel\AuditLog\Tests\Fakes\Models\Tag;

class PostTagModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_get_classname_of_auditlog_model()
    {
        $tag_post = new PostTag();
        $this->assertEquals(PostTagAuditLog::class, $tag_post->getAuditLogModelName());
    }

    /** @test */
    public function can_get_instance_of_auditlog_model()
    {
        $tag_post = new PostTag();
        $this->assertInstanceOf(PostTagAuditLog::class, $tag_post->getAuditLogModelInstance());
    }

    /** @test
     *
     * @group failing
     */
    public function creating_a_post_tag_triggers_a_revision()
    {
        /** @var Tag $tag */
        $tag = Tag::create([
            'title'     => 'Here is a comment!',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $post->tags()->sync($tag);

        var_dump(PostTagAuditLog::all());

        /*
        $logs = $tag->auditLogs()->where('event_type', EventType::CREATED)->get();
        $this->assertEquals(2, $logs->count());

        $title = $logs->where('field_name', 'tag')->first();
        $this->assertEquals('tag', $title->field_name);
        $this->assertNull($title->field_value_old);
        $this->assertEquals('tag', $title->field_value_new);

        $tagged = $logs->where('field_name', 'posted_at')->first();
        $this->assertEquals('posted_at', $tagged->field_name);
        $this->assertNull($tagged->field_value_old);
        $this->assertEquals('2019-04-05 12:00:00', $tagged->field_value_new);*/
    }
}
