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
        switch ($event_type) {
            default:
                $changes = $model->getDirty();
                break;
            case EventType::CREATED:
                $changes = $model->toArray();
                break;
            case EventType::RESTORED:
                $changes = $model->getChanges();
                break;
        }

        collect($changes)
            ->except(config('model-auditlog.global_ignored_fields'))
            ->each(function ($change, $key) use ($event_type, $model) {
                $log = new static();
                $log->event_type = $event_type;
                $log->subject_id = $model->getKey();
                $log->occurred_at = now();
                $log->setAttribute('field_name', $key);
                $log->setAttribute('field_value_old', $model->getOriginal($key));
                $log->setAttribute('field_value_new', $change);
                $log->save();
            });
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
