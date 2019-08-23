<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use OrisIntel\AuditLog\Models\BaseModel;

class PostTagAuditLog extends BaseModel
{
    public $timestamps = false;

    public $table = 'post_tag_auditlog';

    protected $guarded = [];


}
