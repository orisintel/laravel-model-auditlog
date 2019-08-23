<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use OrisIntel\AuditLog\Models\BaseModel;

class CommentPostAuditLog extends BaseModel
{
    public $timestamps = false;

    public $table = 'comment_post_auditlog';

    protected $guarded = [];
}
