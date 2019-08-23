<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use OrisIntel\AuditLog\Traits\AuditLoggablePivot;

class CommentPost extends Pivot
{
    use AuditLoggablePivot;
    use SoftDeletes;

    protected $guarded = [];

    protected $audit_loggable_keys = [
        'comment_id' => 'comment_id',
        'post_id' => 'post_id'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
