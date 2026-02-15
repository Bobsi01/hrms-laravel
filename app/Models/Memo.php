<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Memo extends Model
{
    protected $table = 'memos';

    // DB has only updated_at and published_at, no created_at
    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'memo_code',
        'header',
        'body',
        'issued_by_user_id',
        'issued_by_name',
        'issued_by_position',
        'status',
        'allow_downloads',
        'published_at',
        'deleted_at',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'allow_downloads' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MemoRecipient::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MemoAttachment::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }
}
