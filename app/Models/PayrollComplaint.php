<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollComplaint extends Model
{
    protected $table = 'payroll_complaints';

    protected $fillable = [
        'payroll_run_id',
        'payslip_id',
        'employee_id',
        'issue_type',
        'subject',
        'description',
        'status',
        'ticket_code',
        'attachments',
        'assigned_to',
        'submitted_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'resolved_at' => 'datetime',
            'attachments' => 'array',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
