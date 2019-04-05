<?php

namespace OrisIntel\AuditLog\Tests\Fakes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OrisIntel\AuditLog\Traits\AuditLoggable;

class Post extends Model
{
    use AuditLoggable;
    use SoftDeletes;

    protected $guarded = [];
}
