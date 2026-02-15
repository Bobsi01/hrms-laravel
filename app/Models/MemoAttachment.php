<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoAttachment extends Model
{
    protected $table = 'memo_attachments';

    public $timestamps = false;

    protected $fillable = [
        'memo_id',
        'file_path',
        'original_name',
        'file_size',
        'mime_type',
        'description',
        'uploaded_by',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }
}
