<?php

namespace OrisIntel\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    public static function recordChanges(int $event_type, $model) : void
    {
        $changes = self::getChangeType($event_type, $model);
        if (! $changes) {
            //break for force delete
            return;
        }

        $passing_changes = collect($changes)
            ->except(config('model-auditlog.global_ignored_fields'))
            ->except($model->getAuditLogIgnoredFields())
            ->except([
                $model->getKeyName(), // Ignore the current model's primary key
                'created_at',
                'updated_at',
                'date_created',
                'date_modified',
            ]);

        $passing_changes
            ->each(function ($change, $key) use ($event_type, $model) {
                $log = new static();
                $log->event_type = $event_type;
                $log->occurred_at = now();

                foreach ($model->getAuditLogForeignKeyColumns() as $k => $v) {
                    $log->setAttribute($k, $model->$v);
                }

                if (config('model-auditlog.enable_user_foreign_keys')) {
                    $log->user_id = \Auth::{config('model-auditlog.auth_id_function', 'id')}();
                }

                $log->setAttribute('field_name', $key);
                $log->setAttribute('field_value_old', $model->getOriginal($key));
                $log->setAttribute('field_value_new', $change);
                $log->save();
            });
    }

    /**
     * @param $event_type
     * @param $model
     */
    public static function getChangeType($event_type, $model)
    {
        switch ($event_type) {
            case EventType::CREATED:
                return $model->getAttributes();
                break;
            case EventType::RESTORED:
                return $model->getChanges();
                break;
            case EventType::FORCE_DELETED:
                return; // if force deleted we want to stop execution here as there would be nothing to correlate records to
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
