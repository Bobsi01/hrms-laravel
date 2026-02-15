<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleTemplate extends Model
{
    protected $table = 'work_schedule_templates';

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'break_start_time',
        'work_days',
        'hours_per_week',
        'template_type',
        'config_level',
        'branch_id',
        'department_id',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'work_days' => 'array',
            'hours_per_week' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
