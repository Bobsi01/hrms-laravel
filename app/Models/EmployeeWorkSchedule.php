<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWorkSchedule extends Model
{
    protected $table = 'employee_work_schedules';

    protected $fillable = [
        'employee_id',
        'schedule_template_id',
        'custom_start_time',
        'custom_end_time',
        'custom_break_minutes',
        'custom_work_days',
        'custom_hours_per_week',
        'effective_from',
        'effective_to',
        'priority',
        'notes',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'custom_work_days' => 'array',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkScheduleTemplate::class, 'schedule_template_id');
    }
}
