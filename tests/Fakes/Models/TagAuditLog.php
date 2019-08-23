<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use OrisIntel\AuditLog\Models\BaseModel;

class TagAuditLog extends BaseModel
{
    public $timestamps = false;

    public $table = 'tags_auditlog';

    protected $guarded = [];
}
