<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CutoffPeriod extends Model
{
    protected $table = 'cutoff_periods';

    protected $fillable = [
        'period_name',
        'start_date',
        'end_date',
        'cutoff_date',
        'pay_date',
        'status',
        'is_locked',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'cutoff_date' => 'date',
            'pay_date' => 'date',
            'is_locked' => 'boolean',
        ];
    }
}
