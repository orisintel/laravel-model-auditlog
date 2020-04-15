<?php

namespace OrisIntel\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use OrisIntel\AuditLog\EventType;

/**
 * @property int event_type
 * @property int subject_id
 * @property \Illuminate\Support\Carbon occurred_at
 */
abstract class BaseModel extends Model
{
    /**
     * Record the change in the appropriate audit log table.
     *
     * @param int   $event_type
     * @param Model $model
     */
    public function recordChanges(int $event_type, $model) : void
    {
        $changes = self::getChangesByType($event_type, $model);

        $this->saveChanges(
            $this->passingChanges($changes, $model),
            $event_type,
            $model
        );
    }

    /**
     * @param array $changes
     * @param $model
     *
     * @return Collection
     */
    public function passingChanges(array $changes, $model) : Collection
    {
        return collect($changes)
            ->except(config('model-auditlog.global_ignored_fields'))
            ->except($model->getAuditLogIgnoredFields())
            ->except([
                $model->getKeyName(), // Ignore the current model's primary key
                'created_at',
                'updated_at',
                'date_created',
                'date_modified',
            ]);
    }

    /**
     * @param Collection $passing_changes
     * @param int        $event_type
     * @param Model      $model
     */
    public function saveChanges(Collection $passing_changes, int $event_type, $model) : void
    {
        $passing_changes
            ->each(function ($change, $key) use ($event_type, $model) {
                $log = new static();
                $log->event_type = $event_type;
                $log->occurred_at = now();

                foreach ($model->getAuditLogForeignKeyColumns() as $k => $v) {
                    $log->setAttribute($k, $model->$v);
                }

                if (config('model-auditlog.enable_user_foreign_keys')) {
                    $log->user_id = Auth::{config('model-auditlog.auth_id_function', 'id')}();
                }

                $log->setAttribute('field_name', $key);
                $log->setAttribute('field_value_old', $model->getOriginal($key));
                $log->setAttribute('field_value_new', $change);

                $log->attributes;
                $log->save();
            });
    }

    /**
     * @param int $event_type
     * @param $model
     * @param string $relationName
     * @param array  $pivotIds
     */
    public function recordPivotChanges(int $event_type, $model, string $relationName, array $pivotIds) : void
    {
        $pivot = $model->{$relationName}()->getPivotClass();

        $changes = $this->getPivotChanges($pivot, $model, $pivotIds);

        foreach ($changes as $change) {
            $this->savePivotChanges(
                $this->passingChanges($change, $model),
                $event_type,
                (new $pivot())
            );
        }
    }

    /**
     * @param $pivot
     * @param $model
     * @param $pivotIds
     *
     * @return array
     */
    public function getPivotChanges($pivot, $model, array $pivotIds) : array
    {
        $columns = (new $pivot())->getAuditLogForeignKeyColumns();
        $key = in_array($model->getForeignKey(), $columns) ? $model->getForeignKey() : $model->getKeyName();

        $changes = [];
        foreach ($pivotIds as $id => $pivotId) {
            foreach ($columns as $auditColumn => $pivotColumn) {
                if ($pivotColumn !== $key) {
                    $changes[$id][$auditColumn] = $pivotId;
                } else {
                    $changes[$id][$auditColumn] = $model->getKey();
                }
            }
        }

        return $changes;
    }

    /**
     * @param Collection $passing_changes
     * @param int        $event_type
     * @param $pivot
     */
    public function savePivotChanges(Collection $passing_changes, int $event_type, $pivot) : void
    {
        $passing_changes
            ->each(function ($change, $key) use ($event_type, $passing_changes, $pivot) {
                $log = $pivot->getAuditLogModelInstance();
                $log->event_type = $event_type;
                $log->occurred_at = now();

                foreach ($passing_changes as $k => $v) {
                    $log->setAttribute($k, $v);
                }

                if (config('model-auditlog.enable_user_foreign_keys')) {
                    $log->user_id = \Auth::{config('model-auditlog.auth_id_function', 'id')}();
                }

                $log->setAttribute('field_name', $key);
                $log->setAttribute('field_value_old', $change);
                $log->setAttribute('field_value_new', null);

                $log->attributes;
                $log->save();
            });
    }

    /**
     * @param int $event_type
     * @param $model
     *
     * @return array
     */
    public static function getChangesByType(int $event_type, $model) : array
    {
        switch ($event_type) {
            case EventType::CREATED:
                return $model->getAttributes();
                break;
            case EventType::RESTORED:
                return $model->getChanges();
                break;
            case EventType::FORCE_DELETED:
                return []; // if force deleted we want to stop execution here as there would be nothing to correlate records to
                break;
            default:
                return $model->getDirty();
                break;
        }
    }

    /**
     * @return BelongsTo|null
     */
    public function subject() : ?BelongsTo
    {
        return $this->belongsTo($this->getSubjectModelClassname(), 'subject_id');
    }

    /**
     * @return string
     */
    public function getSubjectModelClassname() : string
    {
        return str_replace(config('model-auditlog.model_suffix'), '', get_class($this));
    }

    /**
     * Gets an instance of the audit log for this model.
     *
     * @return mixed
     */
    public function getSubjectModelClassInstance()
    {
        $class = $this->getSubjectModelClassname();

        return new $class();
    }
}
