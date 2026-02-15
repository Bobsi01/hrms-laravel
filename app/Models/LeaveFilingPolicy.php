<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveFilingPolicy extends Model
{
    protected $table = 'leave_filing_policies';

    protected $fillable = [
        'leave_type',
        'require_advance_notice',
        'advance_notice_days',
        'is_active',
        'notes',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'require_advance_notice' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
