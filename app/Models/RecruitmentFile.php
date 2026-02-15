<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentFile extends Model
{
    protected $table = 'recruitment_files';

    public $timestamps = false;

    protected $fillable = [
        'recruitment_id',
        'label',
        'file_path',
        'uploaded_by',
    ];

    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class);
    }
}
