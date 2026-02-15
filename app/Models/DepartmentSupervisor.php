<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentSupervisor extends Model
{
    protected $table = 'department_supervisors';

    public $timestamps = false;

    protected $fillable = [
        'department_id',
        'supervisor_user_id',
        'is_override',
        'assigned_by',
        'assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'is_override' => 'boolean',
            'assigned_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_id');
    }
}
