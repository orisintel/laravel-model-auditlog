<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use OrisIntel\AuditLog\Models\BaseModel;

class CommentAuditLog extends BaseModel
{
    public $timestamps = false;

    public $table = 'comments_auditlog';

    protected $guarded = [];
}
