<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $table = 'performance_reviews';

    protected $fillable = [
        'employee_id',
        'review_date',
        'kpi_score',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'kpi_score' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
