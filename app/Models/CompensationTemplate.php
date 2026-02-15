<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompensationTemplate extends Model
{
    protected $table = 'compensation_templates';

    protected $fillable = [
        'category',
        'name',
        'code',
        'amount_type',
        'static_amount',
        'percentage',
        'is_modifiable',
        'effectivity_until',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'static_amount' => 'decimal:2',
            'percentage' => 'decimal:4',
            'is_modifiable' => 'boolean',
            'is_active' => 'boolean',
            'effectivity_until' => 'date',
        ];
    }
}
