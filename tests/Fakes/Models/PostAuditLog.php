<?php


namespace OrisIntel\AuditLog\Tests\Fakes\Models;


use OrisIntel\AuditLog\Models\BaseModel;

class PostAuditLog extends BaseModel
{
    public $timestamps = false;

    public $table = 'posts_auditlog';
}
