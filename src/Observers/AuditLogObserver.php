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
        /*
         * If a model is hard deleting, either via a force delete or that model does not implement
         * the SoftDeletes trait we should tag it as such so logging doesn't occur down the pipe.
         */
        if ((! method_exists($model, 'isForceDeleting') || $model->isForceDeleting())) {
            $event = EventType::FORCE_DELETED;
        }

        $this->getAuditLogModel($model)
            ->recordChanges($event ?? EventType::DELETED, $model);
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
     * @param $model
     * @param $relationName
     * @param $pivotIds
     */
    public function pivotDetached($model, string $relationName, array $pivotIds)
    {
        $this->getAuditLogModel($model)
            ->recordPivotChanges(EventType::PIVOT_DELETED, $model, $relationName, $pivotIds);
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
