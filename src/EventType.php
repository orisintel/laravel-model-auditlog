<?php

namespace OrisIntel\AuditLog;

class EventType
{
    const CREATED = 1;
    const UPDATED = 2;
    const DELETED = 3;
    const RESTORED = 4;
    const FORCE_DELETED = 5;
}
