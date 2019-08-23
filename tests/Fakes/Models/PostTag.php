<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OrisIntel\AuditLog\Traits\AuditLoggablePivot;

class PostTag extends Pivot
{
    use AuditLoggablePivot;

    protected $guarded = [];

    protected $audit_loggable_keys = [
        'post_id' => 'post_id',
        'tag_id'  => 'tag_id',
    ];
}
