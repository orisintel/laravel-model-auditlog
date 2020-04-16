<?php

namespace OrisIntel\AuditLog\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use OrisIntel\AuditLog\EventType;
use OrisIntel\AuditLog\Tests\Fakes\Models\Tag;
use OrisIntel\AuditLog\Tests\Fakes\Models\TagAuditLog;

class TagModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_get_classname_of_auditlog_model()
    {
        $tag = new Tag();
        $this->assertEquals(TagAuditLog::class, $tag->getAuditLogModelName());
    }

    /** @test */
    public function can_get_instance_of_auditlog_model()
    {
        $tag = new Tag();
        $this->assertInstanceOf(TagAuditLog::class, $tag->getAuditLogModelInstance());
    }

    /** @test */
    public function creating_a_tag_triggers_a_revision()
    {
        /** @var Tag $tag */
        $tag = Tag::create([
            'title'     => 'Tag',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        /** @var Collection $logs */
        $logs = $tag->auditLogs()->where('event_type', EventType::CREATED)->get();
        $this->assertEquals(2, $logs->count());

        $title = $logs->where('field_name', 'title')->first();
        $this->assertEquals('title', $title->field_name);
        $this->assertNull($title->field_value_old);
        $this->assertEquals('Tag', $title->field_value_new);

        $taged = $logs->where('field_name', 'posted_at')->first();
        $this->assertEquals('posted_at', $taged->field_name);
        $this->assertNull($taged->field_value_old);
        $this->assertEquals('2019-04-05 12:00:00', $taged->field_value_new);
    }

    /** @test */
    public function updating_a_tag_triggers_a_revision()
    {
        /** @var Tag $tag */
        $tag = Tag::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $tag->auditLogs()->count());

        // Modify the tag
        $tag->update(['title' => 'My New Title']);
        $this->assertEquals(3, $tag->auditLogs()->count());

        $title = $tag->auditLogs()->where('event_type', EventType::UPDATED)->first();
        $this->assertEquals('title', $title->field_name);
        $this->assertEquals('Test', $title->field_value_old);
        $this->assertEquals('My New Title', $title->field_value_new);
    }

    /** @test */
    public function deleting_a_tag_triggers_a_revision()
    {
        /** @var Tag $tag */
        $tag = Tag::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $tag->auditLogs()->count());

        $tag->delete();

        $this->assertEquals(3, $tag->auditLogs()->count());

        $last = $tag->auditLogs()->where('event_type', EventType::DELETED)->first();

        $this->assertEquals('deleted_at', $last->field_name);
        $this->assertNull($last->field_value_old);
        $this->assertNotEmpty($last->field_value_new);
    }

    /** @test */
    public function force_deleting_a_tag_does_not_trigger_a_revision()
    {
        /** @var Tag $tag */
        $tag = Tag::create([
            'title'     => 'Test',
            'posted_at' => '2019-04-05 12:00:00',
        ]);

        $this->assertEquals(2, $tag->auditLogs()->count());

        $tag->forceDelete();

        $this->assertEquals(2, $tag->auditLogs()->count());
    }
}
