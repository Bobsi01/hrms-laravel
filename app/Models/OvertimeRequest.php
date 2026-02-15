<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $table = 'overtime_requests';

    protected $fillable = [
        'employee_id',
        'overtime_date',
        'start_time',
        'end_time',
        'hours_worked',
        'overtime_type',
        'reason',
        'work_description',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'included_in_payroll_run_id',
        'is_paid',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'overtime_date' => 'date',
            'hours_worked' => 'decimal:2',
            'approved_at' => 'datetime',
            'is_paid' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
