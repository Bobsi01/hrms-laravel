<?php

namespace App\Traits;

use App\Services\AuditService;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait for capturing old/new values on model changes for audit logging.
 *
 * Usage in controllers:
 *   use App\Traits\AuditsModelChanges;
 *
 *   // Before update:
 *   $changes = $this->captureChanges($employee, $validatedData);
 *   $employee->update($validatedData);
 *   $this->audit->actionLog('employees', 'update', 'success', [
 *       'employee_id' => $employee->id,
 *       ...$changes,
 *   ]);
 *
 *   // Before delete:
 *   $snapshot = $this->snapshotForDelete($employee, ['id', 'first_name', 'last_name', 'email']);
 *   $employee->delete();
 *   $this->audit->actionLog('employees', 'delete', 'success', $snapshot);
 */
trait AuditsModelChanges
{
    /**
     * Capture the old and new values for fields that actually changed.
     *
     * @param  Model  $model          The existing model instance
     * @param  array  $newData        The incoming data (e.g., validated form input)
     * @param  array  $sensitiveKeys  Fields to mask in the audit log (e.g., password, salary)
     * @return array  ['old_values' => [...], 'new_values' => [...], 'changed_fields' => [...]]
     */
    protected function captureChanges(Model $model, array $newData, array $sensitiveKeys = []): array
    {
        $old = [];
        $new = [];
        $changed = [];

        foreach ($newData as $key => $value) {
            // Skip non-fillable or identical values
            if (!$model->isFillable($key)) {
                continue;
            }

            $currentValue = $model->getAttribute($key);

            // Normalize for comparison (handle type differences)
            if ($this->valuesAreDifferent($currentValue, $value)) {
                $changed[] = $key;

                if (in_array($key, $sensitiveKeys, true)) {
                    $old[$key] = '[REDACTED]';
                    $new[$key] = '[REDACTED]';
                } else {
                    $old[$key] = $currentValue;
                    $new[$key] = $value;
                }
            }
        }

        return [
            'old_values'     => $old,
            'new_values'     => $new,
            'changed_fields' => $changed,
        ];
    }

    /**
     * Create a snapshot of a model before deletion for audit purposes.
     *
     * @param  Model       $model   The model being deleted
     * @param  array|null  $fields  Specific fields to include (null = all fillable)
     * @return array
     */
    protected function snapshotForDelete(Model $model, ?array $fields = null): array
    {
        $attributes = $fields
            ? array_intersect_key($model->getAttributes(), array_flip($fields))
            : array_intersect_key($model->getAttributes(), array_flip($model->getFillable()));

        return [
            'deleted_record' => $attributes,
            'model'          => class_basename($model),
            'id'             => $model->getKey(),
        ];
    }

    /**
     * Compare two values accounting for type differences (e.g., "1" vs 1, null vs "").
     */
    private function valuesAreDifferent(mixed $a, mixed $b): bool
    {
        // Both null or empty string → same
        if (($a === null || $a === '') && ($b === null || $b === '')) {
            return false;
        }

        // Numeric comparison for numeric-ish values
        if (is_numeric($a) && is_numeric($b)) {
            return (float) $a !== (float) $b;
        }

        return (string) $a !== (string) $b;
    }
}
