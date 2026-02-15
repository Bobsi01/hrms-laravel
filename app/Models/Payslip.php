<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    protected $table = 'payslips';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'period_start',
        'period_end',
        'basic_pay',
        'gross_pay',
        'total_earnings',
        'total_deductions',
        'net_pay',
        'breakdown',
        'earnings_json',
        'deductions_json',
        'status',
        'generated_by',
        'remarks',
        'version',
        'released_at',
        'released_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'basic_pay' => 'decimal:2',
            'gross_pay' => 'decimal:2',
            'total_earnings' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'breakdown' => 'array',
            'earnings_json' => 'array',
            'deductions_json' => 'array',
            'released_at' => 'datetime',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }
}
