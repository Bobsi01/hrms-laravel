<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoRecipient extends Model
{
    protected $table = 'memo_recipients';

    public $timestamps = false;

    protected $fillable = [
        'memo_id',
        'audience_type',
        'audience_identifier',
        'audience_label',
    ];

    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }
}
