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

    /** @test */
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

        /** @var PostTag $post_tag */
        $post_tag = PostTag::first();

        $logs = $post_tag->auditLogs()->where('event_type', EventType::CREATED)->get();
        $this->assertEquals(2, $logs->count());

        $title = $logs->where('field_name', 'tag_id')->first();
        $this->assertEquals('tag_id', $title->field_name);
        $this->assertEquals(1, $title->field_value_new);

        $tagged = $logs->where('field_name', 'post_id')->first();
        $this->assertEquals('post_id', $tagged->field_name);
        $this->assertEquals(1, $tagged->field_value_new);
    }

    /** @test */
    public function syncing_triggers_a_revision()
    {
        $tag1 = Tag::create([
            'title'     => 'Here is a comment!',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $tag2 = Tag::create([
            'title'     => 'Here is another comment!',
            'posted_at' => '2019-04-06 12:00:00',
        ]);

        /** @var Post $post */
        $post = Post::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $post->tags()->sync($tag1);

        //Audit log record for each id in composite key
        $this->assertEquals(2, PostTagAuditLog::all()->count());
        //hasMany relationship works on tag model to audit log
        $this->assertEquals(2, $tag1->auditLogs()->count());
        //Record correct in pivot
        $this->assertEquals(1, PostTag::where('post_id', 1)->where('tag_id', 1)->count());

        $post->tags()->sync($tag2);

        //Audit log record for each id in composite key, including the second sync
        $this->assertEquals(6, PostTagAuditLog::all()->count());
        // hasMany relationship works on new tag model to audit log
        $this->assertEquals(2, $tag2->auditLogs()->count());

        //Correct data count after sync
        $this->assertEquals(1, PostTag::where('post_id', 1)->where('tag_id', 2)->count());
        $this->assertEquals(0, PostTag::where('post_id', 1)->where('tag_id', 1)->count());
    }

    /** @test */
    public function deleting_a_post_tag_does_not_trigger_a_revision()
    {
        $tag1 = Tag::create([
            'id'        => 50,
            'title'     => 'Here is a comment!',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        /** @var Post $post */
        $post = Post::create([
            'id'        => 2000,
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $post->tags()->sync($tag1);

        //Audit log record for each id in composite key
        $this->assertEquals(2, PostTagAuditLog::all()->count());
        //hasMany relationship works on tag model to audit log
        $this->assertEquals(2, $tag1->auditLogs()->count());
        //Record correct in pivot
        $this->assertEquals(1, PostTag::where('post_id', 2000)->where('tag_id', 50)->count());

        $tag2 = Tag::create([
            'id'        => 99,
            'title'     => 'Here is another comment!',
            'posted_at' => '2019-04-06 12:00:00',
        ]);

        $post->tags()->attach($tag2->id);

        $this->assertEquals(2, PostTag::count());
        $this->assertEquals(4, PostTagAuditLog::all()->count());

        //detach both records
        $post->tags()->detach();

        // Sync/detach created a force delete situation where no changes are recorded
        // and thus aren't saved to the audit log
        $this->assertEquals(8, PostTagAuditLog::all()->count());

        // Correct data count after sync
        $this->assertEquals(0, PostTag::count());
    }
}
