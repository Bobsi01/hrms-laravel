<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollBatch extends Model
{
    protected $table = 'payroll_batches';

    protected $fillable = [
        'payroll_run_id',
        'branch_id',
        'status',
        'computation_mode',
        'approvers_chain',
        'approvals_log',
        'submission_meta',
        'submitted_by',
        'computation_job_id',
        'last_computed_at',
        'remarks',
        'approval_template_id',
    ];

    protected function casts(): array
    {
        return [
            'approvers_chain' => 'array',
            'approvals_log' => 'array',
            'submission_meta' => 'array',
            'last_computed_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'payroll_run_id', 'payroll_run_id');
    }
}
