<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionAccessPermission extends Model
{
    protected $table = 'position_access_permissions';

    protected $fillable = [
        'position_id',
        'domain',
        'resource_key',
        'access_level',
        'allow_override',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'allow_override' => 'boolean',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
