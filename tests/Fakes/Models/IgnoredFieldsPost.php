<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use OrisIntel\AuditLog\Traits\AuditLoggable;

class IgnoredFieldsPost extends Model
{
    use AuditLoggable;

    protected $guarded = [];

    protected $table = 'posts';

    public function getAuditLogIgnoredFields(): array
    {
        return ['posted_at'];
    }
}
