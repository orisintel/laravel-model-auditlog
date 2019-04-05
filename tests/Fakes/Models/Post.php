<?php


namespace OrisIntel\AuditLog\Tests\Fakes\Models;


use Illuminate\Database\Eloquent\Model;
use OrisIntel\AuditLog\Traits\AuditLoggable;

class Post extends Model
{
    use AuditLoggable;

    protected $guarded = [];
}
