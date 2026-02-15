<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRun extends Model
{
    protected $table = 'payroll_runs';

    protected $fillable = [
        'period_start',
        'period_end',
        'status',
        'notes',
        'generated_by',
        'released_at',
        'company_id',
        'run_mode',
        'computation_mode',
        'settings_snapshot',
        'initiated_by',
        'submitted_at',
        'closed_at',
        'approval_template_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'released_at' => 'datetime',
            'submitted_at' => 'datetime',
            'closed_at' => 'datetime',
            'settings_snapshot' => 'array',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(PayrollBatch::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(PayrollComplaint::class);
    }
}
