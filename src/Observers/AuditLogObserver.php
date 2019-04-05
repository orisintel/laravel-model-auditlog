<?php

namespace OrisIntel\AuditLog\Observers;

use Illuminate\Database\Eloquent\Model;
use OrisIntel\AuditLog\EventType;

class AuditLogObserver
{
    /**
     * @param Model $model
     */
    public function created($model) : void
    {
        $this->getAuditLogModel($model)
            ->recordChanges(EventType::CREATED, $model);
    }

    /**
     * @param Model $model
     */
    public function updated($model) : void
    {
        $this->getAuditLogModel($model)
            ->recordChanges(EventType::UPDATED, $model);
    }

    /**
     * @param Model $model
     */
    public function deleted($model) : void
    {
        $this->getAuditLogModel($model)
            ->recordChanges(EventType::DELETED, $model);
    }

    /**
     * @param Model $model
     */
    public function restored($model) : void
    {
        $this->getAuditLogModel($model)
            ->recordChanges(EventType::RESTORED, $model);
    }

    /**
     * Returns an instance of the AuditLogModel for the specific
     * model you provide.
     *
     * @param $model
     *
     * @return mixed
     */
    protected function getAuditLogModel($model)
    {
        return $model->getAuditLogModelInstance();
    }
}
