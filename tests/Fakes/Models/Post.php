<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Fico7489\Laravel\Pivot\Traits\PivotEventTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OrisIntel\AuditLog\Traits\AuditLoggable;

class Post extends Model
{
    use AuditLoggable;
    use SoftDeletes;
    use PivotEventTrait;

    protected $guarded = [];

    protected $primaryKey = 'post_id';

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tag',
            'post_id',
            'tag_id'
        )
            ->using(PostTag::class);
    }
}
