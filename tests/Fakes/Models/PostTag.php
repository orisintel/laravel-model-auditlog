<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use OrisIntel\AuditLog\Traits\AuditLoggablePivot;

class PostTag extends Pivot
{
    use AuditLoggablePivot;

    protected $guarded = [];

    /**
     * The array keys are the composite key in the audit log
     * table while the pivot table columns are the values.
     *
     * @var array
     */
    protected $audit_loggable_keys = [
        'audit_post_id' => 'post_id',
        'tag_id'        => 'tag_id',
    ];
}
