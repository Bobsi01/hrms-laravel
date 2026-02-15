<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $table = 'system_logs';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'message',
        'module',
        'file',
        'line',
        'func',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
